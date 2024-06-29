<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
use Modules\Academic\Models\Assignment;
use RealRashid\SweetAlert\Facades\Alert;

class AssignmentController extends Controller
{
    private const ALLOWED_EXTENSIONS = ['doc', 'docx', 'pdf', 'zip'];

    private const UPLOAD_DIR = 'public/uploads/files/assignments/';

    public function index(): Factory|View
    {
        // Was joining sections/subjects on assignments.class_id instead of the
        // real section_id/subject_id columns — every list row showed whatever
        // section/subject happened to share that class's numeric id (garbage),
        // and rows with no such coincidental match were silently dropped by
        // the inner join.
        $assignments = Assignment::select('*', 'assignments.id AS id')
            ->join('classes', 'classes.id', '=', 'assignments.class_id')
            ->join('sections', 'sections.id', '=', 'assignments.section_id')
            ->join('subjects', 'subjects.id', '=', 'assignments.subject_id')
            ->where('assignments.session_id', get_option('academic_year'))
            ->where('assignments.institute_id', get_institute_id())
            ->where('assignments.branch_id', get_branch_id())
            ->orderBy('assignments.id', 'DESC')
            ->get();

        return view('academic::assignments.assignment-list', compact('assignments'));
    }

    public function create(): Factory|View
    {
        return view('academic::assignments.assignment-add');
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'class_id' => 'required',
            'section_id' => 'required',
            'subject_id' => 'required',
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
        $assignment->description = sanitize_rich_text($request->description);
        $assignment->deadline = $request->deadline;
        $assignment->class_id = $request->class_id;
        $assignment->section_id = $request->section_id;
        $assignment->subject_id = $request->subject_id;

        try {
            foreach (['file', 'file_2', 'file_3', 'file_4'] as $field) {
                if ($request->hasFile($field)) {
                    $assignment->{$field} = safeDocumentUploader(
                        base_path(self::UPLOAD_DIR),
                        $request->file($field),
                        self::ALLOWED_EXTENSIONS
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('Assignment file upload rejected: '.$e->getMessage());
            Alert::error('Error', _lang('One of the uploaded files could not be verified as a valid document.'));

            return back()->withInput();
        }

        $assignment->save();

        Alert::toast('Assignments create successfully.', 'success');

        return redirect('assignments');
    }

    public function show(int $id): Factory|View
    {
        $assignment = Assignment::select('*', 'assignments.id AS id')
            ->join('classes', 'classes.id', '=', 'assignments.class_id')
            ->join('sections', 'sections.id', '=', 'assignments.section_id')
            ->join('subjects', 'subjects.id', '=', 'assignments.subject_id')
            ->where('assignments.id', $id)
            ->where('assignments.session_id', get_option('academic_year'))
            ->where('assignments.institute_id', get_institute_id())
            ->where('assignments.branch_id', get_branch_id())
            ->first();

        return view('academic::assignments.assignment-show', compact('assignment'));
    }

    public function edit(int $id): Factory|View
    {
        $assignment = Assignment::where('id', $id)
            ->where('assignments.session_id', get_option('academic_year'))
            ->where('assignments.institute_id', get_institute_id())
            ->where('assignments.branch_id', get_branch_id())->first();

        return view('academic::assignments.assignment-edit', compact('assignment'));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'class_id' => 'required',
            'section_id' => 'required',
            'subject_id' => 'required',
            'file' => 'nullable|mimes:doc,pdf,docx,zip',
            'file_2' => 'nullable|mimes:doc,pdf,docx,zip',
            'file_3' => 'nullable|mimes:doc,pdf,docx,zip',
            'file_4' => 'nullable|mimes:doc,pdf,docx,zip',
        ]);

        $assignment = Assignment::where('id', $id)
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->first();

        if (! $assignment) {
            Alert::error('Error', _lang('Assignment not found.'));

            return redirect('assignments');
        }

        $assignment->session_id = get_option('academic_year');
        $assignment->title = $request->title;
        $assignment->description = sanitize_rich_text($request->description);
        $assignment->deadline = $request->deadline;
        $assignment->class_id = $request->class_id;
        $assignment->section_id = $request->section_id;
        $assignment->subject_id = $request->subject_id;

        try {
            foreach (['file', 'file_2', 'file_3', 'file_4'] as $field) {
                if ($request->hasFile($field)) {
                    $assignment->{$field} = safeDocumentUploader(
                        base_path(self::UPLOAD_DIR),
                        $request->file($field),
                        self::ALLOWED_EXTENSIONS,
                        $assignment->{$field}
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('Assignment file upload rejected: '.$e->getMessage());
            Alert::error('Error', _lang('One of the uploaded files could not be verified as a valid document.'));

            return back()->withInput();
        }

        $assignment->save();

        Alert::toast('Assignments updated successfully.', 'success');

        return redirect('assignments');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $assignment = Assignment::where('id', $id)
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->first();

        if (! $assignment) {
            Alert::error('Error', _lang('Assignment not found.'));

            return redirect('assignments');
        }

        $assignment->delete();

        Alert::toast('Assignments delete successfully.', 'success');

        return redirect('assignments');
    }
}
