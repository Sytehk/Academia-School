<?php

use Illuminate\Support\Facades\DB;
use Modules\Authentication\Models\User;

/**
 * TeacherController::index() used to select('*', ...) across a three-table
 * join (teachers -> users -> LEFT JOIN departments). A bare '*' expands to
 * every column from every joined table; same-named columns collide and PDO
 * keeps only the last one processed, so the value the page ends up showing
 * depended on physical join order rather than being chosen on purpose --
 * adding a column to users or departments later could silently change what
 * renders with no code change here. Scoped to explicit columns instead.
 */
it('renders the teacher list with the expected name, phone, image and department', function () {
    $admin = User::factory()->create([
        'institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Admin', 'status' => 1,
    ]);
    $this->actingAs($admin);

    DB::table('departments')->updateOrInsert(
        ['id' => 1],
        ['institute_id' => 1, 'branch_id' => 1, 'department_name' => 'Science Dept', 'slug' => 'science-dept'],
    );

    $teacherUser = User::factory()->create([
        'institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Teacher', 'status' => 1,
        'name' => 'Jane Teacher', 'phone' => '01977778888', 'image' => 'jane.png',
    ]);
    DB::table('teachers')->updateOrInsert(
        ['id' => 501],
        [
            'institute_id' => 1, 'branch_id' => 1, 'user_id' => $teacherUser->id,
            'department_id' => 1, 'name' => 'Jane Teacher', 'gender' => 'Female',
            'phone' => '01977778888', 'status' => '1',
        ],
    );

    $response = $this->get(route('teachers.index'));

    $response->assertOk();
    $response->assertSee('Jane Teacher');
    $response->assertSee('jane.png');
    // The department join must still resolve for a teacher who has one.
    $response->assertViewHas('teachers', function ($teachers) {
        // $teachers is a paginator (TeacherController::index() now paginates,
        // see TeacherStaffListPaginationTest) -- collect($paginator) collects
        // its toArray() structure (current_page/data/... keys), not its rows;
        // firstWhere() proxies straight to the underlying collection instead.
        $row = $teachers->firstWhere('id', 501);

        return $row
            && $row->name === 'Jane Teacher'
            && $row->department_name === 'Science Dept'
            && $row->user_phone === '01977778888'
            && (string) $row->teacher_status === '1';
    });
});

it('still lists a teacher with no department (the leftJoin must not drop them)', function () {
    $admin = User::factory()->create([
        'institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Admin', 'status' => 1,
    ]);
    $this->actingAs($admin);

    $teacherUser = User::factory()->create([
        'institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Teacher', 'status' => 1,
        'name' => 'No Dept Teacher',
    ]);
    DB::table('teachers')->updateOrInsert(
        ['id' => 502],
        [
            'institute_id' => 1, 'branch_id' => 1, 'user_id' => $teacherUser->id,
            'department_id' => null, 'name' => 'No Dept Teacher', 'gender' => 'Female',
            'phone' => '01900000502', 'status' => '1',
        ],
    );

    $response = $this->get(route('teachers.index'));

    $response->assertOk();
    $response->assertSee('No Dept Teacher');
});
