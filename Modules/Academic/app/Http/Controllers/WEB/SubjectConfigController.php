<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\StudentGroup;
use Modules\Academic\Models\Subject;
use Modules\Academic\Models\SubjectConfig;

class SubjectConfigController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $classID = $request->class_id;
        $groupID = $request->group_id;

        $subjectConfigs = Subject::select('subject_configs.id', 'subject_configs.class_id', 'subject_configs.group_id', 'subject_configs.subject_id', 'subject_configs.subject_type', 'subject_configs.sr_no', 'subject_configs.merge_config_id', 'subjects.subject_name', 'subjects.subject_code')
            ->join('subject_configs', 'subject_configs.subject_id', '=', 'subjects.id')
            ->where('subject_configs.institute_id', get_institute_id())
            ->where('subject_configs.branch_id', get_branch_id())
            ->where('subject_configs.class_id', $classID)
            ->where('subject_configs.group_id', $groupID)
            ->get();

        // $subjectConfigs is always a Collection object here — empty() on an
        // object is always false, so the "not assigned" branch below was dead
        // code and every empty result silently returned message: [] instead.
        if ($subjectConfigs->isNotEmpty()) {
            return response()->json([
                'status' => 200,
                'message' => $subjectConfigs,
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Class & Group Wise Subject Not Assigned',
        ]);
    }

    public function create(): Factory|View
    {
        $allClass = ClassModel::select('id', 'class_name')->where('status', 1)->get();
        $allGroup = StudentGroup::select('id', 'group_name')->get();
        $allSubject = Subject::select('id', 'subject_name', 'subject_code', 'subject_type')->get();

        return view('academic::subject_config.create', compact('allClass', 'allGroup', 'allSubject'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'group_id' => [
                'nullable',
                'exists:student_groups,id',
            ],
            'subjects' => 'required|array|min:1',
            'subjects.*' => 'required|exists:subjects,id',
        ]);

        $classID = (int) $request->input('class_id');
        // group_id is nullable at the DB level — casting a missing value to
        // (int) 0 previously violated the FK to student_groups on every
        // groupless class. Keep it as a real null instead.
        $groupID = $request->filled('group_id') ? (int) $request->input('group_id') : null;

        foreach ($request->subjects as $subject) {
            $subjectExist = SubjectConfig::where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->where('subject_id', (int) $subject)
                ->where('class_id', $classID)
                ->where('group_id', $groupID)
                ->exists();

            if (! $subjectExist) {
                SubjectConfig::create([
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                    'class_id' => $classID,
                    'group_id' => $groupID,
                    'subject_id' => (int) $subject,
                ]);
            }
        }

        return response()->json([
            'status' => 200,
            'success' => _lang('Subject has been added'),
        ]);
    }

    public function updateConfig(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|array|min:1',
            'id.*' => 'required|exists:subject_configs,id',
            'subject_type' => 'nullable|array',
            'subject_serial' => 'nullable|array',
            'merge_id' => 'nullable|array',
        ]);

        $ids = $request->id;
        $subject_types = $request->subject_type ?? [];
        $subject_serials = $request->subject_serial ?? [];
        $merge_ids = $request->merge_id ?? [];

        try {
            DB::transaction(function () use ($ids, $subject_types, $subject_serials, $merge_ids) {
                foreach ($ids as $i => $id) {
                    $update_data = SubjectConfig::where('institute_id', get_institute_id())
                        ->where('branch_id', get_branch_id())
                        ->find($id);

                    if (! $update_data) {
                        continue;
                    }

                    $update_data->update([
                        'subject_type' => $subject_types[$i] ?? $update_data->subject_type,
                        'sr_no' => $subject_serials[$i] ?? $update_data->sr_no,
                        'merge_config_id' => $merge_ids[$i] ?? $update_data->merge_config_id,
                    ]);
                }
            });
        } catch (Exception $e) {
            Log::error('Subject config update failed: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'status' => 500,
                'success' => _lang('Data could not be updated.'),
            ], 500);
        }

        return response()->json([
            'status' => 200,
            'success' => 'Data Update Successfully!',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $subjectConfig = SubjectConfig::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('id', $id)
            ->first();

        if ($subjectConfig) {
            $subjectConfig->delete();

            return response()->json([
                'status' => 200,
                'success' => 'Subject Config Delete Successfully',
            ]);
        }

        return response()->json([
            'status' => 400,
            'success' => 'Subject Config Not Found',
        ]);
    }
}
