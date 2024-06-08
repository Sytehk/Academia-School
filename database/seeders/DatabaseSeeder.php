<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authentication\Database\Seeders\InstituteTableSeeder;
use Modules\Authentication\Database\Seeders\UtilitySeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            InstituteTableSeeder::class,
            PermissionSeeder::class,
            UtilitySeeder::class,
            SystemCoreSeeder::class,
        ]);
    }
}
