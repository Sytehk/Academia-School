<?php

namespace Modules\Gateways\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\PaymentProcess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Authentication\Models\User;
use Modules\Gateways\Models\PaymentRequest;

class PaymobController extends Controller
{
    use PaymentProcess;

    private mixed $config_values;

    private PaymentRequest $payment;

    private User $user;

    private string $base_url;

    private array $supportedCountries = [
        'egypt' => 'https://accept.paymob.com',
        'PAK' => 'https://pakistan.paymob.com',
        'KSA' => 'https://ksa.paymob.com',
        'oman' => 'https://oman.paymob.com',
        'UAE' => 'https://uae.paymob.com',
    ];

    private string $defaultBaseUrl = 'https://accept.paymob.com';

    public function __construct(PaymentRequest $payment, User $user)
    {
        $this->payment = $payment;
        $this->user = $user;

        $paymentId = request()->query('payment_id');
        $requested = $paymentId ? PaymentRequest::find($paymentId) : null;

        if ($requested) {
            $this->payment = $requested;
        }

        $config = $this->paymentConfig('paymob_accept', PAYMENT_CONFIG, $requested);
        $this->config_values = $config?->payment_info;

        $country = is_array($this->config_values) ? ($this->config_values['supported_country'] ?? null) : null;
        $this->base_url = $this->supportedCountries[$country] ?? $this->defaultBaseUrl;
    }

    protected function cURL($url, $json)
    {
        $ch = curl_init($url);

        $headers = [];
        $headers[] = 'Content-Type: application/json';

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $output = curl_exec($ch);

        curl_close($ch);

        return json_decode($output);
    }

    public function credit(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json($this->responseFormatter(GATEWAYS_DEFAULT_400, null, $this->errorProcessor($validator)), 400);
        }

        $paymentData = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (! isset($paymentData)) {
            return response()->json($this->responseFormatter(GATEWAYS_DEFAULT_204), 200);
        }

        session()->put('payment_id', $paymentData->id);

        if ($paymentData['additional_data'] != null) {
            $business = json_decode($paymentData['additional_data']);
            $business_name = $business->business_name ?? 'my_business';
        } else {
            $business_name = 'my_business';
        }

        $payer = json_decode($paymentData['payer_information']);
        $url = $this->base_url.'/v1/intention/';
        $config = $this->config_values;
        $token = $config['secret_key']; // secret key

        // Data for the request
        $integration_id = (int) $config['integration_id'];
        $data = [
            'amount' => round($paymentData->payment_amount * 100),
            'currency' => $paymentData->currency_code,
            'payment_methods' => [$integration_id], // integration id will be integer
            'items' => [
                [
                    'name' => 'payable amount',
                    'amount' => round($paymentData->payment_amount * 100),
                    'description' => 'payable amount',
                    'quantity' => 1,
                ],
            ],
            'billing_data' => [
                'apartment' => 'N/A',
                'email' => ! empty($payer->email) ? $payer->email : 'test@gmail.com',
                'floor' => 'N/A',
                'first_name' => ! empty($payer->name) ? $payer->name : 'rashed',
                'street' => 'N/A',
                'building' => 'N/A',
                'phone_number' => ! empty($payer->phone) ? $payer->phone : '0182780000000',
                'shipping_method' => 'PKG',
                'postal_code' => 'N/A',
                'city' => 'N/A',
                'country' => 'N/A',
                'last_name' => ! empty($payer->name) ? $payer->name : 'rashed',
                'state' => 'N/A',
            ],
            'special_reference' => time(),
            'customer' => [
                'first_name' => ! empty($payer->name) ? $payer->name : 'rashed',
                'last_name' => ! empty($payer->name) ? $payer->name : 'rashed',
                'email' => ! empty($payer->email) ? $payer->email : 'test@gmail.com',
                'extras' => [
                    're' => '22',
                ],
            ],
            'extras' => [
                'ee' => 22,
            ],
            'redirection_url' => route('paymob.callback'),
        ];

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Token '.$token,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $result = json_decode($response, true);
        if (! isset($result['client_secret'])) {
            return response()->json($this->responseFormatter(GATEWAYS_DEFAULT_204), 200);
        }
        $secret_key = $result['client_secret'];
        curl_close($ch);
        $publicKey = $config['public_key'];
        $urlRedirect = $this->base_url."/unifiedcheckout/?publicKey=$publicKey&clientSecret=$secret_key";

        return redirect()->to($urlRedirect);
    }

    public function createOrder($token, $paymentData, $business_name)
    {
        $items[] = [
            'name' => $business_name,
            'amount_cents' => round($paymentData->payment_amount * 100),
            'description' => 'payment ID :'.$paymentData->id,
            'quantity' => 1,
        ];

        $data = [
            'auth_token' => $token,
            'delivery_needed' => 'false',
            'amount_cents' => round($paymentData->payment_amount * 100),
            'currency' => $paymentData->currency_code,
            'items' => $items,

        ];

        return $this->cURL(
            $this->base_url.'/api/ecommerce/orders',
            $data
        );
    }

    public function getPaymentToken($order, $token, $paymentData, $payer)
    {
        $value = $paymentData->payment_amount;
        $billingData = [
            'apartment' => 'N/A',
            'email' => ! empty($payer->email) ? $payer->email : 'test@gmail.com',
            'floor' => 'N/A',
            'first_name' => ! empty($payer->name) ? $payer->name : 'rashed',
            'street' => 'N/A',
            'building' => 'N/A',
            'phone_number' => ! empty($payer->phone) ? $payer->phone : '0182780000000',
            'shipping_method' => 'PKG',
            'postal_code' => 'N/A',
            'city' => 'N/A',
            'country' => 'N/A',
            'last_name' => ! empty($payer->name) ? $payer->name : 'rashed',
            'state' => 'N/A',
        ];

        $data = [
            'auth_token' => $token,
            'amount_cents' => round($value * 100),
            'expiration' => 3600,
            'order_id' => $order->id,
            'billing_data' => $billingData,
            'currency' => $paymentData->currency_code,
            'integration_id' => is_array($this->config_values) ? $this->config_values['integration_id'] : $this->config_values->integration_id,
        ];

        $response = $this->cURL(
            $this->base_url.'/api/acceptance/payment_keys',
            $data
        );

        return $response->token;
    }

    public function callback(Request $request)
    {
        $data = $request->all();
        ksort($data);
        $hmac = $data['hmac'];
        $array = [
            'amount_cents',
            'created_at',
            'currency',
            'error_occured',
            'has_parent_transaction',
            'id',
            'integration_id',
            'is_3d_secure',
            'is_auth',
            'is_capture',
            'is_refunded',
            'is_standalone_payment',
            'is_voided',
            'order',
            'owner',
            'pending',
            'source_data_pan',
            'source_data_sub_type',
            'source_data_type',
            'success',
        ];
        $connectedString = '';
        foreach ($data as $key => $element) {
            if (in_array($key, $array)) {
                $connectedString .= $element;
            }
        }
        $secret = is_array($this->config_values) ? $this->config_values['hmac'] : $this->config_values->hmac;
        $hased = hash_hmac('sha512', $connectedString, $secret);

        if ($hased == $hmac && $data['success'] === 'true') {

            $this->payment::where(['id' => session('payment_id')])->update([
                'payment_method' => 'paymob',
                'is_paid' => 1,
                'transaction_id' => session('payment_id'),
            ]);

            $paymentData = $this->payment::where(['id' => session('payment_id')])->first();
            $paymentData?->runSuccessHook();

            return $this->paymentResponse($paymentData, 'success');
        }
        $paymentData = $this->payment::where(['id' => session('payment_id')])->first();
        $paymentData?->runFailureHook();

        return $this->paymentResponse($paymentData, 'fail');
    }
}
