<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\Shift;

class ShiftController extends Controller
{
    public function index(): JsonResponse
    {
        $shifts = Shift::orderBy('id', 'desc')->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->get();

        return $this->responseSuccess($shifts, 'Shifts have been fetched successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|max:255',
        ]);

        DB::beginTransaction();
        try {
            $shift = new Shift;
            $shift->institute_id = get_institute_id();
            $shift->branch_id = get_branch_id();
            $shift->name = $request->name;
            $shift->save();
            DB::commit();

            return $this->responseSuccess([], 'Shifts have been fetched successfully.');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->responseError([], $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        $shift = Shift::where('id', $id)->first();
        if (! $shift) {
            return $this->responseError([], _lang('Something went wrong. shift can not be found.'), 404);
        }

        return $this->responseSuccess($shift, 'Shifts have been show successfully.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|max:255',
        ]);

        $shift = Shift::where('id', $id)->first();
        if (! $shift) {
            return $this->responseError([], _lang('Something went wrong. shift can not be found.'), 404);
        }

        DB::beginTransaction();
        try {
            $shift->update([
                'name' => $request->name,
            ]);

            DB::commit();

            return $this->responseSuccess([], 'Shifts have been update successfully.');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $shift = Shift::where('id', $id)->first();
        if (! empty($shift)) {
            $shift->delete();

            return $this->responseSuccess([], 'Shifts have been delete successfully.');
        } else {
            return $this->responseError([], _lang('Something went wrong. Shift can not be delete.'));
        }
    }
}
