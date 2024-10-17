<?php

namespace Modules\Gateways\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\PaymentProcess;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Validator;
use Modules\Authentication\Models\User;
use Modules\Gateways\Models\PaymentRequest;

class SenangPayController extends Controller
{
    use PaymentProcess;

    private mixed $config_values;

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

        $config = $this->paymentConfig('senang_pay', PAYMENT_CONFIG, $requested);
        $this->config_values = $config?->payment_info;
    }

    public function index(Request $request): View|Factory|JsonResponse|Application
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json($this->responseFormatter(GATEWAYS_DEFAULT_400, null, $this->errorProcessor($validator)), 400);
        }

        $payment_data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (! isset($payment_data)) {
            return response()->json($this->responseFormatter(GATEWAYS_DEFAULT_204), 200);
        }
        $payer = json_decode($payment_data['payer_information']);
        $config = $this->config_values;
        session()->put('payment_id', $payment_data->id);

        return view('Gateways::payment.senang-pay', compact('payment_data', 'payer', 'config'));
    }

    public function return_senang_pay(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        if ($request['status_id'] == 1) {
            $this->payment::where(['id' => session()->get('payment_id')])->update([
                'payment_method' => 'senang_pay',
                'is_paid' => 1,
                'transaction_id' => $request['transaction_id'],
            ]);
            $data = $this->payment::where(['id' => session()->get('payment_id')])->first();
            $data?->runSuccessHook();

            return $this->paymentResponse($data, 'success');
        }
        $payment_data = $this->payment::where(['id' => session()->get('payment_id')])->first();
        $payment_data?->runFailureHook();

        return $this->paymentResponse($payment_data, 'fail');
    }
}
