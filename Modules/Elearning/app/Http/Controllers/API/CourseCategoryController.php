<?php

namespace Modules\Elearning\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Elearning\Http\Requests\CourseCategory\CourseCategoryStoreRequest;
use Modules\Elearning\Http\Requests\CourseCategory\CourseCategoryUpdateRequest;
use Modules\Elearning\Models\CourseCategory;
use Modules\Elearning\Repositories\CourseCategoryRepository;

class CourseCategoryController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private CourseCategoryRepository $courseCategoryRepository) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 20);
        $page = (int) $request->get('page', 1);
        $search = $request->get('search');
        $categoryId = $request->get('category_id');

        $query = CourseCategory::with('subCategories')
            ->where('status', 1);

        if ($categoryId) {
            $query->where('id', $categoryId);
        } else {
            $query->whereNull('parent_id');
        }

        if ($search) {
            $query->where('name', 'like', "%$search%");
        }

        $categories = $query
            ->orderBy('priority')
            ->get();

        $flattened = $this->flattenCategories($categories);

        $paginator = new LengthAwarePaginator(
            array_slice($flattened, ($page - 1) * $perPage, $perPage),
            count($flattened),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return $this->responseSuccess($paginator, 'CourseCategory has been fetched successfully.');
    }

    protected function flattenCategories($categories, $depth = 0)
    {
        $flat = [];

        foreach ($categories as $category) {
            $subCount = $category->subCategories->count();
            $prefix = $depth > 0 ? str_repeat('— ', $depth) : '';

            $flat[] = [
                'id' => $category->id,
                'name' => $prefix.$category->name,
                'depth' => $depth,
                'sub_category_count' => $subCount,
                'description' => $category->description,
                'image' => $category->image,
                'bg_color' => $category->bg_color,
            ];

            if ($subCount > 0) {
                $flat = array_merge(
                    $flat,
                    $this->flattenCategories($category->subCategories, $depth + 1)
                );
            }
        }

        return $flat;
    }

    public function store(CourseCategoryStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->courseCategoryRepository->create($request->all()),
                'CourseCategory has been created successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->courseCategoryRepository->getById($id),
                'CourseCategory has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(CourseCategoryUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->courseCategoryRepository->update($id, $this->getUpdateRequest($request)),
                'CourseCategory has been updated successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $courseCategory = CourseCategory::where('id', $id)->with('subCategories', 'courses')->first();
        if (! $courseCategory) {
            return $this->responseError([], 'Course Category not found.', 404);
        }
        if ($courseCategory->courses->count() > 0) {
            return $this->responseError([], 'This category has courses. Please delete them first.', 400);
        }

        try {
            return $this->responseSuccess(
                $this->courseCategoryRepository->delete($id),
                'CourseCategory has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function courseCategoryList(Request $request)
    {
        // Fetch all active sub-categories where 'parent_id' is not null
        $categories = CourseCategory::where('status', 1)
            ->whereNull('parent_id')
            ->get();

        return $this->responseSuccess(
            $categories,
            'Course Category has been deleted successfully.'
        );
    }

    public function courseSubCategoryList($categoryId)
    {
        // Get parent category (for example, "Admission Test Preparation - 2023")
        $parentCategory = CourseCategory::find($categoryId);

        // Get sub-categories for this parent
        $subCategories = $parentCategory->subCategories;

        return $this->responseSuccess(
            $subCategories,
            'Course Sub-Category has been deleted successfully.'
        );
    }
}
