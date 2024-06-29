<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Academic\Http\Requests\StudentCategoryCreateRequest;
use Modules\Academic\Models\StudentCategory;
use Modules\Academic\Services\StudentCategoriesService;

class APIStudentCategoriesController extends Controller
{
    public function __construct(private readonly StudentCategoriesService $studentCategoryService) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 100);
        $studentCategories = $this->studentCategoryService->getStudentCategories([], $perPage);

        return $this->responseSuccess($studentCategories, 'Student Categories fetch successfully.');
    }

    public function store(StudentCategoryCreateRequest $request): JsonResponse
    {
        $instituteId = get_institute_id();
        $branchId = get_branch_id();
        // Manual duplicate check
        $exists = StudentCategory::where('name', $request->name)
            ->where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->first();

        if ($exists) {
            return $this->responseError([], _lang('This Student Category already exists for your institute/branch.'));
        }

        $studentCategory = $this->studentCategoryService->createStudentCategory($request->validated());

        if (! $studentCategory) {
            return $this->responseError([], _lang('Something went wrong. Student Category can not be submitted.'));
        }

        return $this->responseSuccess($studentCategory, _lang('Student Category has been submitted.'));
    }

    public function show(int $id): JsonResponse
    {
        $studentCategory = $this->studentCategoryService->findStudentCategoryById($id);

        if (! $studentCategory) {
            return $this->responseError([], _lang('Something went wrong. Student Category can not be found.'), 404);
        }

        return $this->responseSuccess($studentCategory, _lang('Student Category has been fetch.'));
    }

    public function update(StudentCategoryCreateRequest $request, int $id)
    {
        $studentCategory = $this->studentCategoryService->findStudentCategoryById($id);
        if (! $studentCategory) {
            return $this->responseError([], _lang('Something went wrong. Student Category cannot be found.'), 404);
        }

        $instituteId = get_institute_id();
        $branchId = get_branch_id();
        // Manual duplicate check excluding current record
        $exists = StudentCategory::where('name', $request->name)
            ->where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->where('id', '!=', $id)
            ->first();

        if ($exists) {
            return $this->responseError([], _lang('Another Student Category with this name already exists for your institute/branch.'));
        }

        $this->studentCategoryService->updateStudentCategory($request->validated(), $id);

        return $this->responseSuccess([], _lang('Student Category has been update.'));
    }

    public function destroy(int $id): JsonResponse
    {
        $studentCategory = $this->studentCategoryService->deleteStudentCategoryById($id);

        if (! $studentCategory) {
            return $this->responseError([], _lang('Something went wrong. Student Category can not be delete.'));
        }

        return $this->responseSuccess([], _lang('Student Category has been deleted.'));
    }
}
