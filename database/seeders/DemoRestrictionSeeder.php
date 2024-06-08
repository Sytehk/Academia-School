<?php

namespace Database\Seeders;

use App\Models\DemoRestriction;
use Illuminate\Database\Seeder;

class DemoRestrictionSeeder extends Seeder
{
    public function run(): void
    {
        $restrictions = [
            ['key' => 'password_change', 'name' => 'Password Change', 'description' => 'Prevent password changes in demo mode', 'type' => 'security'],
            ['key' => 'email_change', 'name' => 'Email Change', 'description' => 'Prevent email changes in demo mode', 'type' => 'security'],
            ['key' => 'profile_change', 'name' => 'Profile Change', 'description' => 'Prevent admin profile changes', 'type' => 'security'],
            ['key' => 'system_settings', 'name' => 'System Settings', 'description' => 'Prevent critical system settings updates', 'type' => 'settings'],
            ['key' => 'payment_settings', 'name' => 'Payment Settings', 'description' => 'Prevent payment gateway credential changes', 'type' => 'settings'],
            ['key' => 'smtp_settings', 'name' => 'SMTP Settings', 'description' => 'Prevent SMTP credential changes', 'type' => 'settings'],
            ['key' => 'api_credentials', 'name' => 'API Credentials', 'description' => 'Prevent API key changes', 'type' => 'settings'],
            ['key' => 'database_settings', 'name' => 'Database Settings', 'description' => 'Prevent database destructive operations', 'type' => 'security'],
            ['key' => 'account_delete', 'name' => 'Account Delete', 'description' => 'Prevent permanent account deletion', 'type' => 'security'],
            ['key' => 'tenant_delete', 'name' => 'Tenant Delete', 'description' => 'Prevent institute/tenant deletion', 'type' => 'security'],
            ['key' => 'module_disable', 'name' => 'Module Disable', 'description' => 'Prevent disabling modules in demo', 'type' => 'module'],
            ['key' => 'module_delete', 'name' => 'Module Delete', 'description' => 'Prevent module deletion', 'type' => 'module'],
            ['key' => 'data_export', 'name' => 'Data Export', 'description' => 'Restrict sensitive data export', 'type' => 'data'],
            ['key' => 'data_import', 'name' => 'Data Import', 'description' => 'Restrict bulk data import', 'type' => 'data'],
            ['key' => 'system_upgrade', 'name' => 'System Upgrade', 'description' => 'Prevent system updates in demo mode', 'type' => 'system'],
        ];

        foreach ($restrictions as $restriction) {
            DemoRestriction::query()->updateOrCreate(
                ['key' => $restriction['key']],
                array_merge($restriction, ['enabled' => true])
            );
        }
    }
}
