<?php

namespace App\Services;

use App\Jobs\SendSmsJob;
use GuzzleHttp\Client;
use Modules\Academic\Models\SmsLog;
use Twilio\Rest\Client as TwilioClient;

class SmsService
{
    /**
     * Queue an SMS for the sms:process-logs cron to actually send, rather than
     * sending synchronously in the request. Centralizes the SmsLog::create()
     * pattern that was previously duplicated inline across attendance controllers.
     */
    public function queueLog(
        string $receiver,
        string $message,
        string $userType,
        int $senderId,
        array $extra = []
    ): ?SmsLog {
        if (empty($receiver)) {
            return null;
        }

        $smsLog = SmsLog::create(array_merge([
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'receiver' => $receiver,
            'message' => $message,
            'user_type' => $userType,
            'sender_id' => $senderId,
            'status' => 0,
        ], $extra));

        dispatch(new SendSmsJob($smsLog));

        return $smsLog;
    }

    protected $gateway;

    protected $apiKey;

    protected $senderId;

    protected $twilioSid;

    protected $twilioToken;

    protected $twilioNumber;

    public function __construct()
    {
        $this->gateway = strtolower((string) (get_option('sms_gateway') ?: get_option('default_sms_gateway')));
        $this->apiKey = get_option('bulk_sms_api_key') ?: get_option('api_key');
        $this->senderId = get_option('bulk_sms_sender_id') ?: get_option('sender_id');
        $this->twilioSid = get_option('twilio_sid');
        $this->twilioToken = get_option('twilio_token');
        $this->twilioNumber = get_option('twilio_from_number');
    }

    public function sendSMS($receiverNumber, $message)
    {
        $gateway = $this->normalizeGateway($this->gateway);

        return match ($gateway) {
            'twilio' => $this->sendViaTwilio($receiverNumber, $message),
            'bulksmsbd' => $this->sendViaBulkSmsBD($receiverNumber, $message),
            'mimsms' => $this->sendViaMimSms($receiverNumber, $message),
            'infobip' => $this->sendViaInfobip($receiverNumber, $message),
            'clickatell' => $this->sendViaClickatell($receiverNumber, $message),
            'messagebird' => $this->sendViaMessageBird($receiverNumber, $message),
            default => ['success' => false, 'error' => 'Invalid SMS Gateway'],
        };
    }

    private function normalizeGateway(?string $gateway): string
    {
        $gateway = strtolower(trim((string) $gateway));

        return match ($gateway) {
            'bulksmsbd', 'bulksms', 'bulk_sms_bd' => 'bulksmsbd',
            'mimsms', 'mim_sms', 'mim-sms' => 'mimsms',
            'twilio' => 'twilio',
            'infobip' => 'infobip',
            'clickatell' => 'clickatell',
            'messagebird', 'message_bird', 'message-bird' => 'messagebird',
            default => $gateway,
        };
    }

    // Every sendVia*() method below catches \Throwable rather than \Exception — an SDK or
    // malformed-credential failure can surface as a TypeError (a PHP Error, not an
    // Exception; Twilio's client throws exactly this when the account SID is null), which
    // a narrower catch lets crash the whole queued job instead of recording a normal
    // failed-send result.
    private function sendViaTwilio($receiverNumber, $message)
    {
        try {
            $client = new TwilioClient($this->twilioSid, $this->twilioToken);
            $client->messages->create($receiverNumber, [
                'from' => $this->twilioNumber,
                'body' => $message,
            ]);

            return ['success' => true, 'message' => 'SMS Sent via Twilio Successfully.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function sendViaBulkSmsBD($receiverNumber, $message)
    {
        try {
            $client = new Client;
            $url = "http://bulksmsbd.net/api/smsapi?api_key={$this->apiKey}&type=text&number={$receiverNumber}&senderid={$this->senderId}&message=".urlencode($message);
            $response = $client->get($url);
            $responseData = json_decode($response->getBody(), true);

            if (($responseData['response_code'] ?? null) == 202) {
                return ['success' => true, 'message' => 'SMS Sent via BulkSMSBD Successfully.'];
            }

            return ['success' => false, 'error' => $responseData['error_message'] ?? 'BulkSMSBD rejected the request.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function sendViaInfobip($receiverNumber, $message)
    {
        try {
            $baseUrl = rtrim((string) get_option('infobip_base_url'), '/');
            $apiKey = get_option('infobip_api_key');
            $sender = get_option('infobip_sender_id');

            if (empty($baseUrl) || empty($apiKey)) {
                return ['success' => false, 'error' => 'Infobip credentials are not configured.'];
            }

            $client = new Client;
            $response = $client->post("{$baseUrl}/sms/2/text/advanced", [
                'headers' => [
                    'Authorization' => "App {$apiKey}",
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'messages' => [[
                        'destinations' => [['to' => $receiverNumber]],
                        'from' => $sender ?: 'InfoSMS',
                        'text' => $message,
                    ]],
                ],
                'http_errors' => false,
                'timeout' => 20,
            ]);

            $responseData = json_decode((string) $response->getBody(), true) ?: [];
            $status = $responseData['messages'][0]['status']['groupName'] ?? null;

            if ($response->getStatusCode() < 300 && strtoupper((string) $status) === 'PENDING') {
                return ['success' => true, 'message' => 'SMS Sent via Infobip Successfully.'];
            }

            return [
                'success' => false,
                'error' => $responseData['messages'][0]['status']['description'] ?? $responseData['requestError']['serviceException']['text'] ?? 'Infobip rejected the request.',
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function sendViaClickatell($receiverNumber, $message)
    {
        try {
            $apiKey = get_option('clickatell_api_key');

            if (empty($apiKey)) {
                return ['success' => false, 'error' => 'Clickatell credentials are not configured.'];
            }

            $client = new Client;
            $response = $client->post('https://platform.clickatell.com/messages', [
                'headers' => [
                    'Authorization' => $apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'content' => $message,
                    'to' => [$receiverNumber],
                ],
                'http_errors' => false,
                'timeout' => 20,
            ]);

            $responseData = json_decode((string) $response->getBody(), true) ?: [];
            $accepted = $responseData['messages'][0]['accepted'] ?? false;

            if ($response->getStatusCode() < 300 && $accepted) {
                return ['success' => true, 'message' => 'SMS Sent via Clickatell Successfully.'];
            }

            return [
                'success' => false,
                'error' => $responseData['messages'][0]['error']['description'] ?? $responseData['error']['description'] ?? 'Clickatell rejected the request.',
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function sendViaMessageBird($receiverNumber, $message)
    {
        try {
            $apiKey = get_option('messagebird_api_key');
            $originator = get_option('messagebird_originator');

            if (empty($apiKey)) {
                return ['success' => false, 'error' => 'MessageBird credentials are not configured.'];
            }

            $client = new Client;
            $response = $client->post('https://rest.messagebird.com/messages', [
                'headers' => [
                    'Authorization' => "AccessKey {$apiKey}",
                    'Accept' => 'application/json',
                ],
                'form_params' => [
                    'originator' => $originator ?: 'MSchool',
                    'recipients' => $receiverNumber,
                    'body' => $message,
                ],
                'http_errors' => false,
                'timeout' => 20,
            ]);

            $responseData = json_decode((string) $response->getBody(), true) ?: [];

            if ($response->getStatusCode() < 300 && ! empty($responseData['id'])) {
                return ['success' => true, 'message' => 'SMS Sent via MessageBird Successfully.'];
            }

            return [
                'success' => false,
                'error' => $responseData['errors'][0]['description'] ?? 'MessageBird rejected the request.',
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function sendViaMimSms($receiverNumber, $message)
    {
        try {
            $userName = get_option('mimsms_username');
            $apiKey = get_option('mimsms_api_key');
            $senderName = get_option('mimsms_sender_name');

            if (empty($userName) || empty($apiKey) || empty($senderName)) {
                return ['success' => false, 'error' => 'MimSMS credentials are not configured.'];
            }

            $client = new Client;
            $response = $client->post('https://api.mimsms.com/sms/send', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'UserName' => $userName,
                    'Apikey' => $apiKey,
                    'MobileNumber' => $this->formatBdMobile($receiverNumber),
                    'CampaignId' => 'null',
                    'SenderName' => $senderName,
                    'TransactionType' => 'T',
                    'Message' => $message,
                ],
                'http_errors' => false,
                'timeout' => 20,
            ]);

            $responseData = json_decode((string) $response->getBody(), true) ?: [];
            $statusCode = (string) ($responseData['statusCode'] ?? $response->getStatusCode());
            $status = strtolower((string) ($responseData['status'] ?? ''));

            if (in_array($statusCode, ['200', '201', '202'], true) || in_array($status, ['success', 'ok', 'true'], true)) {
                return ['success' => true, 'message' => 'SMS Sent via MimSMS Successfully.'];
            }

            return [
                'success' => false,
                'error' => $responseData['message'] ?? $responseData['error'] ?? 'MimSMS rejected the request.',
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function formatBdMobile($number): string
    {
        $digits = preg_replace('/\D+/', '', (string) $number) ?: '';

        if (str_starts_with($digits, '880')) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '88'.$digits;
        }

        if (str_starts_with($digits, '1') && strlen($digits) === 10) {
            return '880'.$digits;
        }

        return $digits;
    }
}
