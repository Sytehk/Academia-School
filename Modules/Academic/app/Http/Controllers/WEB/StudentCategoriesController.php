<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Modules\Academic\Http\Requests\StudentCategoryCreateRequest;
use Modules\Academic\Services\StudentCategoriesService;
use RealRashid\SweetAlert\Facades\Alert;

class StudentCategoriesController extends Controller
{
    public function __construct(private readonly StudentCategoriesService $studentCategoryService) {}

    public function index(): View|Factory
    {
        return view('academic::student_categories.index', [
            'student_categories' => $this->studentCategoryService->getStudentCategories(),
        ]);
    }

    public function store(StudentCategoryCreateRequest $request): Redirector|RedirectResponse
    {
        $studentCategory = $this->studentCategoryService->createStudentCategory($request->validated());

        if (! $studentCategory) {
            Alert::error('Error', _lang('Something went wrong. Student Category can not be create.'));

            return redirect('student-categories');
        }

        Alert::toast('Student Category has been create.', 'success');

        return redirect('student-categories');
    }

    public function edit(int $id): Factory|Redirector|RedirectResponse|View
    {
        $studentCategory = $this->studentCategoryService->findStudentCategoryById($id);

        if (! $studentCategory) {
            Alert::error('Error', _lang('Something went wrong. Student Category can not be edit.'));

            return redirect('student-categories');
        }

        return view('academic::student_categories.edit', [
            'studentCategory' => $studentCategory,
        ]);
    }

    public function update(StudentCategoryCreateRequest $request, int $id): Redirector|RedirectResponse
    {
        $studentCategory = $this->studentCategoryService->findStudentCategoryById($id);

        if (! $studentCategory) {
            Alert::error('Error', _lang('Something went wrong. Student Category can not be update.'));

            return redirect('student-categories');
        }

        $this->studentCategoryService->updateStudentCategory($request->validated(), $id);

        Alert::toast('Student Category has been update.', 'success');

        return redirect('student-categories');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $studentCategory = $this->studentCategoryService->deleteStudentCategoryById($id);

        if (! $studentCategory) {
            Alert::error('Error', _lang('Something went wrong. Student Category can not be delete.'));

            return redirect('student-categories');
        }

        Alert::toast('Student Category has been delete.', 'success');

        return redirect('student-categories');
    }
}
