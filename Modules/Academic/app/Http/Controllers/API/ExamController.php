<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\Exam;

class ExamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $exams = Exam::orderBy('id', 'desc')->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);

        return $this->responseSuccess($exams, 'Exams fetch successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|max:255',
            'exam_code' => 'nullable|numeric',
        ]);

        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        // Manual duplicate check
        $exists = Exam::where('name', $request->name)
            ->where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->first();

        if ($exists) {
            return $this->responseError([], _lang('An exam with this name already exists for your institute/branch.'));
        }

        DB::beginTransaction();
        try {
            $exam = new Exam;
            $exam->institute_id = $instituteId;
            $exam->branch_id = $branchId;
            $exam->name = $request->name;
            $exam->exam_code = $request->exam_code;
            $exam->save();

            DB::commit();

            return $this->responseSuccess([], _lang('Exam has been created successfully.'));
        } catch (\Exception $e) {
            DB::rollback();

            return $this->responseError([], $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        $exam = Exam::where('id', $id)->first();

        return $this->responseSuccess($exam, 'Exam has been fetch successfully.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|max:255',
            'exam_code' => 'nullable|numeric',
        ]);

        $exam = Exam::find($id);
        if (! $exam) {
            return $this->responseError([], _lang('Exam not found.'), 404);
        }

        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        // Duplicate check excluding current record
        $exists = Exam::where('name', $request->name)
            ->where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->where('id', '!=', $id)
            ->first();

        if ($exists) {
            return $this->responseError([], _lang('Another exam with this name already exists for your institute/branch.'));
        }

        DB::beginTransaction();
        try {
            $exam->name = $request->name;
            $exam->exam_code = $request->exam_code;
            $exam->update();

            DB::commit();

            return $this->responseSuccess([], _lang('Exam has been updated successfully.'));
        } catch (\Exception $e) {
            DB::rollback();

            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $exam = Exam::where('id', $id)->first();
        if (! empty($exam)) {
            $exam->delete();

            return $this->responseSuccess([], 'Exam has been delete successfully.');
        } else {
            return $this->responseError([], _lang('Something went wrong. Exam can not be delete.'));
        }
    }
}
