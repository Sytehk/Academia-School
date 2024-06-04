<?php

use Database\Seeders\DatabaseSeeder;

return [

    /*
    |--------------------------------------------------------------------------
    | Application Version
    |--------------------------------------------------------------------------
    |
    | Default version used before the database is seeded. The authoritative
    | version is stored in system_settings (app_version).
    |
    */
    'version' => env('APP_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Demo Mode
    |--------------------------------------------------------------------------
    */
    'demo' => [
        'enabled' => env('DEMO_MODE', false),
        'auto_reset' => env('DEMO_AUTO_RESET', false),
        'reset_interval' => (int) env('DEMO_RESET_INTERVAL', 24),
        'message' => 'This action is disabled in demo mode.',
    ],

    /*
    |--------------------------------------------------------------------------
    | System Updates
    |--------------------------------------------------------------------------
    */
    'updates' => [
        'enabled' => env('SYSTEM_UPDATES_ENABLED', true),
        // Dashboard status card; when true it only renders if something needs attention.
        'dashboard_widget' => env('SYSTEM_STATUS_WIDGET', true),
        'allow_manual_upload' => env('SYSTEM_ALLOW_MANUAL_UPLOAD', true),
        'backup_before_update' => true,
        'maintenance_mode' => true,
        'max_upload_size' => (int) env('SYSTEM_MAX_UPLOAD_MB', 512),
        'max_extract_size' => (int) env('SYSTEM_MAX_EXTRACT_MB', 1024),
        'max_files' => (int) env('SYSTEM_MAX_ZIP_FILES', 10000),
        'storage_path' => 'system-updates',
        'backup_path' => 'system-updates/backups',
        'packages_path' => 'system-updates/packages',
        'temp_path' => 'system-updates/temp',
        'lock_key' => 'system_update_lock',
        'lock_ttl' => 3600,
        'remote_check_url' => env('SYSTEM_UPDATE_SERVER_URL'),
        'remote_check_enabled' => env('SYSTEM_UPDATE_CHECK_ENABLED', false),
        'allowed_commands' => [
            'migrate --force',
            'optimize:clear',
            'config:cache',
            'route:cache',
            'view:cache',
            'cache:clear',
            'view:clear',
            'config:clear',
        ],
        'protected_paths' => [
            '.env',
            '.env.example',
            '.git',
            '.gitignore',
            'vendor',
            'node_modules',
            'storage/app/public',
            'storage/logs',
            'bootstrap/cache',
            'demo.sql',
        ],
        'allowed_file_extensions' => [
            'php', 'blade.php', 'json', 'js', 'css', 'scss', 'sass',
            'svg', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'woff', 'woff2',
            'ttf', 'eot', 'md', 'txt', 'xml', 'yml', 'yaml', 'htaccess',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Demo Data Reset
    |--------------------------------------------------------------------------
    |
    | Tables listed here are truncated during a demo reset. Protected tables
    | are never touched. Extend per project via config merge or env.
    |
    */
    'demo_reset' => [
        'protected_tables' => [
            'migrations',
            'system_settings',
            'demo_restrictions',
            'system_updates',
            'system_update_logs',
            'system_modules',
            'permissions',
            'roles',
            'role_has_permissions',
            'model_has_roles',
            'model_has_permissions',
            'users',
            'oauth_clients',
            'oauth_personal_access_clients',
            'oauth_access_tokens',
            'oauth_refresh_tokens',
            'oauth_auth_codes',
            'plans',
            's_a_a_s_settings',
        ],
        'resettable_tables' => [
            // Project-specific transactional tables — extend in project config
        ],
        'seeders' => [
            DatabaseSeeder::class,
        ],
        'module_seed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Update Lifecycle Hooks
    |--------------------------------------------------------------------------
    |
    | Register class names implementing App\Contracts\SystemUpdateHookInterface
    |
    */
    'update_hooks' => [
        // App\Hooks\MightySchoolUpdateHook::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Manager
    |--------------------------------------------------------------------------
    */
    'modules' => [
        'core_modules' => [
            'Authentication',
            'SystemConfiguration',
        ],
    ],

];
