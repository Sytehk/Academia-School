<?php

namespace Modules\Payroll\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Modules\Payroll\Http\Requests\SalaryHeadCreateRequest;
use Modules\Payroll\Services\SalaryHeadService;

class SalaryHeadController extends Controller
{
    public function __construct(
        private readonly SalaryHeadService $salaryHead
    ) {}

    public function index(): Renderable
    {
        return view('payroll::salary_head.index', [
            'salaryHeads' => $this->salaryHead->getSalaryHeads(),
        ]);
    }

    public function store(SalaryHeadCreateRequest $request): RedirectResponse|Redirector
    {
        $salaryHead = $this->salaryHead->createSalaryHead($request->validated());

        if (! $salaryHead) {
            return redirect('salary-heads')->with('error', _lang('Something went wrong. Salary head can not be submitted.'));
        }

        return redirect('salary-heads')->with('success', _lang('Salary head application has been submitted.'));
    }

    public function edit($id): Renderable
    {
        $salaryHead = $this->salaryHead->findSalaryHeadById($id);
        if (! $salaryHead) {
            abort(404);
        }

        return view('payroll::salary_head.edit', [
            'salaryHead' => $salaryHead,
        ]);
    }

    public function update(SalaryHeadCreateRequest $request, int $id): RedirectResponse|Redirector
    {
        $salaryHead = $this->salaryHead->findSalaryHeadById($id);

        if (! $salaryHead) {
            abort(404);
        }

        $this->salaryHead->updateSalaryHead($request->validated(), $id);

        return redirect('salary-heads')->with('success', _lang('Information has been added'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse|Redirector
    {
        $salaryHead = $this->salaryHead->findSalaryHeadById($id);

        if (! $salaryHead) {
            abort(404);
        }

        $this->salaryHead->deleteSalaryHeadById($id);

        return redirect('salary-heads')->with('success', _lang('Information has been deleted'));
    }
}
