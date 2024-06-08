<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\StudentSession;
use Modules\Academic\Models\Teacher;
use Modules\Authentication\Models\User;
use Modules\ParentModule\Models\ParentModel;

/**
 * Test fixtures for the Teacher / Student / Parent portals.
 *
 * Creates one login of each portal user_type, all scoped to institute 1 / branch 1,
 * plus a second child so the Parent portal child-switcher can be exercised.
 *
 * Idempotent — safe to run repeatedly. Run with:
 *   php artisan db:seed --class="Database\Seeders\SchoolPortalTestSeeder"
 *
 * Credentials (password for every account): 12345678
 *   Teacher : teacher.portal@test.local
 *   Student : student.portal@test.local   (+ student2.portal@test.local)
 *   Parent  : parent.portal@test.local    (children: both students above)
 */
class SchoolPortalTestSeeder extends Seeder
{
    private const INSTITUTE_ID = 1;

    private const BRANCH_ID = 1;

    private const PASSWORD = '12345678';

    public function run(): void
    {
        if (! DB::table('institutes')->where('id', self::INSTITUTE_ID)->exists()
            || ! DB::table('branches')->where('id', self::BRANCH_ID)->exists()) {
            $this->command?->warn('SchoolPortalTestSeeder: institute/branch 1 not found — run the base seeders first. Skipping.');

            return;
        }

        DB::transaction(function () {
            $sessionId = $this->resolveSessionId();
            [$classId, $sectionId] = $this->resolveClassAndSection();

            $teacher = $this->seedTeacher($classId, $sectionId);
            $studentA = $this->seedStudent('student.portal@test.local', 'Portal', 'StudentOne', '01700000001', $sessionId, $classId, $sectionId, 11);
            $studentB = $this->seedStudent('student2.portal@test.local', 'Portal', 'StudentTwo', '01700000002', $sessionId, $classId, $sectionId, 12);
            $this->seedParent([$studentA, $studentB]);

            $this->command?->info('SchoolPortalTestSeeder: seeded teacher='.$teacher->id
                .", students=[{$studentA->id}, {$studentB->id}], class={$classId}, section={$sectionId}, session={$sessionId}");
        });
    }

    private function resolveSessionId(): int
    {
        $fromSetting = DB::table('settings')
            ->where('institute_id', self::INSTITUTE_ID)
            ->where('name', 'academic_year')
            ->value('value');

        if (is_numeric($fromSetting) && DB::table('academic_years')->where('id', $fromSetting)->exists()) {
            return (int) $fromSetting;
        }

        $existing = DB::table('academic_years')->orderBy('id')->value('id');
        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('academic_years')->insertGetId([
            'session' => (string) now()->year.'-'.(now()->year + 1),
            'year' => (string) now()->year,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @return array{0:int,1:int} [classId, sectionId]
     */
    private function resolveClassAndSection(): array
    {
        $classId = DB::table('classes')
            ->where('institute_id', self::INSTITUTE_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->orderBy('id')
            ->value('id');

        if (! $classId) {
            $classId = DB::table('classes')->insertGetId([
                'institute_id' => self::INSTITUTE_ID,
                'branch_id' => self::BRANCH_ID,
                'class_name' => 'Portal Test Class',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $sectionId = DB::table('sections')
            ->where('institute_id', self::INSTITUTE_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->where('class_id', $classId)
            ->orderBy('id')
            ->value('id');

        if (! $sectionId) {
            $sectionId = DB::table('sections')->insertGetId([
                'institute_id' => self::INSTITUTE_ID,
                'branch_id' => self::BRANCH_ID,
                'class_id' => $classId,
                'section_name' => 'A',
                'room_no' => '101',
                'capacity' => 40,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [(int) $classId, (int) $sectionId];
    }

    private function seedUser(string $email, string $name, string $phone, string $userType): User
    {
        return User::withTrashed()->updateOrCreate(
            ['email' => $email],
            [
                'institute_id' => self::INSTITUTE_ID,
                'branch_id' => self::BRANCH_ID,
                'name' => $name,
                'phone' => $phone,
                'password' => Hash::make(self::PASSWORD),
                'user_type' => $userType,
                'status' => 1,
                'user_status' => '1',
                'email_verified_at' => now(),
                'deleted_at' => null,
            ]
        );
    }

    private function seedTeacher(int $classId, int $sectionId): Teacher
    {
        $user = $this->seedUser('teacher.portal@test.local', 'Portal Teacher', '01700000010', 'Teacher');

        $teacher = Teacher::withoutGlobalScopes()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'institute_id' => self::INSTITUTE_ID,
                'branch_id' => self::BRANCH_ID,
                'name' => 'Portal Teacher',
                'designation' => 'Senior Teacher',
                'gender' => 'Male',
                'religion' => 'Islam',
                'phone' => '01700000010',
                'address' => 'Branch 1, Institute 1',
                'joining_date' => now()->subYear()->toDateString(),
                'access_key' => self::PASSWORD,
                'status' => '1',
            ]
        );

        // Assign the teacher to the test class...
        DB::table('class_assigns')->updateOrInsert(
            ['teacher_id' => $teacher->id, 'class_id' => $classId],
            [
                'institute_id' => self::INSTITUTE_ID,
                'branch_id' => self::BRANCH_ID,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // ...and to every subject of that class in the test section.
        $subjectIds = DB::table('subjects')
            ->where('institute_id', self::INSTITUTE_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->where('class_id', $classId)
            ->pluck('id');

        foreach ($subjectIds as $subjectId) {
            DB::table('assign_subjects')->updateOrInsert(
                ['subject_id' => $subjectId, 'section_id' => $sectionId],
                [
                    'institute_id' => self::INSTITUTE_ID,
                    'branch_id' => self::BRANCH_ID,
                    'teacher_id' => $teacher->id,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        return $teacher;
    }

    private function seedStudent(
        string $email,
        string $firstName,
        string $lastName,
        string $phone,
        int $sessionId,
        int $classId,
        int $sectionId,
        int $roll
    ): Student {
        $user = $this->seedUser($email, trim("$firstName $lastName"), $phone, 'Student');

        $student = Student::withoutGlobalScopes()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'institute_id' => self::INSTITUTE_ID,
                'branch_id' => self::BRANCH_ID,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone,
                'gender' => 'Male',
                'religion' => 'Islam',
                'birthday' => now()->subYears(15)->toDateString(),
                'date_of_admission' => now()->subYear()->toDateString(),
                'roll_no' => (string) $roll,
                'address' => 'Branch 1, Institute 1',
                'access_key' => self::PASSWORD,
                'status' => '1',
            ]
        );

        StudentSession::withoutGlobalScopes()->updateOrCreate(
            ['session_id' => $sessionId, 'student_id' => $student->id],
            [
                'institute_id' => self::INSTITUTE_ID,
                'branch_id' => self::BRANCH_ID,
                'class_id' => $classId,
                'section_id' => $sectionId,
                'roll' => (string) $roll,
                'qr_code' => 'STDPORTAL'.$student->id,
            ]
        );

        return $student;
    }

    /**
     * @param  array<int,Student>  $children
     */
    private function seedParent(array $children): ParentModel
    {
        $user = $this->seedUser('parent.portal@test.local', 'Portal Parent', '01700000020', 'Parent');

        $firstChild = $children[0];

        $parent = ParentModel::withoutGlobalScopes()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'institute_id' => self::INSTITUTE_ID,
                'branch_id' => self::BRANCH_ID,
                // default / active child for the switcher
                'student_id' => $firstChild->id,
                'parent_name' => 'Portal Parent',
                'parent_profession' => 'Engineer',
                'parent_phone' => '01700000020',
                'present_address' => 'Branch 1, Institute 1',
                'permanent_address' => 'Branch 1, Institute 1',
                'access_key' => self::PASSWORD,
                'status' => 1,
            ]
        );

        // The Parent portal resolves children via students.parent_id === parent_models.id
        foreach ($children as $child) {
            $child->forceFill(['parent_id' => $parent->id])->saveQuietly();
        }

        return $parent;
    }
}
