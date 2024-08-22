<?php

namespace Modules\Finance\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Finance\Http\Requests\WaiverCreateRequest;
use Modules\Finance\Services\WaiverService;
use RealRashid\SweetAlert\Facades\Alert;

class WaiversController extends Controller
{
    public function __construct(private readonly WaiverService $waiverService) {}

    public function index(): View|Factory
    {
        return view('finance::waivers.index', [
            'waivers' => $this->waiverService->getWaivers(),
        ]);
    }

    public function store(WaiverCreateRequest $request): Redirector|RedirectResponse
    {
        $waiver = $this->waiverService->createWaiver($request->validated());

        if (! $waiver) {
            Alert::error('Error', _lang('Something went wrong. Waiver can not be submitted.'));

            return redirect('waivers');
        }

        Alert::toast(_lang('Waiver has been create successfully'), 'success');

        return redirect('waivers');
    }

    public function edit(int $id): Factory|Redirector|RedirectResponse|View
    {
        $waiver = $this->waiverService->findWaiverById($id);

        if (! $waiver) {
            Alert::error('Error', _lang('Something went wrong. Waiver can not be edit.'));

            return redirect('waivers');
        }

        return view('finance::waivers.edit', [
            'waiver' => $waiver,
        ]);
    }

    public function update(WaiverCreateRequest $request, int $id): Redirector|RedirectResponse
    {
        $waiver = $this->waiverService->findWaiverById($id);

        if (! $waiver) {
            Alert::error('Error', _lang('Something went wrong. Waiver can not be update.'));

            return redirect('waivers');
        }

        $this->waiverService->updateWaiver($request->validated(), $id);

        Alert::toast(_lang('Waiver has been updated successfully'), 'success');

        return redirect('waivers');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $waiver = $this->waiverService->deleteWaiverById($id);

        if (! $waiver) {
            Alert::error('Error', _lang('Something went wrong. Waiver can not be delete.'));

            return redirect('waivers');
        }

        Alert::toast(_lang('Waiver has been delete successfully'), 'success');

        return redirect('waivers');
    }

    public function waiverDelete($id): Redirector|RedirectResponse
    {
        $feeWaiver = $this->waiverService->deleteWaiverById($id);

        if (! $feeWaiver) {
            Alert::error('Error', _lang('Something went wrong. Fee Waiver can not be submitted.'));

            return redirect('fee-head');
        }

        Alert::toast(_lang('Fee Waiver has been delete successfully'), 'success');

        return redirect('fee-head');
    }

    public function serialWaiverUpdate(Request $request): Redirector|RedirectResponse
    {
        $waiverIds = $request->input('fee_waiver_ids');
        $serials = $request->input('serials');

        foreach ($waiverIds as $index => $waiverId) {
            $serial = $serials[$index];
            $department = $this->waiverService->findWaiverById($waiverId);
            $department->update(['serial' => $serial]);
        }

        Alert::toast(_lang('Fee Waiver has been serially update successfully'), 'success');

        return redirect('fee-head');
    }
}
