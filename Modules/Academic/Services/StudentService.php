<?php

declare(strict_types=1);

namespace Modules\Academic\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Academic\Models\CustomFieldValue;
use Modules\Academic\Models\Student;
use Modules\Academic\Repositories\StudentRepository;

class StudentService
{
    public function __construct(
        private readonly StudentRepository $studentRepository
    ) {}

    public function getStudentsAll(): Collection
    {
        return $this->studentRepository->all();
    }

    public function getStudents(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->studentRepository->paginate($perPage, $filter);
    }

    public function findStudentById(int $id): ?Student
    {
        return $this->studentRepository->show((int) $id) ?? null;
    }

    public function createStudent(array $data, $userId = null): ?Student
    {
        $data['user_id'] = intval($userId);
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->studentRepository->create($data);
    }

    public function updateStudent(array $data, int $id): mixed
    {
        return $this->studentRepository->update($data, (int) $id);
    }

    public function deleteStudentById(int $id): int
    {
        return $this->studentRepository->delete((int) $id);
    }

    /**
     * Shared query builder for getStudentsByClassSectionGroup() and
     * getStudentsByClassSectionGroupList() -- the two methods were identical
     * copies down to this exact select/join/filter/sort block, kept in sync
     * by hand. Collapsed into one builder both callers pass through.
     */
    private function studentsByClassSectionGroupQuery(
        int $classId,
        int $sectionId,
        int $groupId,
        int $instituteId,
        int $branchId,
        $search,
        $sortBy,
        $sortOrder,
    ): Builder {
        $query = Student::query()
            ->select(
                'students.*',
                'users.name',
                'users.email',
                'users.phone',
                'users.image',
                'users.role_id',
                'users.status as user_status',
                'users.user_type',
                'student_sessions.roll',
                'student_sessions.qr_code',
                'classes.id as class_id',
                'classes.class_name',
                'sections.id as section_id',
                'sections.section_name',
                'student_groups.id as group_id',
                'student_groups.group_name',
                'parent_models.id as parent_id',
                'parent_users.email as information_sent_to_email'
            )
            ->leftJoin('users', 'users.id', '=', 'students.user_id')
            ->leftJoin('student_sessions', 'students.id', '=', 'student_sessions.student_id')
            ->leftJoin('classes', 'classes.id', '=', 'student_sessions.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'student_sessions.section_id')
            ->leftJoin('student_groups', 'student_sessions.group_id', '=', 'student_groups.id')
            ->leftJoin('parent_models', 'students.id', '=', 'parent_models.student_id')
            ->leftJoin('users as parent_users', 'parent_models.user_id', '=', 'parent_users.id')
            ->where('student_sessions.institute_id', $instituteId)
            ->where('student_sessions.branch_id', $branchId);

        if ($sessionId = get_option('academic_year')) {
            $query->where('student_sessions.session_id', $sessionId);
        }

        $query->where('users.user_type', 'Student');

        // 🔹 Filters
        if ($classId > 0) {
            $query->where('student_sessions.class_id', $classId);
        }
        if ($sectionId > 0) {
            $query->where('student_sessions.section_id', $sectionId);
        }
        if ($groupId > 0) {
            $query->where('student_sessions.group_id', $groupId);
        }

        // 🔍 Search
        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('students.first_name', 'like', "%$search%")
                    ->orWhere('students.last_name', 'like', "%$search%")
                    ->orWhere('students.phone', 'like', "%$search%")
                    ->orWhere('students.registration_no', 'like', "%$search%")
                    ->orWhere('student_sessions.roll', 'like', "%$search%");
            });
        }

        // 🔃 Sorting
        $allowedSorts = ['students.first_name', 'students.registration_no', 'student_sessions.roll', 'created_at'];

        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'student_sessions.roll';
        }
        $sortOrder = strtolower((string) $sortOrder) === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $sortOrder);

        return $query;
    }

    public function getStudentsByClassSectionGroup(
        int $classId,
        int $sectionId,
        int $groupId,
        int $perPage,
        int $instituteId,
        int $branchId,
        $search = null,
        $sortBy = 'student_sessions.roll',
        $sortOrder = 'asc'
    ) {
        $query = $this->studentsByClassSectionGroupQuery(
            $classId, $sectionId, $groupId, $instituteId, $branchId, $search, $sortBy, $sortOrder
        );

        return $perPage > 0 ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Same result set as getStudentsByClassSectionGroup(), with each student's
     * custom field values attached.
     *
     * $perPage <= 0 returns the plain (unpaginated) collection -- exactly like
     * the sibling method -- so this branches on the actual return type of
     * paginate()/get() rather than assuming a paginator. The previous version
     * called ->getCollection() unconditionally, a LengthAwarePaginator-only
     * method: any caller passing perPage <= 0 (reachable today via an explicit
     * ?per_page=0 on the WEB student list, whose `?? 20` default does not
     * catch an explicitly-passed 0 the way the API controller's `?: 20` does)
     * hit a fatal "Call to undefined method Collection::getCollection()".
     */
    public function getStudentsByClassSectionGroupList(
        $classId,
        $sectionId,
        $groupId,
        $perPage,
        $instituteId,
        $branchId,
        $search = null,
        $sortBy = 'student_sessions.roll',
        $sortOrder = 'asc'
    ) {
        $students = $this->getStudentsByClassSectionGroup(
            (int) $classId, (int) $sectionId, (int) $groupId, (int) $perPage,
            (int) $instituteId, (int) $branchId, $search, $sortBy, $sortOrder
        );

        // --- Attach Custom Fields ---
        $studentIds = $students->pluck('id')->toArray();
        if ($studentIds) {
            $customFieldValues = CustomFieldValue::where('model_type', 'student')
                ->whereIn('model_id', $studentIds)
                ->with('field')
                ->get()
                ->groupBy('model_id');

            $transform = function ($student) use ($customFieldValues) {
                $customs = collect($customFieldValues[$student->id] ?? [])
                    ->mapWithKeys(fn ($cf) => [
                        $cf->field->field_name ?? $cf->custom_field_id => $cf->value,
                    ])
                    ->toArray();

                // Convert to array of objects
                $customFieldsArray = [];
                foreach ($customs as $key => $value) {
                    $customFieldsArray[] = [
                        'field_name' => $key,
                        'value' => $value,
                    ];
                }

                return array_merge($student->toArray(), [
                    'custom_fields' => $customFieldsArray,
                ]);
            };

            // transform() itself exists on both a paginator's underlying
            // collection and a plain Collection; only getCollection() (used to
            // reach that underlying collection) is paginator-only.
            if ($students instanceof LengthAwarePaginator) {
                $students->getCollection()->transform($transform);
            } else {
                $students->transform($transform);
            }
        }

        return $students;
    }

    public function getStudentById($id): ?Student
    {
        return Student::join('users', 'users.id', '=', 'students.user_id')
            ->where('students.id', $id)
            ->select('users.*', 'students.id as studentId')
            ->first();
    }
}
