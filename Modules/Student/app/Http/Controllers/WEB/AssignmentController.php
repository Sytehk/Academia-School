<?php

namespace Modules\Student\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Academic\Models\Assignment;
use Modules\Student\Models\AssignmentSubmit;
use RealRashid\SweetAlert\Facades\Alert;

class AssignmentController extends Controller
{
    public function index(): Factory|View
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
            ->where('assignments.session_id', get_option('academic_year'))
            ->where('assignments.institute_id', get_institute_id())
            ->where('assignments.branch_id', get_branch_id()) // Ensure correct academic year
            ->orderBy('assignment_submits.id', 'DESC')
            ->get();

        return view('student::assignments.assignments-submit.assignment-list', compact('assignmentSubmits'));
    }

    public function create(): Factory|View
    {
        $assignments = Assignment::get();

        return view('student::assignments.assignments-submit.assignment-add', compact('assignments'));
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'assignment_id' => 'required|numeric',
            'file' => 'required',
            'file_2' => 'nullable',
            'file_3' => 'nullable',
            'file_4' => 'nullable',
        ]);

        $assignmentSubmit = new AssignmentSubmit;
        $assignmentSubmit->session_id = get_option('academic_year');
        $assignmentSubmit->institute_id = get_institute_id();
        $assignmentSubmit->branch_id = get_branch_id();
        $assignmentSubmit->student_id = get_student_id();
        $assignmentSubmit->assignment_id = intval($request->assignment_id);

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

        Alert::toast('Assignments create successfully.', 'success');

        return redirect('student/assignments-submit');
    }

    public function show(int $id): Factory|View
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
            abort(404, 'Submission not found.');
        }

        // Pass the data to the view
        return view('student::assignments.assignments-submit.assignment-edit', compact('assignmentSubmit'));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $request->validate([
            'assignment_id' => 'required|numeric',
            'file' => 'required',
            'file_2' => 'nullable',
            'file_3' => 'nullable',
            'file_4' => 'nullable',
        ]);

        $assignmentSubmit = AssignmentSubmit::find($id);
        $assignmentSubmit->assignment_id = intval($request->assignment_id);

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

        Alert::toast('Assignments updated successfully.', 'success');

        return redirect('student/assignments-submit');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $assignmentSubmit = AssignmentSubmit::find($id);
        $assignmentSubmit->delete();

        Alert::toast('Assignments delete successfully.', 'success');

        return redirect('student/assignments-submit');
    }
}
