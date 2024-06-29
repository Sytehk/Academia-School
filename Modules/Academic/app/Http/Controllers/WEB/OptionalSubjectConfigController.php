<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Academic\Models\OptionalSubjectConfig;
use Modules\Academic\Models\OptionalSubjectItem;
use Modules\Academic\Models\Subject;
use RealRashid\SweetAlert\Facades\Alert;

class OptionalSubjectConfigController extends Controller
{
    public function index()
    {
        $configs = OptionalSubjectConfig::with([
            'class',
            'group',
            'items.subject',
        ])
            ->where('institute_id', get_institute_id())
            ->latest()
            ->get();

        return view('academic::optional_subject.index', compact('configs'));
    }

    public function create()
    {
        return view('academic::optional_subject.create');
    }

    public function store(Request $request)
    {
        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'group_id' => 'nullable|exists:student_groups,id',
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('optional_subject_configs', 'name')->where(
                    fn ($q) => $q->where('institute_id', $instituteId)
                        ->where('class_id', $request->class_id)
                        ->where('group_id', $request->group_id)
                ),
            ],
            'total_mark' => 'required|numeric|min:1',
            'selection_limit' => 'required|numeric|min:1',
            'subjects' => 'required|array|min:1',
            'subjects.*' => 'required|distinct|exists:subjects,id',
        ], [
            'name.unique' => 'An optional subject config with this name already exists for this class/group.',
            'subjects.*.distinct' => 'The same subject was selected more than once.',
        ]);

        DB::transaction(function () use ($request, $instituteId, $branchId) {
            $config = OptionalSubjectConfig::create([
                'institute_id' => $instituteId,
                'branch_id' => $branchId,
                'class_id' => $request->class_id,
                'group_id' => $request->group_id,
                'name' => $request->name,
                'total_mark' => $request->total_mark,
                'selection_limit' => $request->selection_limit,
            ]);

            foreach ($request->subjects as $key => $subject) {
                OptionalSubjectItem::create([
                    'institute_id' => $instituteId,
                    'branch_id' => $branchId,
                    'optional_subject_config_id' => $config->id,
                    'subject_id' => $subject,
                    'mark' => $request->mark[$key] ?? 100,
                    'priority' => $key,
                ]);
            }
        });

        Alert::toast('Optional Subject Created Successfully', 'success');

        return redirect()
            ->route('optional-subject-config.index');
    }

    private function findConfigOrFail(int $id): OptionalSubjectConfig
    {
        $config = OptionalSubjectConfig::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->find($id);

        abort_if(! $config, 404, _lang('Optional subject config not found.'));

        return $config;
    }

    public function edit($id)
    {
        $config = $this->findConfigOrFail((int) $id)->load('items');

        return view(
            'academic::optional_subject.edit',
            compact('config')
        );
    }

    public function update(Request $request, $id)
    {
        $instituteId = get_institute_id();
        $branchId = get_branch_id();
        $configId = (int) $id;

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'group_id' => 'nullable|exists:student_groups,id',
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('optional_subject_configs', 'name')->where(
                    fn ($q) => $q->where('institute_id', $instituteId)
                        ->where('class_id', $request->class_id)
                        ->where('group_id', $request->group_id)
                )->ignore($configId),
            ],
            'total_mark' => 'required|numeric|min:1',
            'selection_limit' => 'required|numeric|min:1',
            'subjects' => 'required|array|min:1',
            'subjects.*' => 'required|distinct|exists:subjects,id',
        ], [
            'name.unique' => 'An optional subject config with this name already exists for this class/group.',
            'subjects.*.distinct' => 'The same subject was selected more than once.',
        ]);

        DB::transaction(function () use ($request, $configId, $instituteId, $branchId) {
            $config = $this->findConfigOrFail($configId);

            $config->update([
                'class_id' => $request->class_id,
                'group_id' => $request->group_id,
                'name' => $request->name,
                'total_mark' => $request->total_mark,
                'selection_limit' => $request->selection_limit,
            ]);

            $config->items()->delete();

            foreach ($request->subjects as $key => $subject) {
                OptionalSubjectItem::create([
                    'institute_id' => $instituteId,
                    'branch_id' => $branchId,
                    'optional_subject_config_id' => $config->id,
                    'subject_id' => $subject,
                    'mark' => $request->mark[$key] ?? 100,
                    'priority' => $key,
                ]);
            }
        });

        Alert::toast('Updated Successfully', 'success');

        return redirect()
            ->route('optional-subject-config.index');
    }

    public function destroy($id)
    {
        $config = $this->findConfigOrFail((int) $id);

        try {
            $config->delete();
            Alert::toast('Deleted Successfully', 'success');
        } catch (Exception $e) {
            Alert::error('Error', _lang('This optional subject config could not be deleted because students already have picks recorded against it.'));
        }

        return back();
    }

    public function getSubjects(Request $request)
    {
        $subjects = Subject::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('class_id', (int) $request->class_id)
            ->where(function ($q) use ($request) {
                if ($request->group_id) {
                    $q->where('group_id', (int) $request->group_id);
                }
            })
            ->get();

        $html = '';
        foreach ($subjects as $subject) {
            $html .=
                '<option value="'.$subject->id.'">
            '.e($subject->subject_name).'
            </option>';
        }

        return $html;
    }
}
