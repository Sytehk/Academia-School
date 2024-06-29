<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Academic\Http\Requests\ClassCreateRequest;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Section;
use Modules\Academic\Services\ClassService;

class APIClassController extends Controller
{
    public function __construct(private readonly ClassService $classService) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $classes = ClassModel::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);

        return $this->responseSuccess($classes, 'Classes fetch successfully.');
    }

    public function store(ClassCreateRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Add institute_id and branch_id from current user
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        // Check if class already exists in this institute/branch
        $exists = ClassModel::where('class_name', $data['class_name'])
            ->where('institute_id', $data['institute_id'])
            ->where('branch_id', $data['branch_id'])
            ->first();

        if ($exists) {
            return $this->responseError([], _lang('This class already exists in your institute/branch.'));
        }

        $class = $this->classService->createClass($data);

        if (! $class) {
            return $this->responseError([], _lang('Something went wrong. Class cannot be created.'));
        }

        return $this->responseSuccess([], _lang('Class has been created successfully.'));
    }

    public function show(int $id): JsonResponse
    {
        $class = $this->classService->findClassById($id);

        if (! $class) {
            return $this->responseError([], _lang('Something went wrong. Class can not be found.'), 404);
        }

        return $this->responseSuccess($class, _lang('Class has been submitted.'));
    }

    public function update(ClassCreateRequest $request, int $id): JsonResponse
    {
        $class = $this->classService->findClassById($id);

        if (! $class) {
            return $this->responseError([], _lang('Something went wrong. Class cannot be found.'), 404);
        }

        $data = $request->validated();
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        // Check if another class with the same name exists in this institute/branch
        $exists = ClassModel::where('class_name', $data['class_name'])
            ->where('institute_id', $data['institute_id'])
            ->where('branch_id', $data['branch_id'])
            ->where('id', '!=', $id)
            ->first();

        if ($exists) {
            return $this->responseError([], _lang('Another class with this name already exists in your institute/branch.'));
        }

        $this->classService->updateClass($data, $id);

        return $this->responseSuccess([], _lang('Class has been updated successfully.'));
    }

    public function destroy(int $id): JsonResponse
    {
        $section = Section::where('class_id', $id)->first();
        if ($section) {
            return $this->responseError([], _lang('Class cannot be deleted because it has assigned sessions.'));
        }

        $class = $this->classService->deleteClassById($id);
        if (! $class) {
            return $this->responseError([], _lang('Something went wrong. Class can not be delete.'));
        }

        return $this->responseSuccess([], _lang('Class has been deleted.'));
    }
}
