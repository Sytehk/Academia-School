<?php

namespace Modules\SystemConfiguration\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SAASPaymentGatewayController extends Controller
{
    public function index()
    {
        $gateways = DB::table('s_a_a_s_settings')
            ->where('type', 'payment_config')
            ->orderBy('id', 'asc')
            ->get();

        return view('systemconfiguration::saas_payment_gateways.index', compact('gateways'));
    }

    public function edit($id)
    {
        $gateway = DB::table('s_a_a_s_settings')
            ->where('id', $id)
            ->where('type', 'payment_config')
            ->first();

        if (! $gateway) {
            abort(404);
        }

        $payment_info = json_decode($gateway->payment_info, true);

        return view('systemconfiguration::saas_payment_gateways.edit', compact(
            'gateway',
            'payment_info'
        ));
    }

    public function update(Request $request, $id)
    {
        $gateway = DB::table('s_a_a_s_settings')
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

        DB::table('s_a_a_s_settings')
            ->where('id', $id)
            ->update([
                'mode' => $request->mode,
                'status' => $request->status ?? 0,
                'payment_info' => json_encode($paymentData),
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('saas-payment-gateways.index')
            ->with('success', 'Payment Gateway Updated Successfully');
    }
}
