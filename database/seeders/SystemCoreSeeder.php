<?php

namespace Database\Seeders;

use App\Services\ModuleManagerService;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SystemCoreSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DemoRestrictionSeeder::class,
            SystemSettingSeeder::class,
        ]);

        $this->grantSystemPermissions();

        app(ModuleManagerService::class)->syncFromNwidart();
    }

    private function grantSystemPermissions(): void
    {
        $permissions = [
            'system.update.view',
            'system.update.upload',
            'system.update.install',
            'system.update.rollback',
            'system.update.history',
            'system.module.view',
            'system.module.enable',
            'system.module.disable',
            'system.demo.manage',
            'system.settings.manage',
        ];

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $adminRole = Role::query()
            ->where('name', 'Admin')
            ->where('guard_name', 'web')
            ->first();

        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }
    }
}
