<?php

namespace Modules\Finance\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\FeeDateConfig;
use Modules\Finance\Models\FeeMap;
use RealRashid\SweetAlert\Facades\Alert;

class DateConfigController extends Controller
{
    public function index(): View|Factory
    {
        return view('finance::date_config.index', [
            'fee_date_configs' => FeeDateConfig::join('fee_sub_heads', 'fee_date_configs.fee_sub_head_id', '=', 'fee_sub_heads.id')
                ->select('fee_date_configs.id', 'fee_date_configs.payable_date_start', 'fee_date_configs.payable_date_end', 'fee_sub_heads.name as fee_sub_head_name')
                ->orderBy('fee_date_configs.id', 'desc')
                ->get(),
        ]);
    }

    public function store(Request $request): Factory|Redirector|RedirectResponse|View
    {
        $feeMap = FeeMap::where('fee_head_id', $request->fee_head_id)->where('type', 'fee')->first();
        $fee_date_configs = FeeDateConfig::join('fee_sub_heads', 'fee_date_configs.fee_sub_head_id', '=', 'fee_sub_heads.id')
            ->select('fee_date_configs.id', 'fee_date_configs.payable_date_start', 'fee_date_configs.payable_date_end', 'fee_sub_heads.name as fee_sub_head_name')
            ->orderBy('fee_date_configs.id', 'desc')
            ->get();

        if ($feeMap) {
            $feeMapFeeSubHeads = DB::table('fee_map_fee_sub_head')
                ->join('fee_sub_heads', 'fee_map_fee_sub_head.fee_sub_head_id', 'fee_sub_heads.id')
                ->select('fee_map_fee_sub_head.*', 'fee_sub_heads.*')
                ->orderBy('fee_sub_heads.serial', 'asc')
                ->where('fee_map_id', $feeMap->id)
                ->get();

            return view('finance::date_config.index', ['feeMap' => $feeMap, 'feeMapFeeSubHeads' => $feeMapFeeSubHeads, 'fee_date_configs' => $fee_date_configs]);
        } else {
            Alert::error('Error', 'FeeMap not found for the provided ID');

            return redirect('date-config');
        }
    }

    public function edit(int $id): Factory|View
    {
        $feeDateConfig = FeeDateConfig::where('id', $id)->first();

        return view('finance::date_config.edit', compact('feeDateConfig'));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        DB::beginTransaction();
        try {
            $feeDateConfig = FeeDateConfig::where('id', $id)->first();
            $feeDateConfig->payable_date_start = $request->payable_date;
            $feeDateConfig->payable_date_end = $request->fine_active_date;
            $feeDateConfig->save();

            DB::commit();

            Alert::toast(_lang('Successfully update Fee Date config with related records.'), 'success');

            return redirect('date-config');
        } catch (\Exception $e) {
            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return redirect('date-config');
        }
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $feeDateConfig = FeeDateConfig::where('id', $id)->first();

        if (! empty($feeDateConfig)) {
            $feeDateConfig->delete();

            Alert::toast(_lang('Successfully delete Fee Date config with related records.'), 'success');

            return redirect('date-config');
        } else {
            Alert::error('Error', _lang('Failed to delete Fee Date config with related records. Please try again.'));

            return redirect('date-config');
        }
    }
}
