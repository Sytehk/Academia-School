<?php

namespace Modules\SMS\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\SMS\Http\Requests\PhoneBookCategoryRequest;
use Modules\SMS\Services\PhoneBookCategoryService;

class PhoneBookCategoryController extends Controller
{
    public function __construct(private readonly PhoneBookCategoryService $phoneBookCategoryService) {}

    public function index(): JsonResponse
    {
        $phoneBookCategories = $this->phoneBookCategoryService->getPhoneBookCategories();

        return $this->responseSuccess($phoneBookCategories, 'Phone Book Categories fetch successfully.');
    }

    public function store(PhoneBookCategoryRequest $request): JsonResponse
    {
        $this->phoneBookCategoryService->store($request->validated());

        return $this->responseSuccess([], 'Phone Book has been create successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $phoneBookCategory = $this->phoneBookCategoryService->findById($id);
        if (! $phoneBookCategory) {
            return $this->responseError([], _lang('Something went wrong. Phone Book not found.'));
        }

        return $this->responseSuccess($phoneBookCategory, 'Phone Book Category show successfully.');
    }

    public function update(PhoneBookCategoryRequest $request, int $id): JsonResponse
    {
        $phoneBookCategory = $this->phoneBookCategoryService->findById($id);
        if (! $phoneBookCategory) {
            return $this->responseError([], _lang('Something went wrong. Phone Book not found.'));
        }

        $this->phoneBookCategoryService->update($request->validated(), $id);

        return $this->responseSuccess([], 'Phone Book has been update successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $phoneBookCategory = $this->phoneBookCategoryService->findById($id);
        if (! $phoneBookCategory) {
            return $this->responseError([], _lang('Something went wrong. Phone Book not found.'));
        }

        $this->phoneBookCategoryService->delete($id);

        return $this->responseSuccess([], 'Phone Book has been delete successfully.');
    }
}
