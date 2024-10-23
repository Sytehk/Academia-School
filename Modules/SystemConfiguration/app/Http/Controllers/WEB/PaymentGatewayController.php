<?php

namespace Modules\SystemConfiguration\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\DemoModeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentGatewayController extends Controller
{
    public function index()
    {
        $gateways = DB::table('settings')
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('type', 'payment_config')
            ->orderBy('id', 'asc')
            ->get();

        return view('systemconfiguration::payment_gateways.index', compact('gateways'));
    }

    public function edit($id)
    {

        $gateway = DB::table('settings')
            ->where('id', $id)
            ->where('type', 'payment_config')
            ->first();

        if (! $gateway) {
            abort(404);
        }

        $payment_info = json_decode($gateway->payment_info, true);

        return view('systemconfiguration::payment_gateways.edit', compact(
            'gateway',
            'payment_info'
        ));
    }

    public function update(Request $request, $id)
    {
        app(DemoModeService::class)->check('payment_settings');
        $gateway = DB::table('settings')
            ->where('id', $id)
            ->where('type', 'payment_config')
            ->first();

        if (! $gateway) {
            abort(404);
        }

        $paymentData = [];
        foreach ($request->payment_info ?? [] as $key => $value) {

            $paymentData[$key] = $value;
        }

        DB::table('settings')
            ->where('id', $id)
            ->update([
                'mode' => $request->mode,
                'status' => $request->status ?? 0,
                'payment_info' => json_encode($paymentData),
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('payment-gateways.index')
            ->with('success', 'Payment Gateway Updated Successfully');
    }
}
