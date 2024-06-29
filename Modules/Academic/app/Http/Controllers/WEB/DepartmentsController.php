<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Modules\Academic\Http\Requests\DepartmentCreateRequest;
use Modules\Academic\Services\DepartmentService;
use RealRashid\SweetAlert\Facades\Alert;

class DepartmentsController extends Controller
{
    public function __construct(private readonly DepartmentService $departmentService) {}

    public function index(): View|Factory
    {
        return view('academic::departments.index', [
            'departments' => $this->departmentService->getDepartments(),
        ]);
    }

    public function store(DepartmentCreateRequest $request): Redirector|RedirectResponse
    {
        $department = $this->departmentService->createDepartment($request->validated());

        if (! $department) {
            Alert::error('Error', _lang('Something went wrong. Department can not be create.'));

            return redirect('departments');
        }

        Alert::toast('Department has been create successfully.', 'success');

        return redirect('departments');
    }

    public function edit(int $id): Factory|Redirector|RedirectResponse|View
    {
        $department = $this->departmentService->findDepartmentById($id);

        if (! $department) {
            Alert::error('Error', _lang('Something went wrong. Department can not be edit.'));

            return redirect('departments');
        }

        return view('academic::departments.edit', [
            'department' => $department,
        ]);
    }

    public function update(DepartmentCreateRequest $request, int $id): Redirector|RedirectResponse
    {
        $department = $this->departmentService->findDepartmentById($id);

        if (! $department) {
            Alert::error('Error', _lang('Something went wrong. Department can not be update.'));

            return redirect('departments');
        }

        $this->departmentService->updateDepartment($request->validated(), $id);

        Alert::toast('Department has been update successfully.', 'success');

        return redirect('departments');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $department = $this->departmentService->deleteDepartmentById($id);

        if (! $department) {
            Alert::error('Error', _lang('Something went wrong. Department can not be delete.'));

            return redirect('departments');
        }

        Alert::toast('Department has been delete successfully.', 'success');

        return redirect('departments');
    }
}
