<?php

namespace Modules\Authentication\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\SAASSetting;
use RuntimeException;

/**
 * Permanently removes an institute and every row attached to it.
 *
 * The normal delete path only stamps `institutes.deleted_at` (soft delete), which
 * leaves the tenant's ~180 module tables, users, subscriptions, domain requests,
 * per-institute roles and uploaded files in place and keeps the unique
 * email/domain slots occupied — so the same school can never be re-registered.
 *
 * This service wipes all of it. It is irreversible by design: the SaaS panel's
 * "Delete" action calls it directly.
 *
 * Most module tables carry a plain `institute_id` column with no database-level
 * foreign key, so deletion order cannot be guaranteed. The whole operation
 * therefore runs inside one transaction with FOREIGN_KEY_CHECKS disabled for its
 * duration (always restored afterwards).
 */
class InstitutePurgeService
{
    /**
     * Pivot / auth tables that are tied to a tenant only through a user id or a
     * subscription id (no institute_id column of their own).
     */
    private const USER_KEYED_TABLES = [
        'model_has_roles' => 'model_id',
        'model_has_permissions' => 'model_id',
        'oauth_access_tokens' => 'user_id',
        'oauth_auth_codes' => 'user_id',
        'personal_access_tokens' => 'tokenable_id',
        'sessions' => 'user_id',
    ];

    /**
     * @throws RuntimeException when the institute is the configured default
     *                          template (removing it would break provisioning).
     */
    public function purge(int $instituteId): void
    {
        $institute = Institute::withTrashed()->find($instituteId);

        if (! $institute) {
            return;
        }

        $this->assertNotDefaultTemplate($instituteId);

        $branchIds = DB::table('branches')->where('institute_id', $instituteId)->pluck('id')->all();
        $userIds = DB::table('users')->where('institute_id', $instituteId)->pluck('id')->all();
        $subscriptionIds = DB::table('subscriptions')->where('institute_id', $instituteId)->pluck('id')->all();

        $deleted = [];

        DB::transaction(function () use ($instituteId, $branchIds, $userIds, $subscriptionIds, &$deleted) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            try {
                foreach (Schema::getTableListing(schemaQualified: false) as $table) {
                    if ($table === 'institutes') {
                        continue;
                    }

                    $columns = Schema::getColumnListing($table);

                    if (in_array('institute_id', $columns, true)) {
                        $count = DB::table($table)->where('institute_id', $instituteId)->delete();
                    } elseif ($branchIds !== [] && in_array('branch_id', $columns, true)) {
                        $count = DB::table($table)->whereIn('branch_id', $branchIds)->delete();
                    } else {
                        continue;
                    }

                    if ($count > 0) {
                        $deleted[$table] = $count;
                    }
                }

                if ($subscriptionIds !== [] && Schema::hasTable('subscription_items')) {
                    $c = DB::table('subscription_items')->whereIn('subscription_id', $subscriptionIds)->delete();
                    if ($c > 0) {
                        $deleted['subscription_items'] = $c;
                    }
                }

                if ($userIds !== []) {
                    foreach (self::USER_KEYED_TABLES as $table => $column) {
                        if (! Schema::hasTable($table) || ! in_array($column, Schema::getColumnListing($table), true)) {
                            continue;
                        }

                        $c = DB::table($table)->whereIn($column, $userIds)->delete();
                        if ($c > 0) {
                            $deleted[$table] = $c;
                        }
                    }
                }

                $deleted['institutes'] = Institute::withTrashed()->whereKey($instituteId)->forceDelete();
            } finally {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        });

        $this->deleteLogo($institute->logo);

        Log::info('Institute purged', [
            'institute_id' => $instituteId,
            'branches' => count($branchIds),
            'users' => count($userIds),
            'rows_deleted' => $deleted,
        ]);
    }

    private function assertNotDefaultTemplate(int $instituteId): void
    {
        $templateId = (int) SAASSetting::query()
            ->where('name', 'default_template_institute_id')
            ->value('value');

        if ($templateId === $instituteId) {
            throw new RuntimeException(
                'This institute is set as the default school template. Choose a different template in SaaS settings before deleting it.'
            );
        }
    }

    private function deleteLogo(?string $logo): void
    {
        if (blank($logo) || in_array($logo, ['def.png', 'default.png'], true)) {
            return;
        }

        // Provisioning has historically stored the logo under both "institute/"
        // and "institutes/"; clear both.
        foreach (['institute/', 'institutes/'] as $dir) {
            try {
                Storage::disk('public')->delete($dir.$logo);
            } catch (\Throwable $e) {
                // A missing file is fine — the DB row is what matters.
            }
        }
    }
}
