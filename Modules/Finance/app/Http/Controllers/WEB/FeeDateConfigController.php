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
use RealRashid\SweetAlert\Facades\Alert;

class FeeDateConfigController extends Controller
{
    public function index(): View|Factory
    {
        return view('finance::date_config.index');
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        DB::beginTransaction();
        try {
            $dataCount = count($request->payable_date);
            for ($i = 0; $i < $dataCount; $i++) {
                $feeDateConfig = new FeeDateConfig;

                $feeDateConfig->fee_sub_head_id = $request->fee_sub_head_id[$i];
                $feeDateConfig->payable_date_start = $request->payable_date[$i];
                $feeDateConfig->payable_date_end = $request->fine_active_date[$i];

                $feeDateConfig->save();
            }

            DB::commit();

            Alert::toast(_lang('Successfully created Fee Date config with related records.'), 'success');

            return redirect('date-config');
        } catch (\Exception $e) {
            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return redirect('date-config');
        }
    }
}
