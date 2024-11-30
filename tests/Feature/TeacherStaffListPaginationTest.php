<?php

use Illuminate\Support\Facades\DB;
use Modules\Authentication\Models\User;

/**
 * T2 + the matching staff-list bug found while fixing it.
 *
 * teacher-list.blade.php's client-side DataTable has no serverSide/ajax
 * option -- it only ever paginates whatever the server already rendered.
 * TeacherController::index() rendered EVERY teacher every time (no
 * pagination at all), so DataTables' own pager worked, just slowly.
 *
 * staff-list.blade.php was the opposite problem: StaffController::index()
 * already used paginate($perPage), but the view never called
 * $staffs->links() -- there was no way to reach a staff member past
 * whatever fit on page one. Fixing teacher pagination the same way
 * (paginate() alone, with no links()) would have introduced the exact bug
 * staff already had; both are fixed together here.
 */
function seedTeacher(int $id, string $name): void
{
    $userId = 20000 + $id;
    DB::table('users')->updateOrInsert(
        ['id' => $userId],
        [
            'institute_id' => 1, 'branch_id' => 1, 'name' => $name,
            'phone' => '0193'.str_pad((string) $id, 7, '0', STR_PAD_LEFT),
            'email' => "teacher{$id}@paging.local", 'password' => bcrypt('x'),
            'user_type' => 'Teacher', 'status' => 1,
        ],
    );
    DB::table('teachers')->updateOrInsert(
        ['id' => $userId],
        [
            'institute_id' => 1, 'branch_id' => 1, 'user_id' => $userId,
            'name' => $name, 'gender' => 'Male', 'phone' => '0193'.str_pad((string) $id, 7, '0', STR_PAD_LEFT),
            'status' => '1',
        ],
    );
}

function seedStaff(int $id, string $name): void
{
    $userId = 30000 + $id;
    DB::table('users')->updateOrInsert(
        ['id' => $userId],
        [
            'institute_id' => 1, 'branch_id' => 1, 'name' => $name,
            'phone' => '0194'.str_pad((string) $id, 7, '0', STR_PAD_LEFT),
            'email' => "staff{$id}@paging.local", 'password' => bcrypt('x'),
            'user_type' => 'Staff', 'status' => 1,
        ],
    );
    DB::table('staffs')->updateOrInsert(
        ['id' => $userId],
        [
            'institute_id' => 1, 'branch_id' => 1, 'user_id' => $userId,
            'name' => $name, 'phone' => '0194'.str_pad((string) $id, 7, '0', STR_PAD_LEFT),
            'designation' => 'Clerk', 'status' => '1',
        ],
    );
}

it('paginates the teacher list and lets an admin reach a later page', function () {
    $admin = User::factory()->create(['institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Admin', 'status' => 1]);
    $this->actingAs($admin);

    for ($i = 1; $i <= 25; $i++) {
        seedTeacher($i, 'Paged Teacher No'.str_pad((string) $i, 2, '0', STR_PAD_LEFT));
    }

    // Ordered by teachers.id DESC, so teacher #01 (the lowest id) is the one
    // pushed to a later page -- must NOT be on page 1 (default page size 20)...
    $page1 = $this->get(route('teachers.index'));
    $page1->assertOk();
    $page1->assertDontSee('Paged Teacher No01');
    // A real, discoverable link to page 2 must be rendered -- not just that
    // the endpoint happens to answer a direct ?page=2 request when asked.
    $page1->assertSee('page=2', false);

    $page2 = $this->get(route('teachers.index', ['page' => 2]));
    $page2->assertOk();
    $page2->assertSee('Paged Teacher No01');
});

it('lets an admin reach a staff member past the first page (the pre-existing bug this session found)', function () {
    $admin = User::factory()->create(['institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Admin', 'status' => 1]);
    $this->actingAs($admin);

    for ($i = 1; $i <= 15; $i++) {
        seedStaff($i, 'Paged Staff No'.str_pad((string) $i, 2, '0', STR_PAD_LEFT));
    }

    // Ordered by staffs.id DESC, so staff #01 (the lowest id) is the one
    // pushed to a later page -- must NOT be on page 1 (default page size 10)...
    $page1 = $this->get(route('staffs.index'));
    $page1->assertOk();
    $page1->assertDontSee('Paged Staff No01');

    // A direct request for ?page=2 always honours server-side pagination
    // regardless of whether a link to it exists anywhere -- that part was
    // never the bug. The actual bug (and the actual fix) is whether a human
    // browsing page 1 has any way to DISCOVER page 2 exists: assert the
    // rendered page contains a link to it, not just that the endpoint answers
    // it when asked directly.
    $page1->assertSee('page=2', false);

    $page2 = $this->get(route('staffs.index', ['page' => 2]));
    $page2->assertOk();
    $page2->assertSee('Paged Staff No01');
});
