<?php

namespace Modules\Gateways\Http\Controllers\WEB;

use App\Traits\PaymentProcess;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Modules\Authentication\Models\User;
use Modules\Gateways\Models\PaymentRequest;
use Razorpay\Api\Api;

class RazorPayController extends Controller
{
    use PaymentProcess;

    private PaymentRequest $payment;

    private $user;

    public function __construct(PaymentRequest $payment, User $user)
    {
        $this->payment = $payment;
        $this->user = $user;

        $paymentId = request()->query('payment_id');
        $requested = $paymentId ? PaymentRequest::find($paymentId) : null;

        if ($requested) {
            $this->payment = $requested;
        }

        $config = $this->paymentConfig('razor_pay', PAYMENT_CONFIG, $requested);
        $razor = $config?->payment_info;

        if ($razor) {
            Config::set('razor_config', [
                'api_key' => $razor['api_key'],
                'api_secret' => $razor['api_secret'],
            ]);
        }
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

        if ($data['additional_data'] != null) {
            $business = json_decode($data['additional_data']);
            $business_name = $business->business_name ?? 'my_business';
            $business_logo = $business->business_logo ?? url('/');
        } else {
            $business_name = 'my_business';
            $business_logo = url('/');
        }

        return view('payment.razor-pay', compact('data', 'business_logo', 'business_name'));
    }

    public function payment(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        $input = $request->all();

        if (empty($input['razorpay_payment_id'])) {
            $payment_data = $this->payment::where(['id' => $request['payment_id']])->first();
            $payment_data?->runFailureHook();

            return $this->paymentResponse($payment_data, 'fail');
        }

        $api = new Api(config('razor_config.api_key'), config('razor_config.api_secret'));
        $payment = $api->payment->fetch($input['razorpay_payment_id']);
        $response = $api->payment->fetch($input['razorpay_payment_id'])->capture(['amount' => $payment['amount'] - $payment['fee']]);

        $this->payment::where(['id' => $request['payment_id']])->update([
            'payment_method' => 'razor_pay',
            'is_paid' => 1,
            'transaction_id' => $input['razorpay_payment_id'],
        ]);
        $data = $this->payment::where(['id' => $request['payment_id']])->first();
        $data?->runSuccessHook();

        return $this->paymentResponse($data, 'success');
    }

    public function callback(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        $input = $request->all();
        $data_id = base64_decode($request?->payment_data);
        $payment_data = $this->payment::where(['id' => $data_id])->first();

        if ($payment_data && count($input) && ! empty($input['razorpay_payment_id'])) {
            // NOTE: this integration fetches/captures by payment_id rather than verifying
            // razorpay_order_id + razorpay_signature via $api->utility->verifyPaymentSignature().
            // Treat this callback as provisional confirmation only until signature
            // verification is added; it should not be the sole source of truth for
            // high-value transactions.
            $payment_data->payment_method = 'razor_pay';
            $payment_data->is_paid = 1;
            $payment_data->transaction_id = $input['razorpay_payment_id'];
            $payment_data->save();
            $payment_data->runSuccessHook();

            return $this->paymentResponse($payment_data, 'success');
        }

        $payment_data?->runFailureHook();

        return $this->paymentResponse($payment_data, 'fail');
    }

    public function cancel(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        $payment_data = $this->payment::where(['id' => $request['payment_id']])->first();

        return $this->paymentResponse($payment_data, 'fail');
    }
}
