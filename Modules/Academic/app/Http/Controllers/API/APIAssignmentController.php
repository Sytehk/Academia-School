<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\Assignment;

class APIAssignmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $assignments = Assignment::select('*', 'assignments.id AS id')
            ->join('classes', 'classes.id', '=', 'assignments.class_id')
            ->join('sections', 'sections.id', '=', 'assignments.class_id')
            ->join('subjects', 'subjects.id', '=', 'assignments.class_id')
            ->where('assignments.session_id', get_option('academic_year'))
            ->where('assignments.institute_id', get_institute_id())
            ->where('assignments.branch_id', get_branch_id())
            ->orderBy('assignments.id', 'DESC')
            ->paginate($perPage);

        return $this->responseSuccess($assignments, 'Assignments has been fetched successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'file' => 'required|mimes:doc,pdf,docx,zip',
            'file_2' => 'nullable|mimes:doc,pdf,docx,zip',
            'file_3' => 'nullable|mimes:doc,pdf,docx,zip',
            'file_4' => 'nullable|mimes:doc,pdf,docx,zip',
        ]);

        $assignment = new Assignment;
        $assignment->institute_id = get_institute_id();
        $assignment->branch_id = get_branch_id();
        $assignment->session_id = get_option('academic_year');
        $assignment->title = $request->title;
        $assignment->description = $request->description;
        $assignment->deadline = $request->deadline;
        $assignment->class_id = (int) $request->class_id;
        $assignment->section_id = (int) $request->section_id;
        $assignment->subject_id = (int) $request->subject_id;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignment->file = $file_name;
        }
        if ($request->hasFile('file_2')) {
            $file = $request->file('file_2');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignment->file_2 = $file_name;
        }
        if ($request->hasFile('file_3')) {
            $file = $request->file('file_3');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignment->file_3 = $file_name;
        }
        if ($request->hasFile('file_4')) {
            $file = $request->file('file_4');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignment->file_4 = $file_name;
        }

        $assignment->save();

        return $this->responseSuccess([], 'Assignments has been added.');
    }

    public function show(int $id): JsonResponse
    {
        $assignment = Assignment::select('*', 'assignments.id AS id')
            ->join('classes', 'classes.id', '=', 'assignments.class_id')
            ->join('sections', 'sections.id', '=', 'assignments.class_id')
            ->join('subjects', 'subjects.id', '=', 'assignments.class_id')
            ->where('assignments.id', $id)
            ->where('assignments.session_id', get_option('academic_year'))
            ->where('assignments.institute_id', get_institute_id())
            ->where('assignments.branch_id', get_branch_id())
            ->first();

        return $this->responseSuccess($assignment, 'Assignments has been fetch.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'file' => 'nullable|mimes:doc,pdf,docx,zip',
            'file_2' => 'nullable|mimes:doc,pdf,docx,zip',
            'file_3' => 'nullable|mimes:doc,pdf,docx,zip',
            'file_4' => 'nullable|mimes:doc,pdf,docx,zip',
        ]);

        $assignment = Assignment::find($id);
        if (! $assignment) {
            return $this->responseError([], _lang('Something went wrong. assignment can not be found.'), 404);
        }

        $assignment->session_id = get_option('academic_year');
        $assignment->title = $request->title;
        $assignment->description = $request->description;
        $assignment->deadline = $request->deadline;
        $assignment->class_id = $request->class_id;
        $assignment->section_id = $request->section_id;
        $assignment->subject_id = $request->subject_id;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignment->file = $file_name;
        }
        if ($request->hasFile('file_2')) {
            $file = $request->file('file_2');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignment->file_2 = $file_name;
        }
        if ($request->hasFile('file_3')) {
            $file = $request->file('file_3');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignment->file_3 = $file_name;
        }
        if ($request->hasFile('file_4')) {
            $file = $request->file('file_4');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignment->file_4 = $file_name;
        }

        $assignment->save();

        return $this->responseSuccess($assignment, 'Assignments has been updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $assignment = Assignment::find($id);
        if (! $assignment) {
            return $this->responseError([], _lang('Something went wrong. assignment can not be found.'), 404);
        }

        $assignment->delete();

        return $this->responseSuccess([], 'Assignments has been deleted.');
    }
}
