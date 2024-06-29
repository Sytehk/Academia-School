<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\AssignSubject;

class AssignSubjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $sectionId = (int) $request->section_id;
        $classId = (int) $request->class_id;

        // Corrected method call
        $subjects = AssignSubject::getSubjects($classId, $sectionId);

        return $this->responseSuccess($subjects, 'Subjects search successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'teacher_id' => 'required|array|min:1',
            'teacher_id.*' => 'required|exists:teachers,id',
            'subject_id' => 'required|array|min:1',
            'subject_id.*' => 'required|exists:subjects,id',
            'section_id' => 'required|array|min:1',
            'section_id.*' => 'required|exists:sections,id',
        ]);

        if (count($request->teacher_id) !== count($request->subject_id) || count($request->teacher_id) !== count($request->section_id)) {
            return $this->responseError([], 'Subject/section/teacher selections do not match up.', 422);
        }

        $instituteId = get_institute_id();
        $branchId = get_branch_id();
        $created = [];

        for ($i = 0; $i < count($request->teacher_id); $i++) {
            // Match on the actual business key (subject+section within this
            // institute), not a client-supplied "id" — matching on "id" meant
            // every call created a brand new duplicate row instead of updating
            // the existing assignment, since new assignments never send an id.
            $created[] = AssignSubject::updateOrCreate(
                [
                    'institute_id' => $instituteId,
                    'branch_id' => $branchId,
                    'subject_id' => $request->subject_id[$i],
                    'section_id' => $request->section_id[$i],
                ],
                [
                    'teacher_id' => $request->teacher_id[$i],
                ]
            );
        }

        return $this->responseSuccess($created, 'Subjects assigned successfully.');
    }

    public function show($id): JsonResponse
    {
        $assignment = AssignSubject::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->find($id);

        if (! $assignment) {
            return $this->responseError([], 'Assignment not found.', 404);
        }

        return $this->responseSuccess($assignment, 'Assignment fetched successfully.');
    }

    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $assignment = AssignSubject::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->find($id);

        if (! $assignment) {
            return $this->responseError([], 'Assignment not found.', 404);
        }

        try {
            $assignment->update($request->only(['teacher_id', 'subject_id', 'section_id']));
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }

        return $this->responseSuccess($assignment, 'Assignment updated successfully.');
    }

    public function destroy($id): JsonResponse
    {
        $assignment = AssignSubject::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->find($id);

        if (! $assignment) {
            return $this->responseError([], 'Assignment not found.', 404);
        }

        $assignment->delete();

        return $this->responseSuccess([], 'Assignment deleted successfully.');
    }
}
