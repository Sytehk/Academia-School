<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\AssignShift;

class AssignShiftController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $assignShifts = AssignShift::with(['teacher:id,name', 'shift:id,name'])
                ->select('id', 'teacher_id', 'shift_id')
                ->get()
                ->map(function ($shift) {
                    return [
                        'id' => $shift->id,
                        'teacher_id' => $shift->teacher_id,
                        'teacher_name' => $shift->teacher->name ?? null,
                        'shift_id' => $shift->shift_id,
                        'shift_name' => $shift->shift->name ?? null,
                    ];
                });

            return $this->responseSuccess($assignShifts, 'Assign Shifts have been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], _lang('Something went wrong.'));
        }
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'shift_id' => 'required|exists:shifts,id',
        ]);

        $teacher_id = (int) $request->teacher_id;
        $shift_id = (int) $request->shift_id;

        DB::beginTransaction();
        try {
            $exists = AssignShift::where('teacher_id', $teacher_id)
                ->where('shift_id', $shift_id)
                ->exists();

            if ($exists) {
                return $this->responseError([], _lang('Assign Shift Already Added to this teacher.'));
            } else {
                $assign_shift = new AssignShift;
                $assign_shift->institute_id = get_institute_id();
                $assign_shift->branch_id = get_branch_id();
                $assign_shift->teacher_id = $teacher_id;
                $assign_shift->shift_id = $shift_id;
                $assign_shift->save();
                DB::commit();

                return $this->responseSuccess($assign_shift, 'Assign Shift Assign successfully');
            }
        } catch (Exception $e) {
            DB::rollback();

            return $this->responseError([], _lang('Failed to insert records unsuccessfully.'));
        }
    }

    public function show(int $id): JsonResponse
    {
        $assign_shift = AssignShift::where('id', $id)->first();

        return $this->responseSuccess($assign_shift, 'Assign Shift fetch successfully');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'teacher_id' => 'required',
            'shift_id' => 'required',
        ]);

        $teacher_id = $request->teacher_id;
        $shift_id = $request->shift_id;

        $assign_shift = AssignShift::where('id', $id)->first();

        DB::beginTransaction();
        try {
            $assign_shift->teacher_id = $teacher_id;
            $assign_shift->shift_id = $shift_id;
            $assign_shift->update();

            DB::commit();

            return $this->responseSuccess([], 'Assign Shift Updated successfully.');
        } catch (Exception $e) {
            DB::rollback();

            return $this->responseError([], 'Failed to insert records unsuccessfully');
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $assign_shift = AssignShift::where('id', $id)->first();
        if (! empty($assign_shift)) {
            $assign_shift->delete();

            return $this->responseSuccess([], 'Assign Shift Remove successfully.');
        } else {
            return $this->responseError([], 'Something went wrong');
        }
    }
}
