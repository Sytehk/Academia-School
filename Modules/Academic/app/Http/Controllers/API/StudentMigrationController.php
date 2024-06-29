<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\StudentMigration;
use Modules\Academic\Models\StudentSession;

class StudentMigrationController extends Controller
{
    public function getStudentForMigration(Request $request): JsonResponse
    {
        $classId = (int) $request->class_id;
        $sectionId = (int) $request->section_id;

        $students = Student::join('student_sessions', 'students.id', '=', 'student_sessions.student_id')
            ->select('students.*', 'student_sessions.roll')
            ->where('student_sessions.session_id', get_option('academic_year'))
            ->where('student_sessions.institute_id', get_institute_id())
            ->where('student_sessions.branch_id', get_branch_id())
            ->where('student_sessions.class_id', $classId)
            ->where('student_sessions.section_id', $sectionId)
            ->with('studentSession.group', 'user')
            ->get();

        return $this->responseSuccess($students, 'Get Student for migration.');
    }

    public function store(Request $request): JsonResponse
    {
        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        $request->validate([
            'users' => 'required|array|min:1',
            'users.*' => Rule::exists('students', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId)),
            'section_id' => ['required', Rule::exists('sections', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId))],
            'migrate_section_id' => ['required', Rule::exists('sections', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId))],
            'group_id' => ['required', Rule::exists('student_groups', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId))],
            'class_id' => 'required|array', // one previous class id per student, same length as users[]
            'class_id.*' => Rule::exists('classes', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId)),
            'migrate_class_id' => ['required', Rule::exists('classes', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId))],
            'prev_roll' => 'required|array',
            'new_roll' => 'required|array',
            'new_roll.*' => 'required|integer|min:1',
            'type' => 'required|string',
            'academic_year' => ['required', 'numeric', Rule::exists('academic_years', 'id')],
        ]);

        $studentIds = $request->users;
        $prev_class_id = $request->class_id;
        $class_id = $request->migrate_class_id;
        $prev_section_id = $request->section_id;
        $section_id = $request->migrate_section_id;
        $group_id = $request->group_id;
        $type = $request->type;
        $prev_roll = $request->prev_roll;
        $new_roll = $request->new_roll;
        $academic_year = $request->academic_year;

        // The DB's own unique_combination constraint on student_sessions includes
        // student_id, so it can never stop two different students from ending up
        // with the same roll in the destination class/section — check explicitly.
        $duplicateRoll = StudentSession::where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->where('session_id', $academic_year)
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->whereIn('roll', array_unique($new_roll))
            ->whereNotIn('student_id', $studentIds)
            ->exists();

        if ($duplicateRoll) {
            return $this->responseError([], 'One or more of the new roll numbers is already assigned to another student in the destination class/section.', 422);
        }

        try {
            DB::transaction(function () use ($studentIds, $prev_class_id, $class_id, $prev_section_id, $section_id, $group_id, $type, $prev_roll, $new_roll, $academic_year, $instituteId, $branchId) {
                foreach ($studentIds as $key => $studentId) {
                    $student = Student::where('institute_id', $instituteId)
                        ->where('branch_id', $branchId)
                        ->find((int) $studentId);

                    if (! $student) {
                        continue;
                    }

                    // Resolve the student's session for the CURRENT academic year explicitly —
                    // Student::studentSession() has no session_id filter, so relying on it
                    // becomes unreliable the moment a student has more than one session row.
                    $session = StudentSession::where('institute_id', $instituteId)
                        ->where('branch_id', $branchId)
                        ->where('student_id', $student->id)
                        ->where('session_id', get_option('academic_year'))
                        ->first();

                    // Capture the session's current group before it's overwritten below —
                    // this is the authoritative "previous group", not students.group.
                    $prevGroupId = $session?->group_id;

                    $migrate = new StudentMigration;
                    $migrate->student_id = $studentId;
                    $migrate->prev_section_id = $prev_section_id;
                    $migrate->section_id = $section_id;
                    $migrate->group_id = $group_id;
                    $migrate->prev_group_id = $prevGroupId;
                    // class_id[] carries one previous class per student, matching users[] by index.
                    $migrate->prev_class_id = $prev_class_id[$key] ?? ($prev_class_id[0] ?? null);
                    $migrate->class_id = $class_id;
                    $migrate->new_roll = $new_roll[$key] ?? null;
                    $migrate->prev_roll = $prev_roll[$key] ?? null;
                    $migrate->type = $type;
                    $migrate->academic_year = $academic_year;
                    $migrate->institute_id = $instituteId;
                    $migrate->branch_id = $branchId;
                    $migrate->save();

                    if ($session) {
                        $session->update([
                            'section_id' => $section_id,
                            'class_id' => $class_id,
                            'roll' => $new_roll[$key] ?? null,
                            'group_id' => $group_id,
                        ]);
                    }
                }
            });

            return $this->responseSuccess([], 'Migrate successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function migratedList(Request $request): JsonResponse
    {
        $sectionId = intval($request->section_id);
        $academicYearId = intval($request->academic_year_id);

        // student_migrations/students/student_sessions all have columns named
        // id/institute_id/branch_id/class_id/section_id — selecting '*' from all
        // three let the last-joined table's value silently win for every
        // colliding column name. Alias every ambiguous column explicitly.
        $migratedLists = StudentMigration::query()
            ->with('class', 'section')
            ->select(
                'student_migrations.id as id',
                'student_migrations.type',
                'student_migrations.prev_class_id',
                'student_migrations.prev_section_id',
                'student_migrations.prev_group_id',
                'student_migrations.prev_roll',
                'student_migrations.class_id as migrated_class_id',
                'student_migrations.section_id as migrated_section_id',
                'student_migrations.group_id as migrated_group_id',
                'student_migrations.new_roll',
                'student_migrations.academic_year',
                'student_migrations.created_at as migrated_at',
                'students.id as student_id',
                'students.first_name',
                'students.last_name',
                'students.registration_no',
                'student_sessions.roll',
                'student_sessions.class_id as current_class_id',
                'student_sessions.section_id as current_section_id',
            )
            ->leftJoin('students', 'students.id', '=', 'student_migrations.student_id')
            ->leftJoin('student_sessions', function ($join) {
                $join->on('students.id', '=', 'student_sessions.student_id')
                    ->whereNull('student_sessions.deleted_at');
            })
            ->where('student_migrations.institute_id', get_institute_id())
            ->where('student_migrations.branch_id', get_branch_id())
            ->where('student_migrations.section_id', $sectionId)
            ->where('student_migrations.academic_year', $academicYearId)
            ->orderBy('student_sessions.roll', 'ASC')
            ->get();

        return $this->responseSuccess($migratedLists, 'Get Migration Students.');
    }
}
