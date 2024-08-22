<?php

namespace Modules\Finance\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Modules\Academic\Models\Section;
use Modules\Academic\Services\SectionService;
use Modules\Finance\Http\Requests\FeesCreateRequest;
use Modules\Finance\Models\Fee;
use Modules\Finance\Services\AbsentFineService;
use Modules\Finance\Services\FeeService;
use RealRashid\SweetAlert\Facades\Alert;

class FeesController extends Controller
{
    public function __construct(
        private readonly SectionService $sectionService,
        private readonly FeeService $feeService,
        private readonly AbsentFineService $absentFineService
    ) {}

    public function store(FeesCreateRequest $request): Redirector|RedirectResponse
    {
        $studentSectionGet = Section::where('class_id', $request->class_id)->where('student_group_id', $request->group_id)->get();
        if (! $studentSectionGet) {
            Alert::error('Error', _lang('Something went wrong. Fee can not be submitted.'));

            return redirect('amount-config');
        }

        // Initialize an array to store all fee_head results
        $fee_heads = [];
        foreach ($studentSectionGet as $studentSection) {
            $requestData = array_merge($request->validated(), ['section_id' => $studentSection->id]);

            // Create fee_head using merged request data
            $fee_head = $this->feeService->createFee($requestData);

            // Store fee_head result
            $fee_heads[] = $fee_head;
        }

        // Check if any fee_head is created
        if (empty(array_filter($fee_heads))) {
            Alert::error('Error', _lang('Something went wrong. Fee can not be submitted.'));

            return redirect('amount-config');
        }

        Alert::toast(_lang('Fee has been create successfully'), 'success');

        return redirect('amount-config');
    }

    public function edit(int $id): Factory|View
    {
        return view('finance::amount_config.fee_amount_edit', [
            'sections' => $this->sectionService->getSections(),
            'fee' => $this->feeService->findFeeById((int) $id),
            'absentFines' => $this->absentFineService->getAbsentFines(),
        ]);
    }

    public function update(FeesCreateRequest $request, int $id): Redirector|RedirectResponse
    {
        $fee_head = $this->feeService->updateFee($request->validated(), $id);

        if (! $fee_head) {
            Alert::error('Error', _lang('Something went wrong. Fee can not be edit.'));

            return redirect('amount-config');
        }

        Alert::toast(_lang('Fee has been update successfully'), 'success');

        return redirect('amount-config');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $fee = Fee::where('id', $id)->first();

        if (! empty($fee)) {
            $fee->delete();

            Alert::toast(_lang('Fee has been delete successfully'), 'success');

            return redirect('amount-config');
        } else {
            Alert::error('Error', _lang('Something went wrong. Fee can not be delete.'));

            return redirect('amount-config');
        }
    }
}
