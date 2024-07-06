<?php

namespace Modules\Authentication\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Authentication\Models\Branch;
use Modules\Authentication\Models\Institute;
use RuntimeException;
use Throwable;

/**
 * Resets a single tenant's data back to a clean state.
 *
 * Two levels are offered:
 *  - resetSettingsToDefaults(): re-seeds settings/theme/home content only.
 *  - factoryReset(): wipes every institute-scoped operational table, then
 *    re-seeds defaults. Destructive and not reversible.
 */
class InstituteResetService
{
    public function __construct(
        private readonly InstituteDefaultSetupService $defaultSetup
    ) {}

    /**
     * Tables that must never be touched by a factory reset even though they
     * carry an institute_id column (the tenant root itself, auth/billing/
     * infrastructure tables).
     */
    private const NEVER_TOUCH = [
        'institutes',
        'branches',
        'users',
        'settings',
        'subscriptions',
        'subscription_items',
        'plans',
        'themes',
        'password_reset_tokens',
        'sessions',
        'personal_access_tokens',
        'migrations',
        'roles',
        'permissions',
        'model_has_roles',
        'model_has_permissions',
        'role_has_permissions',
        'jobs',
        'failed_jobs',
        'cache',
        'cache_locks',
    ];

    public function resetSettingsToDefaults(Institute $institute): void
    {
        $branch = Branch::where('institute_id', $institute->id)->orderBy('id')->first();

        if (! $branch) {
            throw new RuntimeException('This institute has no branch to reset settings for.');
        }

        DB::table('settings')
            ->where('institute_id', $institute->id)
            ->where('type', 'general')
            ->delete();

        $this->defaultSetup->seedDefaults($institute, $branch);

        $institute->forceFill(['theme_id' => null])->save();
    }

    /**
     * @return array<string, int> table => rows deleted
     */
    public function factoryReset(Institute $institute): array
    {
        $deleted = [];

        DB::beginTransaction();

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            foreach (Schema::getTableListing() as $table) {
                if (in_array($table, self::NEVER_TOUCH, true)) {
                    continue;
                }

                if (! Schema::hasColumn($table, 'institute_id')) {
                    continue;
                }

                $count = DB::table($table)->where('institute_id', $institute->id)->delete();

                if ($count > 0) {
                    $deleted[$table] = $count;
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            DB::commit();
        } catch (Throwable $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            DB::rollBack();

            Log::error('Institute factory reset failed', [
                'institute_id' => $institute->id,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Factory reset failed: '.$e->getMessage(), 0, $e);
        }

        $branch = Branch::where('institute_id', $institute->id)->orderBy('id')->first();

        if (! $branch) {
            $branch = Branch::create(['institute_id' => $institute->id, 'name' => 'Main Branch']);
        }

        // Clear only the general (content) settings before reseeding — payment
        // gateway configuration is left untouched by a factory reset.
        DB::table('settings')
            ->where('institute_id', $institute->id)
            ->where('type', 'general')
            ->delete();

        $this->defaultSetup->seedDefaults($institute, $branch);
        $institute->forceFill(['theme_id' => null])->save();

        return $deleted;
    }
}
