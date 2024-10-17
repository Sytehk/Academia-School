<?php

namespace Modules\Gateways\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\PaymentProcess;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Modules\Authentication\Models\User;
use Modules\Gateways\Models\PaymentRequest;
use Sajjadhossainshohag\Paystack\Facades\Paystack;

class PaystackController extends Controller
{
    use PaymentProcess;

    private PaymentRequest $payment;

    private User $user;

    public function __construct(PaymentRequest $payment, User $user)
    {
        $this->payment = $payment;
        $this->user = $user;

        $paymentId = request()->query('payment_id');
        $requested = $paymentId ? PaymentRequest::find($paymentId) : null;

        if ($requested) {
            $this->payment = $requested;
        }

        $config = $this->paymentConfig('paystack', PAYMENT_CONFIG, $requested);
        $values = $config?->payment_info;

        if ($values) {
            Config::set('paystack', [
                'publicKey' => env('PAYSTACK_PUBLIC_KEY', $values['public_key']),
                'secretKey' => env('PAYSTACK_SECRET_KEY', $values['secret_key']),
                'paymentUrl' => env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'),
                'merchantEmail' => env('MERCHANT_EMAIL', $values['merchant_email']),
            ]);
        }
    }

    public function index(Request $request): View|Application|Factory|JsonResponse|\Illuminate\Contracts\Foundation\Application
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json($this->responseFormatter(GATEWAYS_DEFAULT_400, null, $this->errorProcessor($validator)), 400);
        }

        $data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (! isset($data)) {
            return response()->json($this->responseFormatter(GATEWAYS_DEFAULT_204), 200);
        }

        $reference = Paystack::genTranxRef();

        return view('Gateways::payment.paystack', compact('data', 'reference'));
    }

    public function redirectToGateway(Request $request)
    {
        return Paystack::getAuthorizationUrl()->redirectNow();
    }

    public function handleGatewayCallback(Request $request): Application|JsonResponse|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        $paymentDetails = Paystack::getPaymentData();
        if ($paymentDetails['status'] == true) {
            $this->payment::where(['attribute_id' => $paymentDetails['data']['orderID']])->update([
                'payment_method' => 'paystack',
                'is_paid' => 1,
                'transaction_id' => $request['trxref'],
            ]);
            $data = $this->payment::where(['attribute_id' => $paymentDetails['data']['orderID']])->first();
            $data?->runSuccessHook();

            return $this->paymentResponse($data, 'success');
        }

        $payment_data = $this->payment::where(['attribute_id' => $paymentDetails['data']['orderID']])->first();
        $payment_data?->runFailureHook();

        return $this->paymentResponse($payment_data, 'fail');
    }
}
