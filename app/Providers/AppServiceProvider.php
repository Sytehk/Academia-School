<?php

namespace App\Providers;

use App\Helpers\InstituteHelper;
use App\Support\TenantTables;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Fluent;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Modules\Authentication\Models\InstituteImageSetting;
use Modules\Elearning\Models\Lesson;
use Modules\SystemConfiguration\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'lesson' => Lesson::class,
        ]);

        Passport::personalAccessTokensExpireIn(now()->addMonths(1));

        // `tenant_exists:table[,column]` — like `exists`, but only matches rows the
        // current user may read (TenantTables rules: own institute/branch, plus
        // the shared library for shared tables). Plain `exists` would accept an id
        // belonging to another institute.
        Validator::extend('tenant_exists', function ($attribute, $value, $parameters) {
            $table = $parameters[0] ?? null;
            $column = $parameters[1] ?? 'id';
            if (! $table || $value === null || $value === '' || is_array($value)) {
                return false;
            }

            $query = DB::table($table)->where($table.'.'.$column, $value);
            if (Schema::hasColumn($table, 'deleted_at')) {
                $query->whereNull($table.'.deleted_at');
            }
            if (TenantTables::isShared($table) || TenantTables::isInstituteOnly($table)) {
                TenantTables::applyReadScope($query, $table);
            } else {
                if (($instituteId = get_institute_id()) && Schema::hasColumn($table, 'institute_id')) {
                    $query->where($table.'.institute_id', $instituteId);
                }
                if (($branchId = get_branch_id()) && Schema::hasColumn($table, 'branch_id')) {
                    $query->where($table.'.branch_id', $branchId);
                }
            }

            return $query->exists();
        }, 'The selected :attribute is invalid.');

        // get_option() serves from a per-process map (see settings_map() in
        // app/Helpers/general.php). Drop that map whenever a settings row changes
        // so a save is visible to the rest of the same request.
        Setting::saved(static fn () => clear_settings_map());
        Setting::deleted(static fn () => clear_settings_map());

        // Ensure database is configured before running queries
        try {
            if (DB::connection()->getPdo()) {
                // Register factory namespace for the modular structure
                Factory::guessFactoryNamesUsing(function (string $modelName) {
                    return 'Modules\\Authentication\\Database\\Factories\\'.class_basename($modelName).'Factory';
                });

                // Share the acting institute's branding/settings with every view. Public
                // frontend requests have no authenticated tenant context, so resolve the
                // active institute from the current host first and fall back to the logged-in
                // institute only when that is available.
                view()->composer('*', function ($view): void {
                    $request = app('request');
                    $instituteId = null;

                    if (Auth::check()) {
                        $instituteId = get_institute_id();
                    }

                    if (! $instituteId && $request) {
                        $institute = InstituteHelper::resolveInstituteFromRequest($request);
                        $instituteId = $institute?->id;
                    }

                    $instituteImage = null;
                    // Fluent so every `$settings['missing_key']` in the themes
                    // resolves to null instead of raising "Undefined array key",
                    // which otherwise 500s the whole public page.
                    $settings = new Fluent;

                    if ($instituteId && Schema::hasTable('institute_image_settings')) {
                        $instituteImage = InstituteImageSetting::query()
                            ->where('institute_id', $instituteId)
                            ->latest()
                            ->first();
                    }

                    if ($instituteId && Schema::hasTable('settings')) {
                        $settings = new Fluent(
                            DB::table('settings')
                                ->where('institute_id', $instituteId)
                                ->pluck('value', 'name')
                                ->all()
                        );
                    }

                    $view->with(compact('instituteImage', 'settings', 'instituteId'));
                });
            }
        } catch (\Exception $e) {
            // ❌ Prevent crash when DB is not set up
        }

        Schema::defaultStringLength(191);
    }
}
