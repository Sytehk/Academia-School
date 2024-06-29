<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Academic\Models\OptionalSubjectConfig;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\StudentOptionalSubject;
use Modules\Academic\Models\StudentSession;
use Modules\Academic\Models\Subject;
use RealRashid\SweetAlert\Facades\Alert;

class StudentOptionalSubjectController extends Controller
{
    public function index()
    {
        $data = StudentOptionalSubject::with([
            'student',
            'subject',
            'config',
        ])
            ->where('institute_id', get_institute_id())
            ->latest()
            ->get();

        return view('academic::student_optional.index', compact('data'));
    }

    public function create()
    {
        $configs = OptionalSubjectConfig::with([
            'class',
            'group',
        ])
            ->where('institute_id', get_institute_id())
            ->where('is_active', 1)
            ->get();

        $students = Student::where(
            'institute_id',
            get_institute_id()
        )
            ->get();

        return view(
            'academic::student_optional.create',
            compact(
                'configs',
                'students'
            )
        );
    }

    public function store(Request $request)
    {
        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        $request->validate([
            'student_id' => [
                'required',
                Rule::exists('students', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId)),
            ],
            'optional_subject_config_id' => [
                'required',
                Rule::exists('optional_subject_configs', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)),
            ],
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $studentSession = StudentSession::where('student_id', $request->student_id)
            ->where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->where('session_id', get_option('academic_year'))
            ->first();

        if (! $studentSession) {
            Alert::error('Error', _lang('This student has no active session for the current academic year.'));

            return back()->withInput();
        }

        $config = OptionalSubjectConfig::where('institute_id', $instituteId)
            ->find($request->optional_subject_config_id);

        if (! $config) {
            Alert::error('Error', _lang('Optional subject config not found.'));

            return back()->withInput();
        }

        // Make sure the chosen subject is actually one of this config's items,
        // not just any subject id that happens to pass the exists: check.
        if (! $config->items()->where('subject_id', $request->subject_id)->exists()) {
            Alert::error('Error', _lang('The selected subject is not part of this optional subject config.'));

            return back()->withInput();
        }

        $alreadyPicked = StudentOptionalSubject::where('student_id', $request->student_id)
            ->where('optional_subject_config_id', $request->optional_subject_config_id)
            ->where('subject_id', $request->subject_id)
            ->exists();

        if ($alreadyPicked) {
            Alert::error('Error', _lang('This student already has this optional subject recorded for this config.'));

            return back()->withInput();
        }

        $pickCount = StudentOptionalSubject::where('student_id', $request->student_id)
            ->where('optional_subject_config_id', $request->optional_subject_config_id)
            ->count();

        if ($pickCount >= $config->selection_limit) {
            Alert::error('Error', _lang('This student has already reached the selection limit for this optional subject config.'));

            return back()->withInput();
        }

        StudentOptionalSubject::create([
            'institute_id' => $instituteId,
            'branch_id' => $branchId,
            'student_id' => $request->student_id,
            'student_session_id' => $studentSession->id,
            'optional_subject_config_id' => $request->optional_subject_config_id,
            'subject_id' => $request->subject_id,
        ]);

        Alert::toast(
            'Optional Subject Added',
            'success'
        );

        return redirect()
            ->route(
                'student-optional-subject.index'
            );
    }

    private function findRecordOrFail(int $id): StudentOptionalSubject
    {
        $data = StudentOptionalSubject::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->find($id);

        abort_if(! $data, 404, _lang('Record not found.'));

        return $data;
    }

    public function edit($id)
    {
        $data = $this->findRecordOrFail((int) $id);

        $students = Student::where(
            'institute_id',
            get_institute_id()
        )->get();

        $configs = OptionalSubjectConfig::where(
            'institute_id',
            get_institute_id()
        )->get();

        $subjects = Subject::where(
            'class_id',
            $data->config->class_id
        )
            ->where(function ($q) use ($data) {

                if ($data->config->group_id) {

                    $q->where(
                        'group_id',
                        $data->config->group_id
                    );
                }
            })
            ->get();

        return view(
            'academic::student_optional.edit',
            compact(
                'data',
                'students',
                'configs',
                'subjects'
            )
        );
    }

    public function update(Request $request, $id)
    {
        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        $request->validate([
            'student_id' => [
                'required',
                Rule::exists('students', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId)),
            ],
            'student_session_id' => [
                'required',
                Rule::exists('student_sessions', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId)),
            ],
            'optional_subject_config_id' => [
                'required',
                Rule::exists('optional_subject_configs', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)),
            ],
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $data = $this->findRecordOrFail((int) $id);

        $config = OptionalSubjectConfig::where('institute_id', $instituteId)
            ->find($request->optional_subject_config_id);

        if (! $config || ! $config->items()->where('subject_id', $request->subject_id)->exists()) {
            Alert::error('Error', _lang('The selected subject is not part of this optional subject config.'));

            return back()->withInput();
        }

        $duplicate = StudentOptionalSubject::where('id', '!=', $data->id)
            ->where('student_id', $request->student_id)
            ->where('optional_subject_config_id', $request->optional_subject_config_id)
            ->where('subject_id', $request->subject_id)
            ->exists();

        if ($duplicate) {
            Alert::error('Error', _lang('This student already has this optional subject recorded for this config.'));

            return back()->withInput();
        }

        $data->update([
            'student_id' => $request->student_id,
            'student_session_id' => $request->student_session_id,
            'optional_subject_config_id' => $request->optional_subject_config_id,
            'subject_id' => $request->subject_id,
        ]);

        Alert::toast(
            'Optional Subject Updated',
            'success'
        );

        return redirect()
            ->route(
                'student-optional-subject.index'
            );
    }

    public function destroy($id)
    {
        $data = $this->findRecordOrFail((int) $id);
        $data->delete();

        Alert::toast(
            'Deleted Successfully',
            'success'
        );

        return back();
    }

    public function getStudentOptionalSubjects(Request $request)
    {
        $config = OptionalSubjectConfig::with([
            'items.subject',
        ])
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->find($request->config_id);

        $html = '<option value="">
                Select Subject
             </option>';

        if ($config) {

            foreach ($config->items as $item) {
                // Subject uses SoftDeletes; a soft-deleted subject referenced by
                // an existing item would otherwise resolve to null here and
                // fatal on ->subject_name — skip it instead of crashing the AJAX response.
                if (! $item->subject) {
                    continue;
                }

                $html .= '

            <option value="'.$item->subject_id.'">

                '.e($item->subject->subject_name).'

            </option>

            ';
            }
        }

        return $html;
    }
}
