<?php

use Illuminate\Support\Facades\DB;
use Modules\Authentication\Models\User;

/**
 * getStudentsByClassSectionGroupList() called ->getCollection()->transform()
 * unconditionally to attach custom fields -- a LengthAwarePaginator-only
 * method. Whenever $perPage <= 0 the shared helper returns a plain
 * Collection (get(), not paginate()) instead, and getCollection() does not
 * exist on it: fatal error.
 *
 * The WEB student list's `$perPage = (int) ($request->per_page ?? 20)` does
 * NOT catch an explicitly-passed ?per_page=0 the way the API controller's
 * `(int) $request->per_page ?: 20` does -- ?? only falls back on a genuinely
 * missing/null value, not on the falsy (int) 0 that '0' casts to. So this is
 * reachable today via a query string, not just a hypothetical internal call.
 */
it('does not crash the student list when per_page=0 is requested explicitly', function () {
    $admin = User::factory()->create([
        'institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Admin', 'status' => 1,
    ]);
    $this->actingAs($admin);

    DB::table('academic_years')->updateOrInsert(['id' => 1], ['session' => '2026', 'year' => '2026']);
    DB::table('classes')->updateOrInsert(['id' => 1], ['institute_id' => 1, 'branch_id' => 1, 'class_name' => 'One']);
    DB::table('sections')->updateOrInsert(['id' => 1], ['institute_id' => 1, 'branch_id' => 1, 'class_id' => 1, 'section_name' => 'A']);
    DB::table('settings')->updateOrInsert(
        ['institute_id' => 1, 'branch_id' => 1, 'name' => 'academic_year'],
        ['value' => '1'],
    );
    clear_settings_map();

    $studentUser = User::factory()->create([
        'institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Student', 'status' => 1,
        'name' => 'Zero PerPage Student',
    ]);
    DB::table('students')->updateOrInsert(
        ['id' => 801],
        ['institute_id' => 1, 'branch_id' => 1, 'user_id' => $studentUser->id, 'first_name' => 'Zero', 'phone' => '01900000801'],
    );
    DB::table('student_sessions')->updateOrInsert(
        ['id' => 801],
        [
            'institute_id' => 1, 'branch_id' => 1, 'session_id' => 1, 'student_id' => 801,
            'class_id' => 1, 'section_id' => 1, 'roll' => '1',
        ],
    );

    $response = $this->get(route('students.index', ['class_id' => 1, 'section_id' => 1, 'per_page' => 0]));

    $response->assertOk();
    $response->assertSee('Zero PerPage Student');
});
