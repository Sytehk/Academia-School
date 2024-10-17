<?php

namespace Modules\Gateways\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\PaymentProcess;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Str;
use Modules\Gateways\Http\Controllers\AdditionalClasses\LiqPay;
use Modules\Gateways\Models\PaymentRequest;

class LiqPayController extends Controller
{
    use PaymentProcess;

    private PaymentRequest $payment;

    public function __construct(PaymentRequest $payment)
    {
        $this->payment = $payment;
    }

    public function payment(Request $request): JsonResponse|string|RedirectResponse
    {
        $paymentId = request()->query('payment_id');
        $payment = PaymentRequest::where(['id' => $paymentId])->first();

        try {
            $config = $this->paymentConfig('liqpay', PAYMENT_CONFIG, $payment);
            $values = $config->payment_info;

            $tran = Str::random(6).'-'.rand(1, 1000);
            $data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
            if (! isset($data)) {
                return response()->json($this->responseFormatter(GATEWAYS_DEFAULT_204), 200);
            }

            $public_key = $values['public_key'];
            $private_key = $values['private_key'];
            $liqpay = new LiqPay($public_key, $private_key);
            $html = $liqpay->cnb_form([
                'action' => 'pay',
                'amount' => round($data->payment_amount, 2),
                'currency' => $data->currency_code, // USD
                'description' => 'Transaction ID: '.$tran,
                'order_id' => $data->attribute_id,
                'result_url' => route('liqpay.callback', ['payment_id' => $data->id]),
                'server_url' => route('liqpay.callback', ['payment_id' => $data->id]),
                'version' => '3',
            ]);

            return $html;
        } catch (\Exception $ex) {
            return back();
        }
    }

    public function callback(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        if ($request['status'] == 'success') {
            $this->payment::where(['id' => $request['payment_id']])->update([
                'payment_method' => 'liqpay',
                'is_paid' => 1,
                'transaction_id' => $request['transaction_id'],
            ]);
            $data = $this->payment::where(['id' => $request['payment_id']])->first();
            $data?->runSuccessHook();

            return $this->paymentResponse($data, 'success');
        }
        $payment_data = $this->payment::where(['id' => $request['payment_id']])->first();
        $payment_data?->runFailureHook();

        return $this->paymentResponse($payment_data, 'fail');
    }
}
