<?php

namespace Modules\Gateways\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\PaymentProcess;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Authentication\Models\User;
use Modules\Gateways\Models\PaymentRequest;

class BkashPaymentController extends Controller
{
    use PaymentProcess;

    private mixed $config_values;

    private string $base_url;

    private $app_key;

    private $app_secret;

    private $username;

    private $password;

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

        $config = $this->paymentConfig('bkash', PAYMENT_CONFIG, $requested);
        $this->config_values = $config?->payment_info;

        if ($this->config_values) {
            $this->app_key = $this->config_values['app_key'];
            $this->app_secret = $this->config_values['app_secret'];
            $this->username = $this->config_values['username'];
            $this->password = $this->config_values['password'];
            $this->base_url = ($config->mode == 'live') ? 'https://tokenized.pay.bka.sh/v1.2.0-beta' : 'https://tokenized.sandbox.bka.sh/v1.2.0-beta';
        }
    }

    public function getToken()
    {
        $post_token = [
            'app_key' => $this->app_key,
            'app_secret' => $this->app_secret,
        ];

        $url = curl_init($this->base_url.'/tokenized/checkout/token/grant');
        $post_token_json = json_encode($post_token);
        $header = [
            'Content-Type:application/json',
            'username:'.$this->username,
            'password:'.$this->password,
        ];

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $post_token_json);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        $resultdata = curl_exec($url);
        curl_close($url);

        return json_decode($resultdata, true);
    }

    public function make_tokenize_payment(Request $request): JsonResponse|RedirectResponse
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

        $response = self::getToken();
        $auth = $response['id_token'];
        session()->put('token', $auth);
        $callbackURL = route('bkash.callback', ['payment_id' => $request['payment_id'], 'token' => $auth]);

        $requestbody = [
            'mode' => '0011',
            'amount' => round($data->payment_amount, 2),
            'currency' => 'BDT',
            'intent' => 'sale',
            'payerReference' => $data->phone,
            'merchantInvoiceNumber' => 'invoice_'.Str::random('15'),
            'callbackURL' => $callbackURL,
        ];

        $url = curl_init($this->base_url.'/tokenized/checkout/create');
        $requestbodyJson = json_encode($requestbody);

        $header = [
            'Content-Type:application/json',
            'Authorization:'.$auth,
            'X-APP-Key:'.$this->app_key,
        ];

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $requestbodyJson);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $resultdata = curl_exec($url);
        curl_close($url);

        $obj = json_decode($resultdata);

        return redirect()->away($obj->{'bkashURL'});
    }

    public function callback(Request $request): \Illuminate\Foundation\Application|JsonResponse|Redirector|RedirectResponse|Application
    {
        $paymentID = $_GET['paymentID'];
        $auth = $_GET['token'];
        $request_body = [
            'paymentID' => $paymentID,
        ];
        $url = curl_init($this->base_url.'/tokenized/checkout/execute');

        $request_body_json = json_encode($request_body);

        $header = [
            'Content-Type:application/json',
            'Authorization:'.$auth,
            'X-APP-Key:'.$this->app_key,
        ];

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $request_body_json);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $resultdata = curl_exec($url);
        curl_close($url);
        $obj = json_decode($resultdata);

        if ($obj->statusCode == '0000') {

            $this->payment::where(['id' => $request['payment_id']])->update([
                'payment_method' => 'bkash',
                'is_paid' => 1,
                'transaction_id' => $obj->trxID ?? null,
            ]);

            $data = $this->payment::where(['id' => $request['payment_id']])->first();

            $data?->runSuccessHook();

            return $this->paymentResponse($data, 'success');
        } else {
            $payment_data = $this->payment::where(['id' => $request['payment_id']])->first();
            $payment_data?->runFailureHook();

            return $this->paymentResponse($payment_data, 'fail');
        }
    }
}
