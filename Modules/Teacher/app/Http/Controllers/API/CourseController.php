<?php

namespace Modules\Teacher\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Elearning\Http\Requests\Course\CourseStoreRequest;
use Modules\Elearning\Http\Requests\Course\CourseUpdateRequest;
use Modules\Elearning\Models\Course;
use Modules\Elearning\Repositories\CourseRepository;

/**
 * Teacher-scoped course management, reusing the same Course model, validation
 * requests, and repository as the Admin course controller
 * (Modules\Elearning\Http\Controllers\API\CourseController) — left untouched.
 * The only difference is authorization: a teacher may only read/edit/delete
 * courses they themselves created (courses.created_by), on top of the
 * institute/branch scoping BelongsToTenant already applies to every Course
 * query for an authenticated user.
 */
class CourseController extends Controller
{
    public function __construct(private readonly CourseRepository $courseRepository) {}

    private function ownedCourseQuery()
    {
        return Course::where('created_by', Auth::id());
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,schedule,published,private',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = $this->ownedCourseQuery()->with(['courseCategory:id,name']);

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $courses = $query->orderByDesc('created_at')->paginate($request->input('per_page', 10));

        return $this->responseSuccess($courses, 'Courses fetched successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $course = $this->ownedCourseQuery()
            ->with(['courseCategory', 'courseSubCategory', 'courseFaqs', 'courseChapters.contents'])
            ->find($id);

        if (! $course) {
            return $this->responseError([], 'Course not found.', 404);
        }

        return $this->responseSuccess($course, 'Course fetched successfully.');
    }

    public function store(CourseStoreRequest $request): JsonResponse
    {
        try {
            // create() stamps created_by/institute_id/branch_id from the
            // authenticated user, so the calling teacher becomes the owner.
            return $this->responseSuccess(
                $this->courseRepository->create($request->validated()),
                'Course has been created successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode() ?: 500);
        }
    }

    public function update(CourseUpdateRequest $request, int $id): JsonResponse
    {
        if (! $this->ownedCourseQuery()->whereKey($id)->exists()) {
            return $this->responseError([], 'Course not found.', 404);
        }

        try {
            return $this->responseSuccess(
                $this->courseRepository->update($id, $request->validated()),
                'Course has been updated successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode() ?: 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        if (! $this->ownedCourseQuery()->whereKey($id)->exists()) {
            return $this->responseError([], 'Course not found.', 404);
        }

        if ($this->courseRepository->hasProtectedRelations($id)) {
            return $this->responseError([], 'Cannot delete course. Related data exists.', 409);
        }

        try {
            $this->courseRepository->delete($id);

            return $this->responseSuccess([], 'Course has been deleted successfully.');
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode() ?: 500);
        }
    }
}
