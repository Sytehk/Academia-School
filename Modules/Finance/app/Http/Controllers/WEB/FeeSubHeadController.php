<?php

namespace Modules\Finance\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Accounting\Http\Requests\AccountingFundCreateRequest;
use Modules\Finance\Http\Requests\FeeSubHeadCreateRequest;
use Modules\Finance\Services\FeeSubHeadService;
use RealRashid\SweetAlert\Facades\Alert;

class FeeSubHeadController extends Controller
{
    public function __construct(private readonly FeeSubHeadService $feeSubHeadService) {}

    public function index(): View|Factory
    {
        return view('finance::fees_page.index', [
            'fee_sub_heads' => $this->feeSubHeadService->getFeeSubHeads(),
        ]);
    }

    public function store(FeeSubHeadCreateRequest $request): Redirector|RedirectResponse
    {
        $fee_sub_head = $this->feeSubHeadService->createFeeSubHead($request->validated());

        if (! $fee_sub_head) {
            Alert::error('Error', _lang('Something went wrong. Fee-Sub-Head can not be submitted.'));

            return redirect('fee-head');
        }

        Alert::toast(_lang('Fee-Head has been create successfully'), 'success');

        return redirect('fee-head');
    }

    public function edit(int $id): Factory|Redirector|RedirectResponse|View
    {
        $fee_sub_head = $this->feeSubHeadService->findFeeSubHeadById($id);

        if (! $fee_sub_head) {
            Alert::error('Error', _lang('Something went wrong. Fee-Sub-Head can not be edit.'));

            return redirect('fee-head');
        }

        return view('finance::fee_sub_heads.edit', [
            'fee_sub_head' => $fee_sub_head,
        ]);
    }

    public function update(AccountingFundCreateRequest $request, int $id): Redirector|RedirectResponse
    {
        $fee_sub_head = $this->feeSubHeadService->findFeeSubHeadById($id);

        if (! $fee_sub_head) {
            Alert::error('Error', _lang('Something went wrong. Fee-Sub-Head can not be update.'));

            return redirect('fee-head');
        }

        $this->feeSubHeadService->updateFeeSubHead($request->validated(), $id);

        Alert::toast(_lang('Fee-Head has been updated successfully'), 'success');

        return redirect('fee-head');
    }

    public function feeSubHeadDelete($id): Redirector|RedirectResponse
    {
        $fee_sub_head = $this->feeSubHeadService->deleteFeeSubHeadById($id);

        if (! $fee_sub_head) {
            Alert::error('Error', _lang('Something went wrong. Fee-Sub-Head can not be deleted.'));

            return redirect('fee-head');
        }

        Alert::toast(_lang('Fee-Head has been deleted successfully'), 'success');

        return redirect('fee-head');
    }

    public function serialFeeSubHeadUpdate(Request $request): Redirector|RedirectResponse
    {
        $feeHeadIds = $request->input('fee_sub_head_ids');
        $serials = $request->input('serials');

        foreach ($feeHeadIds as $index => $feeHeadId) {
            $serial = $serials[$index];
            $department = $this->feeSubHeadService->findFeeSubHeadById($feeHeadId);
            $department->update(['serial' => $serial]);
        }

        Alert::toast(_lang('Fee-Head has been updated serially successfully'), 'success');

        return redirect('fee-head');
    }
}
