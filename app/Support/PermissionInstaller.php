<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/**
 * Adds permissions to an EXISTING database (PermissionSeeder only runs on a
 * fresh install) and grants them to every role named "Admin", so the role
 * editor lists them and current admins keep full access.
 */
final class PermissionInstaller
{
    /**
     * @param  array<int, string>  $names
     */
    public static function install(array $names): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $now = now();
        foreach ($names as $name) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name, 'guard_name' => 'web'],
                ['updated_at' => $now, 'created_at' => $now],
            );
        }

        $permissionIds = DB::table('permissions')->whereIn('name', $names)->where('guard_name', 'web')->pluck('id');
        $adminRoleIds = DB::table('roles')->where('name', 'Admin')->where('guard_name', 'web')->pluck('id');

        foreach ($adminRoleIds as $roleId) {
            foreach ($permissionIds as $permissionId) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @param  array<int, string>  $names
     */
    public static function uninstall(array $names): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $ids = DB::table('permissions')->whereIn('name', $names)->where('guard_name', 'web')->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('model_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
