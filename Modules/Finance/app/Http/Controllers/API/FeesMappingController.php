<?php

namespace Modules\Finance\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Services\AccountingLedgerService;
use Modules\Finance\Models\FeeMap;

class FeesMappingController extends Controller
{
    public function __construct(
        private readonly AccountingLedgerService $accountingLedgerService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) ($request->per_page ?: 50); // Default to 50 per page if not provided
        $type = $request->type; // Type filter

        try {
            // Build the query dynamically
            $query = FeeMap::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->with('feeHead', 'feeLedger')->orderBy('id', 'DESC');

            // Apply filter based on the 'type' parameter, if provided
            if ($type) {
                $query->where('type', $type);
            }

            // Paginate the results
            $feeMap = $query->paginate($perPage);

            return $this->responseSuccess(
                $feeMap,
                _lang('Fee Map has been fetched successfully.')
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $feeMap = FeeMap::create([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'fee_head_id' => $request->fee_head_id,
                'fee_sub_head_id' => $request->fee_sub_head_id,
                'ledger_id' => $request->ledger_id,
                'type' => $request->type ?? 'fee',
            ]);

            $feeSubHeadIds = $request->fee_sub_head_ids;
            $feeHeadId = $request->fee_head_id;

            if ($request->type === 'fee') {
                $attachArray = array_map(function ($subHeadId) use ($feeHeadId) {
                    return [
                        'fee_head_id' => $feeHeadId,
                        'fee_sub_head_id' => $subHeadId,
                    ];
                }, $feeSubHeadIds);
                $feeMap->feeSubHeads()->attach($attachArray);
            } elseif ($request->type === 'fee_fine') {
                $feeMap->funds()->attach($request->fund_id);
            }

            $feeMap->funds()->attach($request->fund_ids);

            DB::commit();

            return $this->responseSuccess(
                [],
                _lang('Fee-Map has been create successfully')
            );
        } catch (Exception $exception) {
            DB::rollBack();

            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $feeMap = FeeMap::find($id);

        if ($feeMap) {
            DB::beginTransaction();

            try {
                // Detach related feeSubHeads using raw SQL
                DB::table('fee_map_fee_sub_head')->where('fee_map_id', $feeMap->id)->delete();

                // Detach related funds using raw SQL
                DB::table('fee_map_fund')->where('fee_map_id', $feeMap->id)->delete();

                // Delete the Fee-Map record using raw SQL
                DB::table('fee_maps')->where('id', $feeMap->id)->delete();

                DB::commit();

                return $this->responseSuccess(
                    [],
                    _lang('Fee-Map has been delete successfully')
                );
            } catch (Exception $exception) {
                DB::rollBack();

                return $this->responseError([], $exception->getMessage(), $exception->getCode());
            }
        } else {
            return $this->responseError([], _lang('Something went wrong. Fee-Map can not be delete.'));
        }
    }
}
