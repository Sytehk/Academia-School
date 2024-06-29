<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Academic\Models\Period;
use Modules\Academic\Services\PeriodService;
use RealRashid\SweetAlert\Facades\Alert;

class PeriodController extends Controller
{
    public function __construct(
        private readonly PeriodService $periodService
    ) {}

    public function index(): View|Factory
    {
        return view('academic::period.list', [
            'periods' => $this->periodService->getPeriods(),
        ]);
    }

    public function create(Request $request): Factory|View
    {
        return view('academic::period.create');
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'period' => [
                'required',
                'max:50',
                Rule::unique('periods')
                    ->where(function ($query) {
                        return $query->where('institute_id', get_institute_id())
                            ->where('branch_id', get_branch_id());
                    }),
            ],
            'serial_no' => 'nullable|numeric',
        ], [
            'period.unique' => 'This period already exists for the selected institute and branch.',
        ]);

        $period = new Period;
        $period->institute_id = get_institute_id();
        $period->branch_id = get_branch_id();
        $period->serial_no = $request->serial_no;
        $period->period = $request->period;
        $period->save();

        Alert::toast('Period has been created successfully.', 'success');

        return redirect('periods');
    }

    public function edit(int $id): Factory|Redirector|RedirectResponse|View
    {
        $period = $this->periodService->findPeriodById($id);

        if (! $period) {
            Alert::error('Error', _lang('Something went wrong. Period can not be edit.'));

            return redirect('periods');
        }

        return view('academic::period.edit', [
            'period' => $period,
        ]);
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'period' => [
                'required',
                'max:50',
                Rule::unique('periods')
                    ->ignore($id)
                    ->where(function ($query) {
                        return $query->where('institute_id', get_institute_id())
                            ->where('branch_id', get_branch_id());
                    }),
            ],
            'serial_no' => 'nullable|numeric',
        ], [
            'period.unique' => 'This period already exists for this institute and branch.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $period = $this->periodService->findPeriodById($id);

        if (! $period) {
            Alert::error('Error', _lang('Something went wrong. Period cannot be updated.'));

            return redirect('periods');
        }

        $this->periodService->updatePeriod($request->all(), $id);

        Alert::toast('Period has been updated successfully.', 'success');

        return redirect('periods');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $fee_head = $this->periodService->deletePeriodById($id);

        if (! $fee_head) {
            Alert::error('Error', _lang('Something went wrong. Period can not be delete.'));

            return redirect('periods');
        }

        Alert::toast('Period has been delete successfully.', 'success');

        return redirect('periods');
    }
}
