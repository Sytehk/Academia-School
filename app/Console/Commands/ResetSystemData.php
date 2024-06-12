<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;

class ResetSystemData extends Command
{
    protected $signature = 'system:reset';

    protected $description = 'Reset the system database and reseed default data for demo/testing';

    public function handle()
    {
        $this->info('Starting system reset...');
        Log::info('[System Reset] Starting system reset...');

        try {
            Artisan::call('migrate:fresh', ['--force' => true]);
            $this->info('Database migrated.');

            Log::info('[System Reset] Database migrated successfully.');
        } catch (\Exception $e) {
            Log::error('[System Reset] Migration failed: '.$e->getMessage());
        }

        try {
            Artisan::call('db:seed', ['--force' => true]);
            $this->info('Database seeded with default data.');
            Log::info('[System Reset] Database seeded with default data.');
        } catch (\Exception $e) {
            Log::error('[System Reset] Seeding failed: '.$e->getMessage());
        }

        // ✅ Seed all modules like the installation method
        $modules = Module::allEnabled();
        foreach ($modules as $module) {
            $moduleName = $module->getName();
            try {
                Artisan::call('module:seed', ['module' => $moduleName]);
                Log::info("[System Reset] Module seeded: $moduleName");
            } catch (\Exception $e) {
                Log::error("[System Reset] Failed seeding module $moduleName: ".$e->getMessage());
            }
        }

        // Insert the personal access client into the oauth_clients table
        $clientId = (string) Str::uuid(); // Generate a unique UUID for the client ID
        $clientSecret = Str::random(40); // Generate a secure random secret
        DB::table('oauth_clients')->insert([
            'id' => $clientId,
            'user_id' => null,
            'name' => 'Personal Access Client',
            'secret' => $clientSecret,
            'provider' => null,
            'redirect' => config('app.url'),
            'personal_access_client' => true,
            'password_client' => false,
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert the corresponding record into the oauth_personal_access_clients table
        DB::table('oauth_personal_access_clients')->insert([
            'id' => 1, // Adjust ID as needed
            'client_id' => $clientId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        file_put_contents(storage_path('mightySchool'), 'Welcome to MightySchool Software');

        Log::info('[System Reset] System reset process finished.');

        return Command::SUCCESS;
    }
}
