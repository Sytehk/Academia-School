<?php

namespace Modules\Teacher\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\Assignment;
use Modules\Academic\Models\ClassRoutine;
use Modules\Academic\Models\Event;
use Modules\Academic\Models\Notice;
use Modules\Academic\Models\Teacher;
use Modules\Teacher\Http\Controllers\Concerns\ScopesTeacherResources;

class TeacherController extends Controller
{
    use ScopesTeacherResources;

    public function my_profile(): JsonResponse
    {
        $teacher = Teacher::select(
            'teachers.*',
            'teachers.id AS id',
            'teachers.user_id',
            'users.name',
            'users.phone',
            'users.email',
            'users.user_type',
            'users.role_id',
        )
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->where('teachers.id', get_teacher_id())
            ->first();

        if (! $teacher) {
            return $this->responseError([], _lang('Something went wrong. Teacher Profile not found.'));
        }

        return $this->responseSuccess($teacher, 'Teacher Profile fetch successfully.');
    }

    public function class_schedule(): JsonResponse
    {
        $routine = ClassRoutine::getTeacherRoutine(get_teacher_id());

        if (! $routine) {
            return $this->responseError([], _lang('Something went wrong. Class Routine not found.'));
        }

        return $this->responseSuccess($routine, 'Class Routine fetch successfully.');
    }

    public function assignments(): JsonResponse
    {
        $assignments = Assignment::select(
            'assignments.*',
            'classes.class_name',
            'sections.section_name',
            'subjects.subject_name',
        )
            ->join('classes', 'classes.id', '=', 'assignments.class_id')
            ->join('sections', 'sections.id', '=', 'assignments.section_id')
            ->join('subjects', 'subjects.id', '=', 'assignments.subject_id')
            ->join('assign_subjects', function ($join) {
                $join->on('assign_subjects.subject_id', '=', 'assignments.subject_id')
                    ->on('assign_subjects.section_id', '=', 'assignments.section_id');
            })
            ->where('assign_subjects.teacher_id', get_teacher_id())
            ->where('assignments.session_id', get_option('academic_year'))
            ->where('assignments.institute_id', get_institute_id())
            ->where('assignments.branch_id', get_branch_id())
            ->orderBy('assignments.id', 'DESC')
            ->get();

        return $this->responseSuccess($assignments, 'Assignments fetch successfully.');
    }

    public function store_assignment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'class_id' => 'required|integer|exists:classes,id',
            'section_id' => 'required|integer|exists:sections,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'file' => 'required|file|max:10240',
            'file_2' => 'nullable|file|max:10240',
            'file_3' => 'nullable|file|max:10240',
            'file_4' => 'nullable|file|max:10240',
        ]);

        if (! $this->teacherTeaches((int) $validated['class_id'], (int) $validated['section_id'], (int) $validated['subject_id'])) {
            return $this->responseError([], _lang('You can only create assignments for a class, section and subject assigned to you.'), 403);
        }

        $assignment = new Assignment;
        $assignment->institute_id = get_institute_id();
        $assignment->branch_id = get_branch_id();
        $assignment->session_id = get_option('academic_year');
        $assignment->created_by = $request->user()->id;
        $assignment->title = $validated['title'];
        $assignment->description = $validated['description'] ?? null;
        $assignment->deadline = $validated['deadline'];
        $assignment->class_id = $validated['class_id'];
        $assignment->section_id = $validated['section_id'];
        $assignment->subject_id = $validated['subject_id'];

        $this->applyAssignmentFiles($request, $assignment);

        $assignment->save();

        return $this->responseSuccess([], 'Assignments create successfully.');
    }

    public function edit_assignment(int $id): JsonResponse
    {
        $assignment = $this->findOwnedAssignment($id);

        if (! $assignment) {
            return $this->responseError([], _lang('Something went wrong. Assignment not found.'), 404);
        }

        return $this->responseSuccess($assignment, 'Assignment fetch successfully.');
    }

    public function show_assignment(int $id): JsonResponse
    {
        $assignment = $this->findOwnedAssignment($id);

        if (! $assignment) {
            return $this->responseError([], _lang('Something went wrong. Assignment not found.'), 404);
        }

        $assignment->loadMissing('subject');

        return $this->responseSuccess($assignment, 'Assignment fetch successfully.');
    }

    public function update_assignment(Request $request, int $id): JsonResponse
    {
        $assignment = $this->findOwnedAssignment($id);

        if (! $assignment) {
            return $this->responseError([], _lang('Something went wrong. Assignment not found.'), 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'class_id' => 'required|integer|exists:classes,id',
            'section_id' => 'required|integer|exists:sections,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'file' => 'nullable|file|max:10240',
            'file_2' => 'nullable|file|max:10240',
            'file_3' => 'nullable|file|max:10240',
            'file_4' => 'nullable|file|max:10240',
        ]);

        if (! $this->teacherTeaches((int) $validated['class_id'], (int) $validated['section_id'], (int) $validated['subject_id'])) {
            return $this->responseError([], _lang('You can only move an assignment to a class, section and subject assigned to you.'), 403);
        }

        $assignment->session_id = get_option('academic_year');
        $assignment->title = $validated['title'];
        $assignment->description = $validated['description'] ?? null;
        $assignment->deadline = $validated['deadline'];
        $assignment->class_id = $validated['class_id'];
        $assignment->section_id = $validated['section_id'];
        $assignment->subject_id = $validated['subject_id'];

        $this->applyAssignmentFiles($request, $assignment);

        $assignment->save();

        return $this->responseSuccess([], 'Assignment update successfully.');
    }

    public function destroy_assignment(int $id): JsonResponse
    {
        $assignment = $this->findOwnedAssignment($id);

        if (! $assignment) {
            return $this->responseError([], _lang('Something went wrong. Assignment not found.'), 404);
        }

        $assignment->delete();

        return $this->responseSuccess([], 'Assignment delete successfully.');
    }

    public function teacherNoticeGet(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $notices = Notice::select('id', 'title', 'notice', 'date', 'created_by')->with('userType')->paginate($perPage);

        return $this->responseSuccess($notices, 'Notices fetch successfully.');
    }

    public function teacherEventGet(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $events = Event::select('id', 'start_date', 'end_date', 'name', 'details', 'location')->paginate($perPage);

        return $this->responseSuccess($events, 'Events fetch successfully.');
    }

    private function applyAssignmentFiles(Request $request, Assignment $assignment): void
    {
        foreach (['file', 'file_2', 'file_3', 'file_4'] as $field) {
            if ($request->hasFile($field)) {
                $assignment->{$field} = secure_public_move($request->file($field), 'uploads/files/assignments/');
            }
        }
    }
}
