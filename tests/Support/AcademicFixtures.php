<?php

namespace Tests\Support;

use App\Support\PermissionInstaller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Academic\Models\Teacher;
use Modules\Authentication\Models\User;
use Spatie\Permission\Models\Permission;

/**
 * Minimal two-institute academic world for the academic-system tests.
 *
 * Institute 1 / branch 1 (the Pest baseline tenant) and institute 2 / branch 2
 * each get: active academic year 1, a class with one section and group, a
 * subject, a term exam and a grade scale. Ids are fixed so tests stay readable:
 *
 *   tenant 1: class 1, section 1, group 1, subject 1, exam 1
 *   tenant 2: class 2, section 2, group 2, subject 2, exam 2
 */
final class AcademicFixtures
{
    public static function build(): void
    {
        DB::table('institutes')->updateOrInsert(
            ['id' => 2],
            ['name' => 'Other Institute', 'domain' => 'other.local', 'platform' => 'WEB', 'status' => 1],
        );
        DB::table('branches')->updateOrInsert(
            ['id' => 2],
            ['institute_id' => 2, 'name' => 'Other Branch', 'status' => 1],
        );
        DB::table('academic_years')->updateOrInsert(['id' => 1], ['session' => '2026', 'year' => '2026']);
        DB::table('academic_years')->updateOrInsert(['id' => 2], ['session' => '2025', 'year' => '2025']);

        foreach ([1, 2] as $t) {
            DB::table('settings')->updateOrInsert(
                ['institute_id' => $t, 'branch_id' => $t, 'name' => 'academic_year'],
                ['value' => '1'],
            );
            DB::table('classes')->updateOrInsert(
                ['id' => $t],
                ['institute_id' => $t, 'branch_id' => $t, 'class_name' => "Class {$t}"],
            );
            DB::table('student_groups')->updateOrInsert(
                ['id' => $t],
                ['institute_id' => $t, 'branch_id' => $t, 'group_name' => "Group {$t}"],
            );
            DB::table('sections')->updateOrInsert(
                ['id' => $t],
                ['institute_id' => $t, 'branch_id' => $t, 'class_id' => $t, 'student_group_id' => $t, 'section_name' => "Section {$t}"],
            );
            DB::table('subjects')->updateOrInsert(
                ['id' => $t],
                [
                    'institute_id' => $t, 'branch_id' => $t, 'class_id' => $t, 'group_id' => null,
                    'subject_name' => "Maths {$t}", 'subject_code' => "M0{$t}",
                ],
            );
            DB::table('exams')->updateOrInsert(
                ['id' => $t],
                ['institute_id' => $t, 'branch_id' => $t, 'session_id' => 1, 'name' => "First Term {$t}"],
            );

            foreach ([['A', 70, 100, 5], ['B', 50, 69, 4], ['C', 40, 49, 3], ['F', 0, 39, 0]] as $i => [$name, $low, $high, $point]) {
                DB::table('grades')->updateOrInsert(
                    ['id' => ($t * 10) + $i],
                    [
                        'institute_id' => $t, 'branch_id' => $t, 'grade_name' => $name,
                        'grade_number' => $point, 'grade_range' => "{$low}-{$high}",
                        'priority' => (string) ($i + 1),
                        'number_low' => $low, 'number_high' => $high,
                        'point_low' => $point, 'point_high' => $point, 'grade_point' => $point,
                    ],
                );
            }
        }

        clear_settings_map();
    }

    public static function user(string $type = 'Admin', int $tenant = 1, array $permissions = []): User
    {
        static $n = 0;
        $n++;

        $user = User::factory()->create([
            'user_type' => $type,
            'institute_id' => $tenant,
            'branch_id' => $tenant,
            'status' => 1,
            'email' => "fixture{$n}-".uniqid().'@test.local',
            'phone' => '018'.str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
            'password' => Hash::make('secret'),
        ]);

        if ($permissions !== []) {
            PermissionInstaller::install($permissions);
            // Resolve on the web guard explicitly: after Passport::actingAs the
            // default guard is `api`, where no permission exists.
            $user->givePermissionTo(array_map(
                fn ($name) => Permission::findByName($name, 'web'),
                $permissions
            ));
        }

        return $user;
    }

    /**
     * A student enrolled (session 1) in the tenant's class/section/group.
     *
     * @return array{user: User, student_id: int, student_session_id: int}
     */
    public static function student(int $tenant = 1, string $roll = '1', ?int $classId = null, ?int $sectionId = null, ?int $sessionId = 1): array
    {
        $user = self::user('Student', $tenant);
        $studentId = DB::table('students')->insertGetId([
            'institute_id' => $tenant,
            'branch_id' => $tenant,
            'user_id' => $user->id,
            'first_name' => 'Student',
            'last_name' => $roll,
            'phone' => $user->phone,
            'status' => '1',
        ]);
        $sessionRowId = DB::table('student_sessions')->insertGetId([
            'institute_id' => $tenant,
            'branch_id' => $tenant,
            'session_id' => $sessionId,
            'student_id' => $studentId,
            'class_id' => $classId ?? $tenant,
            'section_id' => $sectionId ?? $tenant,
            'group_id' => $tenant,
            'roll' => $roll,
        ]);

        return ['user' => $user, 'student_id' => $studentId, 'student_session_id' => $sessionRowId];
    }

    /**
     * A teacher in tenant 1 assigned to teach $subjectId in $sectionId.
     */
    public static function teacher(int $subjectId = 1, int $sectionId = 1): User
    {
        DB::table('departments')->updateOrInsert(
            ['institute_id' => 1, 'branch_id' => 1, 'department_name' => 'General'],
            ['slug' => 'general'],
        );
        $deptId = DB::table('departments')->where(['institute_id' => 1, 'department_name' => 'General'])->value('id');

        $teacher = Teacher::factory()->create([
            'institute_id' => 1, 'branch_id' => 1, 'department_id' => $deptId,
        ]);
        DB::table('assign_subjects')->insert([
            'institute_id' => 1, 'branch_id' => 1, 'teacher_id' => $teacher->id,
            'subject_id' => $subjectId, 'section_id' => $sectionId,
        ]);

        return User::findOrFail($teacher->user_id);
    }

    /**
     * Configure two mark components (CA 40 + Exam 60) for the tenant's subject/exam.
     */
    public static function markComponents(int $tenant = 1): void
    {
        foreach ([['CA', 40, 16], ['Exam', 60, 24]] as $i => [$title, $total, $pass]) {
            $codeId = DB::table('mark_config_exam_codes')->insertGetId([
                'institute_id' => $tenant, 'branch_id' => $tenant, 'subject_id' => $tenant,
                'title' => $title.$tenant, 'total_marks' => $total, 'pass_mark' => $pass, 'acceptance' => 1,
                'session_id' => 1,
            ]);
            DB::table('mark_configs')->insert([
                'institute_id' => $tenant, 'branch_id' => $tenant, 'class_id' => $tenant, 'group_id' => $tenant,
                'subject_id' => $tenant, 'exam_id' => $tenant, 'mark_config_exam_code_id' => $codeId, 'session_id' => 1,
            ]);
        }
    }
}
