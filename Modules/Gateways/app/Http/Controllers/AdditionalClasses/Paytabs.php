<?php

namespace Modules\Gateways\Http\Controllers\AdditionalClasses;

use App\Traits\PaymentProcess;
use Modules\Gateways\Models\PaymentRequest;

class Paytabs
{
    use PaymentProcess;

    /** @var array<string,mixed> Credentials from the gateway's payment_info (profile_id, server_key, base_url). */
    private array $config_values = [];

    public function __construct(?PaymentRequest $payment = null)
    {
        // Previously referenced an undefined $payment, so paymentConfig() always
        // bailed and $config_values was left uninitialised (fatal on first use).
        // The gateway credentials live in payment_info like every other gateway,
        // not in non-existent live_values/test_values columns.
        $config = $payment ? $this->paymentConfig('paytabs', PAYMENT_CONFIG, $payment) : null;
        $this->config_values = (array) ($config?->payment_info ?? []);
    }

    public function send_api_request($request_url, $data, $request_method = null)
    {
        $data['profile_id'] = $this->config_values['profile_id'] ?? null;
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => ($this->config_values['base_url'] ?? '').'/'.$request_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_CUSTOMREQUEST => isset($request_method) ? $request_method : 'POST',
            CURLOPT_POSTFIELDS => json_encode($data, true),
            CURLOPT_HTTPHEADER => [
                'authorization:'.($this->config_values['server_key'] ?? ''),
                'Content-Type:application/json',
            ],
        ]);

        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);

        return $response;
    }

    public function is_valid_redirect($post_values): bool
    {
        $serverKey = $this->config_values['server_key'] ?? '';
        $requestSignature = $post_values['signature'];
        unset($post_values['signature']);
        $fields = array_filter($post_values);
        ksort($fields);
        $query = http_build_query($fields);
        $signature = hash_hmac('sha256', $query, $serverKey);
        if (hash_equals($signature, $requestSignature) === true) {
            return true;
        } else {
            return false;
        }
    }
}
