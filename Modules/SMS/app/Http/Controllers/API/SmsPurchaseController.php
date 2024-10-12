<?php

namespace Modules\SMS\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\SMS\Models\SmsBalance;
use Modules\SMS\Models\SmsPurchase;
use RealRashid\SweetAlert\Facades\Alert;

class SmsPurchaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        try {
            $smsPurchases = SmsPurchase::orderBy('id', 'DESC')->paginate($perPage);

            return $this->responseSuccess(
                $smsPurchases,
                _lang('SMS Purchases has been fetched successfully.')
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'sms_gateway' => 'required|string',
            'masking_type' => 'required|string',
            'transaction_date' => 'required|date',
            'no_of_sms' => 'required|integer',
            'amount' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();
            $purchase = SmsPurchase::create([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'sms_gateway' => $request->sms_gateway,
                'masking_type' => $request->masking_type,
                'transaction_date' => $request->transaction_date,
                'no_of_sms' => $request->no_of_sms,
                'amount' => $request->amount,
            ]);

            if ($purchase) {
                $balance = SmsBalance::first();
                if ($balance) {
                    if ($request->masking_type == 'masking') {
                        $newBalance = $balance->masking_balance + $request->no_of_sms;
                        $balance->masking_balance = $newBalance;
                    } elseif ($request->masking_type == 'nonmasking') {
                        $newBalance = $balance->non_masking_balance + $request->no_of_sms;
                        $balance->non_masking_balance = $newBalance;
                    } else {
                        Alert::error('Error', _lang('Something went wrong.'));

                        return back();
                    }

                    $balance->save();
                }
            }
            DB::commit();

            return $this->responseSuccess([], 'SMS Purchase create successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        $SMSPurchase = SmsPurchase::find($id);
        if (! $SMSPurchase) {
            return $this->responseError([], _lang('Something went wrong. SMS Purchase can not be show.'));
        }

        return $this->responseSuccess($SMSPurchase, 'SMS Purchase create successfully.');
    }

    public function update(Request $request, int $id)
    {
        $validatedData = $request->validate([
            'sms_gateway' => 'required|string',
            'masking_type' => 'required|string',
            'transaction_date' => 'required|date',
            'no_of_sms' => 'required|integer',
            'amount' => 'required|numeric',
        ]);

        // Find the purchase record by ID
        $purchase = SmsPurchase::find($id);
        if (! $purchase) {
            return $this->responseError([], _lang('Something went wrong. SMS Purchase can not be update.'));
        }

        $balance = SmsBalance::first();
        $qtyDifferent = $request->no_of_sms - $purchase->no_of_sms;
        // Update balance based on masking type
        if ($request->masking_type === 'masking') {
            $balance->masking_balance += $qtyDifferent;
        } elseif ($request->masking_type === 'nonmasking') {
            $balance->non_masking_balance += $qtyDifferent;
        } else {
            return $this->responseError([], _lang('Something went wrong. SMS Balance not working.'));
        }

        // Save the updated balance
        $balance->save();

        // Update the purchase record with validated data
        $purchase->update($validatedData);

        return $this->responseSuccess([], 'SMS Purchase Update successfully.');
    }

    public function destroy(SmsPurchase $smsPurchase)
    {
        if (! $smsPurchase) {
            return $this->responseError([], _lang('Something went wrong. smsPurchase can not be found.'), 404);
        }

        $smsPurchase->delete();

        return $this->responseSuccess([], 'SMS Purchase Delete successfully.');
    }
}
