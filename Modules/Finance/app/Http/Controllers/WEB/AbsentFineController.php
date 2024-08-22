<?php

namespace Modules\Finance\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Academic\Models\AbsentFine;
use Modules\Finance\Services\AbsentFineService;
use RealRashid\SweetAlert\Facades\Alert;

class AbsentFineController extends Controller
{
    public function __construct(private readonly AbsentFineService $absentFineService) {}

    public function store(Request $request): Redirector|RedirectResponse
    {
        $classId = intval($request->class_id);
        $periodId = intval($request->period_id);

        $data = [
            'class_id' => $classId,
            'period_id' => $periodId,
            'fee_amount' => $request->amount,
        ];

        $fee_head = $this->absentFineService->createAbsentFine($data);

        if (! $fee_head) {
            Alert::error('Error', _lang('Something went wrong. Absent Fine can not be submitted.'));

            return redirect('amount-config');
        }

        Alert::toast('Absent Fine has been submitted.', 'success');

        return redirect('amount-config');
    }

    public function edit(int $id): Factory|View
    {
        return view('finance::amount_config.absent_fee_amount_edit', [
            'absentFine' => $this->absentFineService->findAbsentFineById($id),
        ]);
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        try {
            $classId = $request->class_id;
            $periodId = $request->period_id;

            $data = [
                'class_id' => $classId,
                'period_id' => $periodId,
                'fee_amount' => $request->amount,
            ];

            $fee_head = $this->absentFineService->updateAbsentFine($data, $id);

            if (! $fee_head) {
                Alert::error('Error', 'Failed to update absent fine.');

                return back();
            }

            Alert::toast('Absent Fine has been submitted.', 'success');

            return redirect('amount-config');
        } catch (\Exception $e) {
            Alert::error('Error', $e->getMessage());

            return back();
        }
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $absentFine = AbsentFine::where('id', $id)->first();

        if (! empty($absentFine)) {
            $absentFine->delete();

            Alert::toast(_lang('Successfully Absent Fine Deleted.'), 'success');

            return redirect('amount-config');
        } else {
            Alert::error('Error', _lang('Something went wrong.'));

            return redirect('amount-config');
        }
    }
}
