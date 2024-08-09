<?php

namespace Modules\QuestionBank\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\QuestionBank\Http\Requests\QuestionCategory\QuestionCategoryStoreRequest;
use Modules\QuestionBank\Http\Requests\QuestionCategory\QuestionCategoryUpdateRequest;
use Modules\QuestionBank\Models\QuestionCategory;
use Modules\QuestionBank\Repositories\QuestionCategoryRepository;

class QuestionCategoryController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private QuestionCategoryRepository $questionCategoryRepository) {}

    public function getSubjectsByCategory(Request $request)
    {
        $query = QuestionCategory::with(['subjects' => function ($q) use ($request) {
            // Subject filtering
            if ($request->filled('subject_id')) {
                $q->where('id', $request->subject_id);
            }
            if ($request->filled('subject_name')) {
                $q->where('name', 'like', '%'.$request->subject_name.'%');
            }
        }]);

        // Category filtering
        if ($request->filled('category_id')) {
            $query->where('id', $request->category_id);
        }

        if ($request->filled('category_name')) {
            $query->where('name', 'like', '%'.$request->category_name.'%');
        }

        // Global search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%');
            })->orWhereHas('subjects', function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%');
            });
        }

        $perPage = $request->get('per_page', 10);
        $categories = $query->paginate($perPage);

        return $this->responseSuccess(
            $categories,
            'Question Categories with Subjects fetched successfully.'
        );
    }

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->questionCategoryRepository->getAll(request()->all()),
                'Question Category has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(QuestionCategoryStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->questionCategoryRepository->create($request->all()),
                'Question Category has been created successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->questionCategoryRepository->getById($id),
                'Question Category has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(QuestionCategoryUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->questionCategoryRepository->update($id, $this->getUpdateRequest($request)),
                'Question Category has been updated successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->questionCategoryRepository->delete($id),
                'Question Category has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }
}
