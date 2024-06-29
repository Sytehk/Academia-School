<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Modules\Academic\Http\Requests\ClassCreateRequest;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Services\ClassService;
use RealRashid\SweetAlert\Facades\Alert;

class ClassController extends Controller
{
    public function __construct(private readonly ClassService $classService) {}

    public function index(): View|Factory
    {
        return view('academic::classes.index', [
            'classes' => $this->classService->getClasses(),
        ]);
    }

    public function store(ClassCreateRequest $request): Redirector|RedirectResponse
    {
        $class = $this->classService->createClass($request->validated());

        if (! $class) {
            Alert::error('Error', _lang('Something went wrong. Class can not be create.'));

            return redirect('class');
        }

        Alert::toast('Class has been create successfully.', 'success');

        return redirect('class');
    }

    public function edit(int $id): Factory|Redirector|RedirectResponse|View
    {
        $class = $this->classService->findClassById($id);

        if (! $class) {
            Alert::error('Error', _lang('Something went wrong. Class can not be edit.'));

            return redirect('class');
        }

        return view('academic::classes.edit', [
            'class' => $class,
        ]);
    }

    public function update(ClassCreateRequest $request, int $id): Redirector|RedirectResponse
    {
        $class = $this->classService->findClassById($id);

        if (! $class) {
            Alert::error('Error', _lang('Something went wrong. Class can not be update.'));

            return redirect('class');
        }

        $this->classService->updateClass($request->validated(), $id);

        Alert::toast('Class has been update successfully.', 'success');

        return redirect('class');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $class = ClassModel::findOrFail($id);
        if ($class->studentSessions()->exists()) {
            Alert::warning(
                'Warning',
                _lang('This class cannot be deleted because students are assigned to it.')
            );

            return redirect('class');
        }

        $deleted = $this->classService->deleteClassById($id);

        if (! $deleted) {
            Alert::error(
                'Error',
                _lang('Something went wrong. Class cannot be deleted.')
            );

            return redirect('class');
        }

        Alert::toast('Class has been deleted successfully.', 'success');

        return redirect('class');
    }
}
