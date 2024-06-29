<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Academic\Models\Subject;
use RealRashid\SweetAlert\Facades\Alert;

class SubjectController extends Controller
{
    public function index($class = ''): Factory|View
    {
        $subjects = Subject::select('*', 'subjects.id AS id')
            ->join('classes', 'classes.id', '=', 'subjects.class_id')
            ->where('subjects.institute_id', get_institute_id())
            ->where('subjects.branch_id', get_branch_id())
            ->where('subjects.class_id', $class)
            ->orderBy('subjects.id', 'DESC')
            ->get();

        return view('academic::subjects.subject-list', compact('subjects', 'class'));
    }

    public function create(): Factory|View
    {
        return view('academic::subjects.subject-add');
    }

    public function store(Request $request): Factory|Redirector|RedirectResponse|View
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'group_id' => 'nullable|numeric',
            'subject_name' => 'required|string|max:191',
            'subject_code' => 'nullable|string|max:191',
            'subject_short_form' => 'nullable|string|max:191',
            'subject_type' => 'nullable|string|max:191',
            'subject_type_form' => 'nullable|string|max:191',
            'sl_no' => 'nullable|integer|min:0',
        ]);

        if (Subject::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('class_id', $request->class_id)
            ->where('group_id', $request->group_id)
            ->where('subject_code', $request->subject_code)
            ->exists()
        ) {
            Alert::error('Error', _lang('Subject Already Added For This Code. Please Change Code'));

            return redirect('subjects');
        } else {
            $subject = new Subject;
            $subject->institute_id = get_institute_id();
            $subject->branch_id = get_branch_id();
            $subject->class_id = $request->class_id;
            $subject->group_id = $request->group_id;
            $subject->subject_name = $request->subject_name;
            $subject->subject_code = $request->subject_code;
            $subject->subject_short_form = $request->subject_short_form;
            $subject->subject_type = $request->subject_type;
            $subject->subject_type_form = $request->subject_type_form;
            $subject->sl_no = $request->sl_no;
            $subject->save();

            $subjects = Subject::select('*', 'subjects.id AS id')
                ->join('classes', 'classes.id', '=', 'subjects.class_id')
                ->where('subjects.institute_id', get_institute_id())
                ->where('subjects.branch_id', get_branch_id())
                ->where('subjects.class_id', $subject->class_id)
                ->orderBy('subjects.id', 'DESC')
                ->get();

            $class = $subject->class_id;

            Alert::toast('Subject has been create successfully.', 'success');

            return view('academic::subjects.subject-list', compact('subjects', 'class'));
        }
    }

    public function get_subject(Request $request): string
    {
        $results = Subject::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('class_id', $request->class_id)
            ->orderBy('id', 'DESC')
            ->get();
        $subjects = '';
        $subjects .= '<option value="" disabled>Select One</option>';
        foreach ($results as $data) {
            $subjects .= '<option value="'.$data->id.'">'.e($data->subject_name).'</option>';
        }

        return $subjects;
    }

    private function findSubjectOrFail(int $id): Subject
    {
        $subject = Subject::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->find($id);

        abort_if(! $subject, 404, _lang('Subject not found.'));

        return $subject;
    }

    public function edit(int $id): Factory|View
    {
        $subject = $this->findSubjectOrFail($id);

        return view('academic::subjects.subject-edit', compact('subject'));
    }

    public function update(Request $request, int $id): Factory|View
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'group_id' => 'nullable|numeric',
            'subject_name' => 'required|string|max:191',
            'subject_code' => 'nullable|string|max:191',
            'subject_short_form' => 'nullable|string|max:191',
            'subject_type' => 'nullable|string|max:191',
            'subject_type_form' => 'nullable|string|max:191',
            'sl_no' => 'nullable|integer|min:0',
        ]);

        $subject = $this->findSubjectOrFail($id);
        $subject->class_id = $request->class_id;
        $subject->group_id = $request->group_id;
        $subject->subject_name = $request->subject_name;
        $subject->subject_code = $request->subject_code;
        $subject->subject_short_form = $request->subject_short_form;
        $subject->subject_type = $request->subject_type;
        $subject->subject_type_form = $request->subject_type_form;
        $subject->sl_no = $request->sl_no;
        $subject->save();

        $subjects = Subject::select('subjects.*', 'classes.class_name')
            ->join('classes', 'classes.id', '=', 'subjects.class_id')
            ->where('subjects.institute_id', get_institute_id())
            ->where('subjects.branch_id', get_branch_id())
            ->where('class_id', $subject->class_id)->orderBy('id', 'DESC')->get();
        $class = $subject->class_id;

        Alert::toast('Subject has been update successfully.', 'success');

        return view('academic::subjects.subject-list')->with(['subjects' => $subjects, 'class' => $class]);
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $subject = $this->findSubjectOrFail($id);
        $subject->delete();

        Alert::toast('Subject has been delete successfully.', 'success');

        return redirect('subjects');
    }
}
