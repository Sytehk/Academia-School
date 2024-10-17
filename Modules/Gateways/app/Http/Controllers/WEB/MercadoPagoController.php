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
use Illuminate\Support\Facades\Validator;
use MercadoPago\Payer;
use MercadoPago\Payment;
use MercadoPago\SDK;
use Modules\Authentication\Models\User;
use Modules\Gateways\Models\PaymentRequest;

class MercadoPagoController extends Controller
{
    use PaymentProcess;

    private PaymentRequest $paymentRequest;

    private mixed $config;

    private User $user;

    public function __construct(PaymentRequest $paymentRequest, User $user)
    {
        $this->paymentRequest = $paymentRequest;
        $this->user = $user;

        $paymentId = request()->query('payment_id');
        $requested = $paymentId ? PaymentRequest::find($paymentId) : null;

        if ($requested) {
            $this->paymentRequest = $requested;
        }

        $config = $this->paymentConfig('mercadopago', PAYMENT_CONFIG, $requested);
        $this->config = $config?->payment_info;
    }

    public function index(Request $request): View|Application|Factory|JsonResponse|\Illuminate\Contracts\Foundation\Application
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json($this->responseFormatter(GATEWAYS_DEFAULT_400, null, $this->errorProcessor($validator)), 400);
        }

        $data = $this->paymentRequest::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (! isset($data)) {
            return response()->json($this->responseFormatter(GATEWAYS_DEFAULT_204), 200);
        }
        $config = $this->config;

        return view('Gateways::payment.payment-view-marcedo-pogo', compact('config', 'data'));
    }

    public function make_payment(Request $request): Application|JsonResponse|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        // payment_info is cast to array on Setting/SAASSetting (see every other
        // gateway, e.g. Stripe) — object access here fatally errored.
        SDK::setAccessToken($this->config['access_token'] ?? null);
        $payment = new Payment;
        $payment->transaction_amount = (float) $request['transactionAmount'];
        $payment->token = $request['token'];
        $payment->description = $request['description'];
        $payment->installments = (int) $request['installments'];
        $payment->payment_method_id = $request['paymentMethodId'];
        $payment->issuer_id = (int) $request['issuer'];

        $payer = new Payer;
        $payer->email = $request['payer']['email'];
        $payer->identification = [
            'type' => $request['payer']['identification']['type'],
            'number' => $request['payer']['identification']['number'],
        ];
        $payment->payer = $payer;
        $payment->save();

        if ($payment->status == 'approved') {
            $this->paymentRequest::where(['id' => $request['payment_id']])->update([
                'payment_method' => 'mercadopago',
                'is_paid' => 1,
                'transaction_id' => $payment->id,
            ]);
            $data = $this->paymentRequest::where(['id' => $request['payment_id']])->first();
            $data?->runSuccessHook();

            return $this->paymentResponse($data, 'success');
        }
        $payment_data = $this->paymentRequest::where(['id' => $request['payment_id']])->first();
        $payment_data?->runFailureHook();

        return $this->paymentResponse($payment_data, 'fail');
    }
}
