<?php

namespace Modules\SMS\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\SMS\Http\Requests\PhoneBookRequest;
use Modules\SMS\Services\PhoneBookCategoryService;
use Modules\SMS\Services\PhoneBookService;

class PhoneBookController extends Controller
{
    public function __construct(
        private readonly PhoneBookService $phoneBookService,
        private readonly PhoneBookCategoryService $phoneBookCategoryService
    ) {}

    public function index(): JsonResponse
    {
        $phoneBooks = $this->phoneBookService->getPhoneBooks();

        return $this->responseSuccess($phoneBooks, 'Phone Books fetch successfully.');
    }

    public function store(PhoneBookRequest $request): JsonResponse
    {
        $this->phoneBookService->store($request->validated());

        return $this->responseSuccess([], 'Phone Book has been create successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $phoneBook = $this->phoneBookService->findById($id);

        if (! $phoneBook) {
            return $this->responseError([], _lang('Something went wrong. Phone book not found.'));
        }

        return $this->responseSuccess($phoneBook, 'Phone Book has been show successfully.');
    }

    public function update(PhoneBookRequest $request, int $id): JsonResponse
    {
        $phoneBook = $this->phoneBookService->findById($id);
        if (! $phoneBook) {
            return $this->responseError([], _lang('Something went wrong. Phone book not found.'));
        }

        $this->phoneBookService->update($request->validated(), $id);

        return $this->responseSuccess([], 'Phone Book has been update successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $phoneBook = $this->phoneBookService->findById($id);
        if (! $phoneBook) {
            return $this->responseError([], _lang('Something went wrong. Phone book not found.'));
        }

        $this->phoneBookService->delete($id);

        return $this->responseSuccess([], 'Phone Book has been delete successfully.');
    }
}
