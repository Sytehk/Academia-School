<?php

namespace App\Traits;

use Exception;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Modules\Authentication\Models\SAASSetting;
use Modules\SystemConfiguration\Models\Setting;

trait PaymentProcess
{
    use Authenticatable;

    public function responseFormatter($constant, $content = null, $errors = []): array
    {
        $constant = (array) $constant;
        $constant['content'] = $content;
        $constant['errors'] = $errors;

        return $constant;
    }

    public function errorProcessor($validator): array
    {
        $errors = [];
        foreach ($validator->errors()->getMessages() as $index => $error) {
            $errors[] = ['error_code' => $index, 'message' => $error[0]];
        }

        return $errors;
    }

    public function paymentResponse($payment_info, $payment_flag): Application|JsonResponse|Redirector|RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        $getNewUser = (int) 0;
        $additionalData = json_decode($payment_info->additional_data, true);

        if (isset($additionalData['new_customer_id']) && isset($additionalData['is_guest_in_order'])) {
            $getNewUser = (int) (($additionalData['new_customer_id'] != 0 && $additionalData['is_guest_in_order'] != 1) ? 1 : 0);
        }

        $token_string = 'payment_method='.$payment_info->payment_method.'&&transaction_reference='.$payment_info->transaction_id;
        if (in_array($payment_info->payment_platform, ['web', 'app']) && $payment_info['external_redirect_link'] != null) {
            return redirect($payment_info['external_redirect_link'].'?flag='.$payment_flag.'&&token='.base64_encode($token_string).'&&new_user='.$getNewUser);
        }

        return redirect()->route('payment-'.$payment_flag, ['token' => base64_encode($token_string), 'new_user' => $getNewUser]);
    }

    public function paymentConfig($key, $settingsType, $payment): ?object
    {
        if (empty($key) || empty($settingsType) || empty($payment)) {
            return null;
        }

        if ($payment->payment_type == 'subscription_payment') {
            try {
                $config = SAASSetting::where('name', $key)
                    ->where('type', $settingsType)->first();
            } catch (Exception $exception) {
                return new SAASSetting;
            }
        } else {
            try {
                $config = Setting::where('name', $key)
                    ->where('institute_id', $payment->institute_id)
                    ->where('branch_id', $payment->branch_id)
                    ->where('type', $settingsType)->first();
            } catch (Exception $exception) {
                return new Setting;
            }
        }

        return (isset($config)) ? $config : null;
    }
}
