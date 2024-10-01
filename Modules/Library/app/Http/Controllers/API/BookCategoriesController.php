<?php

namespace Modules\Library\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Library\Models\BookCategory;
use Modules\Library\Services\BookCategoryService;

class BookCategoriesController extends Controller
{
    public function __construct(private readonly BookCategoryService $bookCategoryService) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        try {
            $categories = BookCategory::orderBy('id', 'DESC')->paginate($perPage);

            return $this->responseSuccess(
                $categories,
                _lang('Book Categories has been fetched successfully.')
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'category_name' => 'required|string|max:20',
        ]);

        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        // Manual duplicate check
        $exists = BookCategory::where('category_name', $validatedData['category_name'])
            ->where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->first();

        if ($exists) {
            return $this->responseError([], _lang('This Book Category already exists for your institute/branch.'));
        }

        $validatedData['institute_id'] = $instituteId;
        $validatedData['branch_id'] = $branchId;

        $bookCategory = $this->bookCategoryService->createBookCategory($validatedData);

        if (! $bookCategory) {
            return $this->responseError([], _lang('Something went wrong. Book Category cannot be created.'));
        }

        return $this->responseSuccess($bookCategory, _lang('Book Category has been created.'));
    }

    public function show(int $id): JsonResponse
    {
        $bookCategory = $this->bookCategoryService->findBookCategoryById($id);

        if (! $bookCategory) {
            return $this->responseError([], _lang('Something went wrong. Book Category can not be show.'));
        }

        return $this->responseSuccess($bookCategory, _lang('Book Category has been show.'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validatedData = $request->validate([
            'category_name' => 'required|string|max:20',
        ]);

        $accountingCategory = $this->bookCategoryService->findBookCategoryById($id);

        if (! $accountingCategory) {
            return $this->responseError([], _lang('Something went wrong. Book Category cannot be found.'), 404);
        }

        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        // Duplicate check excluding current record
        $exists = BookCategory::where('category_name', $validatedData['category_name'])
            ->where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->where('id', '!=', $id)
            ->first();

        if ($exists) {
            return $this->responseError([], _lang('Another Book Category with this name already exists for your institute/branch.'));
        }

        $validatedData['institute_id'] = $instituteId;
        $validatedData['branch_id'] = $branchId;

        $bookCategory = $this->bookCategoryService->updateBookCategory($validatedData, $id);

        return $this->responseSuccess($bookCategory, _lang('Book Category has been updated successfully.'));
    }

    public function destroy(int $id): JsonResponse
    {
        $bookCategory = $this->bookCategoryService->findBookCategoryById($id);

        if (! $bookCategory) {
            return $this->responseError([], _lang('Something went wrong. Book Category can not be delete.'));
        }

        $bookCategory->delete();

        return $this->responseSuccess([], _lang('Book Category has been delete.'));
    }
}
