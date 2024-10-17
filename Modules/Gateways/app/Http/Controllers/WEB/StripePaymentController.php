<?php

namespace Modules\Gateways\Http\Controllers\WEB;

use App\Traits\PaymentProcess;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Gateways\Models\PaymentRequest;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripePaymentController extends Controller
{
    use PaymentProcess;

    private $config_values;

    private PaymentRequest $payment;

    public function __construct(PaymentRequest $payment)
    {
        $this->payment = $payment;

        $paymentId = request()->query('payment_id');
        $requested = $paymentId ? PaymentRequest::find($paymentId) : null;

        if ($requested) {
            $this->payment = $requested;
        }

        $config = $this->paymentConfig('stripe', PAYMENT_CONFIG, $requested);
        $this->config_values = $config?->payment_info;
    }

    public function index(Request $request): View|Factory|JsonResponse|Application
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }

        $data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (! isset($data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }
        $config = $this->config_values;

        return view('payment.stripe', compact('data', 'config'));
    }

    public function payment_process_3d(Request $request): JsonResponse
    {
        $data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (! isset($data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }
        $payment_amount = $data['payment_amount'];

        Stripe::setApiKey($this->config_values['api_key']);
        header('Content-Type: application/json');
        $currency_code = $data->currency_code;

        $checkout_session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $currency_code ?? 'usd',
                    'unit_amount' => round($payment_amount, 2) * 100,
                    'product_data' => [
                        'name' => 'Fees Collection',
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => url('/').'/payment/stripe/success?session_id={CHECKOUT_SESSION_ID}&payment_id='.$data->id,
            'cancel_url' => url()->previous(),
        ]);

        return response()->json(['id' => $checkout_session->id]);
    }

    public function success(Request $request)
    {
        Stripe::setApiKey($this->config_values['api_key']);
        $session = Session::retrieve($request->get('session_id'));

        if ($session->payment_status == 'paid' && $session->status == 'complete') {
            $this->payment::where(['id' => $request['payment_id']])->update([
                'payment_method' => 'stripe',
                'is_paid' => 1,
                'transaction_id' => $session->payment_intent,
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
