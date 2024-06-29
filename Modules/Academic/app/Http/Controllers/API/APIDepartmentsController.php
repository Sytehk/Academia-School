<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Academic\Http\Requests\DepartmentCreateRequest;
use Modules\Academic\Http\Requests\DepartmentUpdateRequest;
use Modules\Academic\Models\Department;
use Modules\Academic\Services\DepartmentService;

class APIDepartmentsController extends Controller
{
    public function __construct(private readonly DepartmentService $departmentService) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $departments = Department::select('id', 'department_name', 'priority')->paginate($perPage);

        return $this->responseSuccess($departments, 'Departments fetch successfully.');
    }

    public function store(DepartmentCreateRequest $request): JsonResponse
    {
        $department = $this->departmentService->createDepartment($request->validated());

        if (! $department) {
            return $this->responseError([], _lang('Something went wrong. Department can not be create.'));
        }

        return $this->responseSuccess([], _lang('Department has been create.'));
    }

    public function show(int $id): JsonResponse
    {
        $department = $this->departmentService->findDepartmentById($id);

        if (! $department) {
            return $this->responseError([], _lang('Something went wrong. Department can not be found.'), 404);
        }

        return $this->responseSuccess($department, _lang('Department has been show.'));
    }

    public function update(DepartmentUpdateRequest $request, int $id): JsonResponse
    {
        $department = $this->departmentService->findDepartmentById($id);

        if (! $department) {
            return $this->responseError([], _lang('Something went wrong. Department can not be found.'), 404);
        }

        $this->departmentService->updateDepartment($request->validated(), $id);

        return $this->responseSuccess([], _lang('Department has been update.'));
    }

    public function destroy(int $id): JsonResponse
    {
        $department = $this->departmentService->deleteDepartmentById($id);
        if (! $department) {
            return $this->responseError([], _lang('Something went wrong. Department can not be delete.'));
        }

        return $this->responseSuccess([], _lang('Department has been deleted.'));
    }
}
