<?php

namespace Modules\Student\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Student\Models\AssignmentSubmit;

class AssignmentController extends Controller
{
    public function index(): JsonResponse
    {
        $assignmentSubmits = AssignmentSubmit::select(
            'assignment_submits.*',
            'assignments.title AS assignment_title',
            'assignments.description AS assignment_description',
            'assignments.deadline AS assignment_deadline',
            'classes.class_name',
            'sections.section_name',
            'subjects.subject_name'
        )
            ->join('assignments', 'assignments.id', '=', 'assignment_submits.assignment_id') // Link to assignments
            ->join('classes', 'classes.id', '=', 'assignments.class_id') // Link to classes
            ->join('sections', 'sections.id', '=', 'assignments.section_id') // Link to sections
            ->join('subjects', 'subjects.id', '=', 'assignments.subject_id') // Link to subjects
            ->where('assignment_submits.student_id', get_student_id())
            ->where('assignments.session_id', get_option('academic_year'))
            ->where('assignments.institute_id', get_institute_id())
            ->where('assignments.branch_id', get_branch_id()) // Ensure correct academic year
            ->orderBy('assignment_submits.id', 'DESC')
            ->get();

        if (! $assignmentSubmits) {
            return $this->responseError([], _lang('Something went wrong. Assignment Submit not found.'));
        }

        return $this->responseSuccess($assignmentSubmits, 'Assignment Submit fetch successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'assignment_id' => 'required|numeric',
            'file' => 'required',
            'file_2' => 'nullable',
            'file_3' => 'nullable',
            'file_4' => 'nullable',
        ]);

        $assignmentSubmit = new AssignmentSubmit;
        $assignmentSubmit->institute_id = get_institute_id();
        $assignmentSubmit->branch_id = get_branch_id();
        $assignmentSubmit->student_id = get_student_id();
        $assignmentSubmit->assignment_id = intval($request->assignment_id);
        $assignmentSubmit->session_id = get_option('academic_year');

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignmentSubmit->file = $file_name;
        }
        if ($request->hasFile('file_2')) {
            $file = $request->file('file_2');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignmentSubmit->file_2 = $file_name;
        }
        if ($request->hasFile('file_3')) {
            $file = $request->file('file_3');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignmentSubmit->file_3 = $file_name;
        }
        if ($request->hasFile('file_4')) {
            $file = $request->file('file_4');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignmentSubmit->file_4 = $file_name;
        }

        $assignmentSubmit->save();

        return $this->responseSuccess([], 'Assignment Submit successfully.');
    }

    public function show(int $id): JsonResponse
    {
        // Fetch the assignment submission and related assignment details
        $assignmentSubmit = AssignmentSubmit::select(
            'assignment_submits.*',
            'assignments.title AS assignment_title',
            'assignments.description AS assignment_description',
            'assignments.deadline AS assignment_deadline',
            'classes.class_name',
            'sections.section_name',
            'subjects.subject_name'
        )
            ->join('assignments', 'assignments.id', '=', 'assignment_submits.assignment_id') // Link to assignments
            ->join('classes', 'classes.id', '=', 'assignments.class_id') // Link to classes
            ->join('sections', 'sections.id', '=', 'assignments.section_id') // Link to sections
            ->join('subjects', 'subjects.id', '=', 'assignments.subject_id') // Link to subjects
            ->where('assignment_submits.id', $id) // Fetch specific submission by ID
            ->where('assignments.session_id', get_option('academic_year'))
            ->where('assignments.institute_id', get_institute_id())
            ->where('assignments.branch_id', get_branch_id()) // Ensure correct academic year
            ->first();

        if (! $assignmentSubmit) {
            return $this->responseError([], _lang('Something went wrong. Assignment not found.'), 404);
        }

        return $this->responseSuccess($assignmentSubmit, 'Assignment fetch successfully.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'assignment_id' => 'required|numeric',
            'file' => 'required',
            'file_2' => 'nullable',
            'file_3' => 'nullable',
            'file_4' => 'nullable',
        ]);

        $assignmentSubmit = AssignmentSubmit::find($id);
        if (! $assignmentSubmit) {
            return $this->responseError([], _lang('Something went wrong. Assignment not found.'), 404);
        }

        $assignmentSubmit->assignment_id = $request->assignment_id;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignmentSubmit->file = $file_name;
        }
        if ($request->hasFile('file_2')) {
            $file = $request->file('file_2');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignmentSubmit->file_2 = $file_name;
        }
        if ($request->hasFile('file_3')) {
            $file = $request->file('file_3');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignmentSubmit->file_3 = $file_name;
        }
        if ($request->hasFile('file_4')) {
            $file = $request->file('file_4');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignmentSubmit->file_4 = $file_name;
        }

        $assignmentSubmit->save();

        if (! $assignmentSubmit) {
            return $this->responseError([], _lang('Something went wrong. Assignment update not found.'));
        }

        return $this->responseSuccess($assignmentSubmit, 'Assignment update fetch successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $assignmentSubmit = AssignmentSubmit::find($id);
        if (! $assignmentSubmit) {
            return $this->responseError([], _lang('Something went wrong. Assignment delete not found.'));
        }

        $assignmentSubmit->delete();

        return $this->responseSuccess([], 'Assignment delete fetch successfully.');
    }
}
