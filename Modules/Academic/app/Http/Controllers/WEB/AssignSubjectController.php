<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\AssignSubject;
use Modules\Academic\Models\Subject;
use RealRashid\SweetAlert\Facades\Alert;

class AssignSubjectController extends Controller
{
    public function index(): Factory|View
    {
        return view('academic::subjects-assign.assign-index');
    }

    public function search(Request $request): Factory|View
    {
        $classId = (int) $request->class_id;
        $sectionId = (int) $request->section_id;

        $subjects = Subject::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('class_id', $classId)
            ->with(['assign' => function ($q) use ($sectionId) {
                $q->where('section_id', $sectionId);
            }])
            ->get();

        return view('academic::subjects-assign.assign-list', compact('subjects', 'sectionId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'subject_id' => 'required|array|min:1',
            'subject_id.*' => 'required|exists:subjects,id',
            'section_id' => 'required|array|min:1',
            'section_id.*' => 'required|exists:sections,id',
            'teacher_id' => 'required|array|min:1',
            'teacher_id.*' => 'required|exists:teachers,id',
        ]);

        if (count($request->subject_id) !== count($request->section_id) || count($request->subject_id) !== count($request->teacher_id)) {
            Alert::error('Error', _lang('Subject/section/teacher selections do not match up.'));

            return back();
        }

        foreach ($request->subject_id as $i => $subjectId) {
            AssignSubject::updateOrCreate(
                [
                    'subject_id' => $subjectId,
                    'section_id' => $request->section_id[$i],
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                ],
                [
                    'teacher_id' => $request->teacher_id[$i],
                ]
            );
        }

        Alert::toast('Subjects assigned successfully.', 'success');

        return redirect()->route('assign-subjects.index');
    }
}
