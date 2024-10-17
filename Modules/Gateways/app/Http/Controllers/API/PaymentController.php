<?php

namespace Modules\Gateways\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Authentication\Models\SAASSetting;
use Modules\Gateways\Models\PaymentRequest;
use Modules\SystemConfiguration\Models\Setting;

class PaymentController extends Controller
{
    use Authenticatable;

    public function index(Request $request): JsonResponse
    {
        $paymentMethods = Setting::where('type', 'payment_config')
            ->where('institute_id', $this->getCurrentInstituteId())
            ->get();

        return $this->responseSuccess($paymentMethods, 'Payment methods list fetched successfully.');
    }

    public function saasIndex(Request $request): JsonResponse
    {
        $paymentMethods = SAASSetting::where('type', PAYMENT_CONFIG)->get();

        return $this->responseSuccess($paymentMethods, 'Payment methods list fetched successfully.');
    }

    public function update(Request $request, $settingId): JsonResponse
    {
        $setting = Setting::findOrFail($settingId);
        $setting->update([
            'payment_info' => $request->payment_info,
            'mode' => $request->mode ?? 'test',
            'status' => $request->status ?? '1',
            'updated_at' => now(),
        ]);

        return $this->responseSuccess($setting, 'Payment method credentials update successfully.');
    }

    public function statusUpdate($paymentId)
    {
        $setting = Setting::findOrFail($paymentId);
        $setting->update([
            'status' => $setting->status === '1' ? '0' : '1',
            'updated_at' => now(),
        ]);

        return $this->responseSuccess($setting, 'Payment method status updated successfully.');
    }

    public function saasUpdate(Request $request, $settingId): JsonResponse
    {
        $setting = SAASSetting::findOrFail($settingId);
        $setting->update([
            'payment_info' => $request->payment_info,
            'mode' => $request->mode ?? 'test',
            'status' => $request->status ?? '1',
            'updated_at' => now(),
        ]);

        return $this->responseSuccess($setting, 'Payment method credentials update successfully.');
    }

    public function saasStatusUpdate($paymentId)
    {
        $setting = SAASSetting::findOrFail($paymentId);
        $setting->update([
            'status' => $setting->status === '1' ? '0' : '1',
            'updated_at' => now(),
        ]);

        return $this->responseSuccess($setting, 'SAAS Payment method status updated successfully.');
    }

    public function payment(Request $request): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string',
            'payment_type' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string',
            'name' => 'nullable|string',
            'phone' => 'required|string',
            'email' => 'nullable|string',
            'plan_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $paymentMethod = strtolower($request->payment_method);
        $amount = $request->amount;
        $currencyCode = $request->currency ?? 'USD'; // Set your default currency or fetch dynamically

        $payment = new PaymentRequest;
        $payment->institute_id = get_institute_id();
        $payment->branch_id = get_branch_id();
        $payment->payment_amount = $amount;
        $payment->currency_code = $currencyCode;
        $payment->payment_method = $paymentMethod;
        $payment->payment_type = $request->payment_type;
        $payment->name = $request->name ?? null;
        $payment->phone = $request->phone ?? null;
        $payment->email = $request->email ?? null;
        $payment->plan_id = $request->plan_id ?? null;
        $payment->notes = $request->notes ?? null;
        $payment->gateway_callback_url = $request->gateway_callback_url ?? null;
        $payment->external_redirect_link = $request->external_redirect_link ?? null;
        // success_hook/failure_hook are intentionally NOT set from client input — they may only
        // ever be populated server-side with a value present in PaymentRequest::allowedHooks().
        $payment->payment_platform = $request->payment_platform ?? null;

        $payment->save();

        $paymentUrl = $this->generatePaymentUrl($payment);
        if (! $paymentUrl) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid payment method.',
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'payment_url' => $paymentUrl,
        ]);
    }

    private function generatePaymentUrl(PaymentRequest $payment)
    {
        $routes = [
            'ssl_commerz' => 'payment/sslcommerz/pay',
            'stripe' => 'payment/stripe/pay',
            'paymob_accept' => 'payment/paymob/pay',
            'flutterwave' => 'payment/flutterwave-v3/pay',
            'paytm' => 'payment/paytm/pay',
            'paypal' => 'payment/paypal/pay',
            'paytabs' => 'payment/paytabs/pay',
            'liqpay' => 'payment/liqpay/pay',
            'razor_pay' => 'payment/razor-pay/pay',
            'senang_pay' => 'payment/senang-pay/pay',
            'mercadopago' => 'payment/mercadopago/pay',
            'bkash' => 'payment/bkash/make-payment',
            'paystack' => 'payment/paystack/pay',
        ];

        if (array_key_exists($payment->payment_method, $routes)) {
            return url("{$routes[$payment->payment_method]}?payment_id={$payment->id}");
        }

        return false;
    }

    public function success(Request $request): JsonResponse
    {
        return response()->json(['message' => 'Payment succeeded'], 200);
    }

    public function fail(): JsonResponse
    {
        return response()->json(['message' => 'Payment failed'], 403);
    }

    public function saasPayment(Request $request): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string',
            'payment_type' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string',
            'name' => 'nullable|string',
            'phone' => 'required|string',
            'email' => 'nullable|string',
            'plan_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $paymentMethod = strtolower($request->payment_method);
        $amount = $request->amount;
        $currencyCode = $request->currency ?? 'USD'; // Set your default currency or fetch dynamically

        $payment = new PaymentRequest;
        $payment->institute_id = get_institute_id();
        $payment->branch_id = get_branch_id();
        $payment->payment_amount = $amount;
        $payment->currency_code = $currencyCode;
        $payment->payment_method = $paymentMethod;
        $payment->payment_type = $request->payment_type;
        $payment->name = $request->name ?? null;
        $payment->phone = $request->phone ?? null;
        $payment->email = $request->email ?? null;
        $payment->plan_id = $request->plan_id ?? null;
        $payment->notes = $request->notes ?? null;
        $payment->gateway_callback_url = $request->gateway_callback_url ?? null;
        $payment->external_redirect_link = $request->external_redirect_link ?? null;
        $payment->success_hook = $request->success_hook ?? null;
        $payment->failure_hook = $request->failure_hook ?? null;
        $payment->payment_platform = $request->payment_platform ?? null;

        $payment->save();

        $paymentUrl = $this->generatePaymentUrl($payment);
        if (! $paymentUrl) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid payment method.',
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'payment_url' => $paymentUrl,
        ]);
    }
}
