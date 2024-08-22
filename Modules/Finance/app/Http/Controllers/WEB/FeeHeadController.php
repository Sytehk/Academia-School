<?php

namespace Modules\Finance\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Accounting\Http\Requests\AccountingFundCreateRequest;
use Modules\Finance\Services\FeeHeadService;
use Modules\Finance\Services\FeeSubHeadService;
use Modules\Finance\Services\WaiverService;
use RealRashid\SweetAlert\Facades\Alert;

class FeeHeadController extends Controller
{
    public function __construct(
        private readonly FeeHeadService $feeHeadService,
        private readonly FeeSubHeadService $feeSubHeadService,
        private readonly WaiverService $waiverService,
    ) {}

    public function index(): View|Factory
    {
        return view('finance::fees_page.index', [
            'fee_heads' => $this->feeHeadService->getFeeHeads(),
            'fee_sub_heads' => $this->feeSubHeadService->getFeeSubHeads(),
            'fee_waivers' => $this->waiverService->getWaivers(),
        ]);
    }

    public function store(AccountingFundCreateRequest $request)
    {
        $fee_head = $this->feeHeadService->createFeeHead($request->validated());

        if (! $fee_head) {
            Alert::error('Error', _lang('Something went wrong. FeeHead can not be submitted.'));

            return redirect('fee-head');
        }

        Alert::toast(_lang('Fee-Head has been create successfully'), 'success');

        return redirect('fee-head');
    }

    public function edit(int $id)
    {
        $department = $this->feeHeadService->findFeeHeadById($id);

        if (! $department) {
            Alert::error('Error', _lang('Something went wrong. FeeHead can not be edit.'));

            return redirect('fee-head');
        }

        return view('finance::departments.edit', [
            'department' => $department,
        ]);
    }

    public function update(AccountingFundCreateRequest $request, int $id): Redirector|RedirectResponse
    {
        $department = $this->feeHeadService->findFeeHeadById($id);

        if (! $department) {
            Alert::error('Error', _lang('Something went wrong. FeeHead can not be update.'));

            return redirect('fee-head');
        }

        $this->feeHeadService->updateFeeHead($request->validated(), $id);

        Alert::toast(_lang('Fee-Head has been updated successfully'), 'success');

        return redirect('fee-head');
    }

    public function feeHeadDelete($id): Redirector|RedirectResponse
    {
        $fee_head = $this->feeHeadService->deleteFeeHeadById($id);

        if (! $fee_head) {
            Alert::error('Error', _lang('Something went wrong. FeeHead can not be deleted.'));

            return redirect('fee-head');
        }

        Alert::toast(_lang('Fee-Head has been deleted successfully'), 'success');

        return redirect('fee-head');
    }

    public function serialUpdate(Request $request)
    {
        $feeHeadIds = $request->input('fee_head_ids');
        $serials = $request->input('serials');

        foreach ($feeHeadIds as $index => $feeHeadId) {
            $serial = $serials[$index]; // Get the corresponding serial from the serials array
            $department = $this->feeHeadService->findFeeHeadById($feeHeadId);
            $department->update(['serial' => $serial]);
        }

        Alert::toast(_lang('Fee-Head Serial has been updated successfully'), 'success');

        return redirect('fee-head');
    }
}
