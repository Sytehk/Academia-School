<?php

namespace Modules\Finance\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Finance\Models\FeeDateConfig;
use Modules\Finance\Models\FeeMap;

class FeeDateConfigController extends Controller
{
    public function dateConfig(Request $request): JsonResponse
    {
        $request->validate([
            'fee_head_id' => 'required|exists:fee_heads,id',
        ]);

        $feeMap = FeeMap::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('fee_head_id', $request->fee_head_id)
            ->where('type', 'fee')
            ->first();

        if (! $feeMap) {
            return $this->responseError([], _lang('FeeMap not found for the provided ID'), 404);
        }

        $feeMapFeeSubHeads = DB::table('fee_map_fee_sub_head')
            ->join('fee_sub_heads', 'fee_map_fee_sub_head.fee_sub_head_id', 'fee_sub_heads.id')
            ->select('fee_map_fee_sub_head.*', 'fee_sub_heads.*')
            ->orderBy('fee_sub_heads.serial', 'asc')
            ->where('fee_map_id', $feeMap->id)
            ->get();

        return $this->responseSuccess(
            $feeMapFeeSubHeads,
            _lang('Fee Sub Head has been fetched successfully.')
        );
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        try {
            $fee_date_configs = FeeDateConfig::join('fee_sub_heads', 'fee_date_configs.fee_sub_head_id', '=', 'fee_sub_heads.id')
                ->where('fee_date_configs.institute_id', get_institute_id())
                ->where('fee_date_configs.branch_id', get_branch_id())
                ->select('fee_date_configs.id', 'fee_date_configs.payable_date_start', 'fee_date_configs.payable_date_end', 'fee_sub_heads.name as fee_sub_head_name')
                ->orderBy('fee_date_configs.id', 'desc')
                ->paginate($perPage);

            return $this->responseSuccess(
                $fee_date_configs,
                _lang('Fee Date Config has been fetched successfully.')
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            // Validation Rules
            $validator = Validator::make($request->all(), [
                'fee_sub_head_id' => 'required|array',
                'fee_sub_head_id.*' => 'required|exists:fee_sub_heads,id', // Ensure each fee_sub_head_id exists in the database
                'payable_date' => 'required|array',
                'payable_date.*' => 'required|date', // Ensure each payable_date is a valid date
                'fine_active_date' => 'required|array',
                'fine_active_date.*' => 'required|date|after_or_equal:payable_date.*', // Ensure fine_active_date is after or equal to payable_date
            ], [
                'fee_sub_head_id.required' => 'Fee Sub Head IDs are required.',
                'fee_sub_head_id.*.required' => 'Each Fee Sub Head ID must be provided.',
                'fee_sub_head_id.*.exists' => 'Each Fee Sub Head ID must be a valid ID.',
                'payable_date.required' => 'Payable Dates are required.',
                'payable_date.*.required' => 'Each Payable Date must be provided.',
                'payable_date.*.date' => 'Each Payable Date must be a valid date.',
                'fine_active_date.required' => 'Fine Active Dates are required.',
                'fine_active_date.*.required' => 'Each Fine Active Date must be provided.',
                'fine_active_date.*.date' => 'Each Fine Active Date must be a valid date.',
                'fine_active_date.*.after_or_equal' => 'Fine Active Date must be after or equal to Payable Date.',
            ]);

            // If validation fails, return the error messages
            if ($validator->fails()) {
                return $this->responseError([], $validator->errors()->first());
            }

            // Check if array lengths match (payable_date, fine_active_date, and fee_sub_head_id)
            $dataCount = count($request->payable_date);
            if ($dataCount !== count($request->fine_active_date) || $dataCount !== count($request->fee_sub_head_id)) {
                return $this->responseError([], _lang('Mismatch in array lengths for payable_date, fine_active_date, or fee_sub_head_id.'));
            }

            // Loop through the data and save each record
            for ($i = 0; $i < $dataCount; $i++) {
                $feeDateConfig = new FeeDateConfig;
                $feeDateConfig->institute_id = get_institute_id(); // Assuming get_institute_id() returns the institute ID
                $feeDateConfig->branch_id = get_branch_id(); // Assuming get_branch_id() returns the branch ID
                $feeDateConfig->fee_sub_head_id = $request->fee_sub_head_id[$i];
                $feeDateConfig->payable_date_start = $request->payable_date[$i];
                $feeDateConfig->payable_date_end = $request->fine_active_date[$i];

                $feeDateConfig->save();
            }

            // Commit the transaction
            DB::commit();

            return $this->responseSuccess([], _lang('Successfully created Fee Date config with related records.'));
        } catch (Exception $exception) {
            // Rollback the transaction in case of error
            DB::rollBack();

            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            // Validate if the ID is a valid integer (this is mostly redundant if using route model binding, but useful here)
            if (! is_numeric($id)) {
                return $this->responseError([], _lang('Invalid ID format.'), 400);
            }

            // Fetch the FeeDateConfig by its ID
            $feeDateConfig = FeeDateConfig::find($id);
            // If no record is found, return an error
            if (! $feeDateConfig) {
                return $this->responseError([], _lang('Fee Date Config not found.'), 404);
            }

            // Return success with the found data
            return $this->responseSuccess(
                ['data' => $feeDateConfig],
                _lang('Fee Date Config retrieved successfully.')
            );
        } catch (Exception $exception) {
            // If something goes wrong, return the error message
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'payable_date' => 'required|date',
                'fine_active_date' => 'required|date|after_or_equal:payable_date', // fine_active_date must be after or equal to payable_date
            ], [
                'payable_date.required' => 'Payable Date is required.',
                'payable_date.date' => 'Payable Date must be a valid date.',
                'fine_active_date.required' => 'Fine Active Date is required.',
                'fine_active_date.date' => 'Fine Active Date must be a valid date.',
                'fine_active_date.after_or_equal' => 'Fine Active Date must be after or equal to Payable Date.',
            ]);

            // If validation fails, return the error messages
            if ($validator->fails()) {
                return $this->responseError([], $validator->errors()->first());
            }

            // Check if the record exists
            $feeDateConfig = FeeDateConfig::find($id);
            if (! $feeDateConfig) {
                return $this->responseError([], _lang('Fee Date Config not found.'), 404);
            }

            // Update the FeeDateConfig record
            $feeDateConfig->payable_date_start = $request->payable_date;
            $feeDateConfig->payable_date_end = $request->fine_active_date;

            // Save the updated record
            $feeDateConfig->save();

            // Commit transaction
            DB::commit();

            return $this->responseSuccess(
                ['data' => $feeDateConfig],
                _lang('Fee Date Config updated successfully.')
            );
        } catch (Exception $exception) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $feeDateConfig = FeeDateConfig::where('id', $id)->first();

        if (! empty($feeDateConfig)) {
            $feeDateConfig->delete();

            return $this->responseSuccess(
                [],
                _lang('Successfully delete Fee Date config with related records.')
            );
        } else {
            return $this->responseError([], _lang('Failed to delete Fee Date config with related records. Please try again.'));
        }
    }
}
