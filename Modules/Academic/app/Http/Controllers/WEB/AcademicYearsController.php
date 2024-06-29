<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Academic\Http\Requests\AcademicYearCreateRequest;
use Modules\Academic\Services\AcademicYearService;
use RealRashid\SweetAlert\Facades\Alert;

class AcademicYearsController extends Controller
{
    public function __construct(private readonly AcademicYearService $academicYearService) {}

    public function index(): View|Factory
    {
        return view('academic::academic_year.index', [
            'academicYears' => $this->academicYearService->getAcademicYears(),
        ]);
    }

    public function create(Request $request): View|Factory
    {
        return view('academic::academic_year.create');
    }

    public function store(AcademicYearCreateRequest $request): Redirector|RedirectResponse
    {
        $academicYear = $this->academicYearService->createAcademicYear($request->validated());

        if (! $academicYear) {
            Alert::error('Error', _lang('Something went wrong. Academic year cannot be submitted.'));

            return redirect()->route('academic-years.index');
        }

        Alert::toast(_lang('Academic year has been create successfully'), 'success');

        return redirect()->route('academic-years.index');
    }

    public function show(Request $request, int $id): Factory|View
    {
        $academicYear = $this->academicYearService->findAcademicYearById($id);

        return view('academic::academic_year.view', compact('academicYear', 'id'));
    }

    public function edit(Request $request, int $id): Redirector|RedirectResponse|Factory|View
    {
        $academicYear = $this->academicYearService->findAcademicYearById($id);

        if (! $academicYear) {
            Alert::error('Error', _lang('Something went wrong. Academic year cannot be submitted.'));

            return redirect()->route('academic-years.index');
        }

        return view('academic::academic_year.edit', compact('academicYear', 'id'));
    }

    public function update(AcademicYearCreateRequest $request, int $id): Redirector|RedirectResponse
    {
        $academicYear = $this->academicYearService->updateAcademicYear($request->validated(), $id);

        if (! $academicYear) {
            Alert::error('Error', _lang('Something went wrong. Academic year cannot be submitted.'));

            return redirect()->route('academic-years.index');
        }

        Alert::toast(_lang('Academic year has been update successfully'), 'success');

        return redirect()->route('academic-years.index');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $current_session = get_option('academic_year');

        if ($id == $current_session) {
            Alert::error('Error', _lang('Sorry, You cannot delete current Academic Session !'));

            return redirect()->route('academic-years.index');
        }

        $academicYear = $this->academicYearService->deleteAcademicYearById($id);

        if (! $academicYear) {
            Alert::error('Error', _lang('Something went wrong. Academic year can not be delete.'));

            return redirect()->route('academic-years.index');
        }

        Alert::toast(_lang('Academic year has been deleted'), 'success');

        return redirect()->route('academic-years.index');
    }
}
