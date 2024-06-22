<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function () {
        // Minimal tenant baseline. Several tables (students, pages,
        // attendance_waivers, …) carry FKs to institutes/branches, and the
        // model factories hard-code institute_id/branch_id = 1. Seed the
        // referenced rows with explicit id = 1 via raw inserts: RefreshDatabase
        // rolls back each test in a transaction, but InnoDB does NOT rewind the
        // AUTO_INCREMENT counter on rollback, so an Eloquent create() would drift
        // to id 2, 3, … and break the hard-coded FK targets. updateOrInsert on a
        // fixed id is stable across the whole run.
        $db = DB::connection();

        $db->table('institutes')->updateOrInsert(
            ['id' => 1],
            ['name' => 'Test Institute', 'domain' => 'test.local', 'platform' => 'WEB', 'status' => 1],
        );

        $db->table('branches')->updateOrInsert(
            ['id' => 1],
            ['institute_id' => 1, 'name' => 'Main Branch', 'status' => 1],
        );

        $db->table('users')->updateOrInsert(
            ['id' => 1],
            [
                'institute_id' => 1,
                'branch_id' => 1,
                'name' => 'Baseline User',
                'phone' => '01700000000',
                'email' => 'baseline@test.local',
                'password' => Hash::make('12345678'),
                'user_type' => 'Admin',
                'status' => 1,
            ],
        );
    })
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
