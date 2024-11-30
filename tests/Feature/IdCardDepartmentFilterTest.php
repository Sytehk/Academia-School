<?php

use Illuminate\Support\Facades\DB;
use Modules\Authentication\Models\User;

/**
 * teacherIdCardPage()/staffIdCardPage() used to render every teacher/staff in
 * the branch unconditionally -- no filter step at all, unlike students, which
 * require picking a class before printing. teacher-id-cards / staff-id-cards
 * now render a department-filter form instead, and the actual card batch is
 * built by teacher.id.card.print / staff.id.card.print filtered by
 * department_id ("" / omitted = every department, preserving the old
 * unconditional behaviour as an explicit choice rather than the only option).
 */
function seedDepartmentedTeacher(int $id, string $name, ?int $departmentId): void
{
    $userId = 40000 + $id;
    DB::table('users')->updateOrInsert(
        ['id' => $userId],
        [
            'institute_id' => 1, 'branch_id' => 1, 'name' => $name,
            'phone' => '0195'.str_pad((string) $id, 7, '0', STR_PAD_LEFT),
            'email' => "deptteacher{$id}@test.local", 'password' => bcrypt('x'),
            'user_type' => 'Teacher', 'status' => 1,
        ],
    );
    DB::table('teachers')->updateOrInsert(
        ['id' => $userId],
        [
            'institute_id' => 1, 'branch_id' => 1, 'user_id' => $userId,
            'name' => $name, 'gender' => 'Male', 'phone' => '0195'.str_pad((string) $id, 7, '0', STR_PAD_LEFT),
            'department_id' => $departmentId, 'status' => '1',
        ],
    );
}

it('shows a department filter form instead of every teacher unconditionally', function () {
    $admin = User::factory()->create(['institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Admin', 'status' => 1]);
    $this->actingAs($admin);

    DB::table('departments')->updateOrInsert(
        ['id' => 1],
        ['institute_id' => 1, 'branch_id' => 1, 'department_name' => 'Science', 'slug' => 'science'],
    );

    $response = $this->get(route('teacher.id.cards'));

    $response->assertOk();
    $response->assertSee('Science');
    $response->assertViewIs('backend.teachers.id-card.index');
});

it('filters printed teacher cards by department', function () {
    $admin = User::factory()->create(['institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Admin', 'status' => 1]);
    $this->actingAs($admin);

    DB::table('departments')->updateOrInsert(
        ['id' => 1],
        ['institute_id' => 1, 'branch_id' => 1, 'department_name' => 'Science', 'slug' => 'science'],
    );
    DB::table('departments')->updateOrInsert(
        ['id' => 2],
        ['institute_id' => 1, 'branch_id' => 1, 'department_name' => 'Arts', 'slug' => 'arts'],
    );
    seedDepartmentedTeacher(1, 'Science Teacher', 1);
    seedDepartmentedTeacher(2, 'Arts Teacher', 2);

    $filtered = $this->get(route('teacher.id.card.print', ['department_id' => 1]));
    $filtered->assertOk();
    $filtered->assertSee('Science Teacher');
    $filtered->assertDontSee('Arts Teacher');

    // No department_id -- every teacher, preserving the pre-fix behaviour as
    // an explicit "all departments" choice.
    $all = $this->get(route('teacher.id.card.print'));
    $all->assertOk();
    $all->assertSee('Science Teacher');
    $all->assertSee('Arts Teacher');
});
