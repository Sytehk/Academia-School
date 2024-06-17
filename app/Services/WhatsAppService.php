<?php

namespace App\Services;

use App\Jobs\SendWhatsAppJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Academic\Models\WhatsAppLog;
use Modules\Academic\Models\WhatsAppSetting;
use Modules\Academic\Models\WhatsAppTemplate;
use Twilio\Rest\Client as TwilioClient;

class WhatsAppService
{
    public const MAX_RETRIES = 5;

    /**
     * Memoises resolveSetting() and the per-event template lookup for the life
     * of this service instance. The device-sync endpoint resolves ONE
     * WhatsAppService and calls queueForTenant() once per punch in a loop --
     * for a 300-punch sync, the setting and template are identical on every
     * call (same tenant, same 'attendance_in'/'attendance_out' event), so this
     * turned two provably-repeated queries per call into ~600 wasted queries
     * for the batch. The duplicate-reference exists() check a few lines below
     * is deliberately NOT memoised here -- it is genuinely per-message
     * (different reference_id each time) and reflects live send state.
     *
     * @var array<string, WhatsAppSetting|false>
     */
    private array $settingCache = [];

    /** @var array<string, WhatsAppTemplate|false> */
    private array $templateCache = [];

    /**
     * Render the template for $event, and queue a pending WhatsAppLog for later sending.
     * Returns null (and creates nothing) if the module is disabled, the template is missing,
     * or an identical notification for this reference was already queued/sent.
     */
    public function queue(
        string $phone,
        string $event,
        array $parameters = [],
        $studentId = null,
        ?string $referenceType = null,
        $referenceId = null
    ): ?WhatsAppLog {
        return $this->queueForTenant(
            get_institute_id(),
            get_branch_id(),
            $phone,
            $event,
            $parameters,
            $studentId,
            $referenceType,
            $referenceId
        );
    }

    /**
     * Tenant-explicit variant of queue(). The biometric device-sync endpoint and the
     * queue worker both run without an authenticated user, so the tenant cannot be
     * resolved from auth(); it is passed explicitly instead. Behaviour is otherwise
     * identical to queue() (same duplicate prevention, template rendering and logging).
     */
    public function queueForTenant(
        $instituteId,
        $branchId,
        string $phone,
        string $event,
        array $parameters = [],
        $studentId = null,
        ?string $referenceType = null,
        $referenceId = null
    ): ?WhatsAppLog {
        if (! $phone) {
            return null;
        }

        $setting = $this->resolveSetting($instituteId, $branchId);
        if (! $setting || ! $setting->status) {
            return null;
        }

        if ($referenceType && $referenceId) {
            // Include 'sending' (the transient claim state set by SendWhatsAppJob):
            // a message already in-flight for this reference must not be re-queued
            // into a duplicate, exactly like the pending/queued/sent states.
            $alreadyQueued = WhatsAppLog::where('reference_type', $referenceType)
                ->where('reference_id', $referenceId)
                ->whereIn('status', ['pending', 'queued', 'sending', 'sent'])
                ->exists();

            if ($alreadyQueued) {
                return null;
            }
        }

        $template = $this->resolveTemplate($instituteId, $event);

        if (! $template) {
            Log::warning('WhatsApp template not found for event: '.$event);

            return null;
        }

        $message = $template->message;
        foreach ($parameters as $key => $value) {
            $message = str_replace('{{'.$key.'}}', (string) $value, $message);
        }

        $log = WhatsAppLog::create([
            'institute_id' => $instituteId,
            'branch_id' => $branchId,
            'student_id' => $studentId,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'phone' => $phone,
            'message' => $message,
            'status' => 'pending',
            'retry_count' => 0,
        ]);

        dispatch(new SendWhatsAppJob($log));

        return $log;
    }

    /**
     * Actually deliver a queued WhatsAppLog via the configured provider.
     */
    public function send(WhatsAppLog $log): bool
    {
        $setting = $this->resolveSetting($log->institute_id, $log->branch_id);
        if (! $setting || ! $setting->status) {
            $log->update(['status' => 'failed', 'response' => 'WhatsApp module disabled or not configured.']);

            return false;
        }

        $result = match ($setting->provider) {
            'twilio' => $this->sendViaTwilio($setting, $log),
            default => $this->sendViaMeta($setting, $log),
        };

        if ($result['success']) {
            $log->update([
                'status' => 'sent',
                'response' => json_encode($result['response']),
            ]);

            return true;
        }

        $log->update([
            'status' => 'failed',
            'response' => json_encode($result['response']),
            'retry_count' => $log->retry_count + 1,
        ]);

        return false;
    }

    /**
     * Test the currently configured provider's credentials without sending a message.
     */
    public function testConnection(): array
    {
        $setting = whatsappSetting();
        if (! $setting) {
            return ['success' => false, 'message' => 'No active WhatsApp setting found.'];
        }

        try {
            if ($setting->provider === 'twilio') {
                $client = new TwilioClient($setting->business_account_id, $setting->access_token);
                $client->api->v2010->account->fetch();

                return ['success' => true, 'message' => 'Twilio credentials verified successfully.'];
            }

            $response = Http::withToken($setting->access_token)
                ->get('https://graph.facebook.com/v21.0/'.$setting->phone_number_id, [
                    'fields' => 'verified_name,display_phone_number',
                ]);

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Meta WhatsApp Cloud API connection verified successfully.'];
            }

            return ['success' => false, 'message' => 'Connection failed: '.$response->body()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Resolve the active WhatsApp setting for a tenant. The existing auth-based
     * whatsappSetting() helper is tried first so behaviour for logged-in users is
     * unchanged; when there is no authenticated user (public device-sync endpoint,
     * queue worker) the setting is resolved from the explicit institute/branch instead.
     */
    private function resolveSetting($instituteId, $branchId): ?WhatsAppSetting
    {
        $key = ($instituteId ?? 'null').'|'.($branchId ?? 'null');

        if (array_key_exists($key, $this->settingCache)) {
            $cached = $this->settingCache[$key];

            return $cached === false ? null : $cached;
        }

        $setting = $this->resolveSettingUncached($instituteId, $branchId);

        // false, not null, marks "resolved, and the answer is none" so a
        // repeated miss for the same tenant does not keep re-querying.
        $this->settingCache[$key] = $setting ?? false;

        return $setting;
    }

    private function resolveSettingUncached($instituteId, $branchId): ?WhatsAppSetting
    {
        // Preserve the existing auth-based resolution whenever a user is present.
        // (Skipped when unauthenticated: whatsappSetting() would then run
        // where('institute_id', null), which could match a NULL-tenant row.)
        if (auth()->check()) {
            $setting = whatsappSetting();
            if ($setting) {
                return $setting;
            }
        }

        // Unauthenticated contexts (public device-sync endpoint, queue worker):
        // resolve strictly by the explicit tenant.
        if (! $instituteId) {
            return null;
        }

        return WhatsAppSetting::where('institute_id', $instituteId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)->orWhereNull('branch_id');
            })
            ->where('status', 1)
            ->orderByRaw('branch_id IS NULL')
            ->first();
    }

    private function resolveTemplate($instituteId, string $event): ?WhatsAppTemplate
    {
        $key = ($instituteId ?? 'null').'|'.$event;

        if (array_key_exists($key, $this->templateCache)) {
            $cached = $this->templateCache[$key];

            return $cached === false ? null : $cached;
        }

        $template = WhatsAppTemplate::where('institute_id', $instituteId)
            ->where('event', $event)
            ->where('status', 1)
            ->first();

        $this->templateCache[$key] = $template ?? false;

        return $template;
    }

    private function sendViaMeta($setting, WhatsAppLog $log): array
    {
        $response = Http::withToken($setting->access_token)
            ->post('https://graph.facebook.com/v21.0/'.$setting->phone_number_id.'/messages', [
                'messaging_product' => 'whatsapp',
                'to' => $log->phone,
                'type' => 'text',
                'text' => ['body' => $log->message],
            ]);

        $data = $response->json();
        if (! $response->successful()) {
            Log::error('WhatsApp (Meta) send failed', ['status' => $response->status(), 'response' => $data]);
        }

        return [
            'success' => $response->successful(),
            'response' => ['http_code' => $response->status(), 'data' => $data],
        ];
    }

    private function sendViaTwilio($setting, WhatsAppLog $log): array
    {
        try {
            $client = new TwilioClient($setting->business_account_id, $setting->access_token);
            $message = $client->messages->create(
                'whatsapp:'.$log->phone,
                [
                    'from' => 'whatsapp:'.$setting->phone_number_id,
                    'body' => $log->message,
                ]
            );

            return [
                'success' => true,
                'response' => ['sid' => $message->sid, 'status' => $message->status],
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsApp (Twilio) send failed', ['message' => $e->getMessage()]);

            return ['success' => false, 'response' => ['error' => $e->getMessage()]];
        }
    }
}
