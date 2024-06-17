<?php

namespace App\Services;

use Modules\Academic\Models\SmsLog;
use Modules\Academic\Models\WhatsAppLog;

class NotificationDispatchService
{
    public const CHANNEL_NONE = 'none';

    public const CHANNEL_SMS = 'sms';

    public const CHANNEL_WHATSAPP = 'whatsapp';

    public const CHANNEL_BOTH = 'both';

    public function __construct(
        private readonly SmsService $smsService,
        private readonly WhatsAppService $whatsAppService,
    ) {}

    public function resolveChannel(?string $channel, mixed $smsStatus = null): string
    {
        $channel = is_string($channel) ? strtolower(trim($channel)) : '';

        if (in_array($channel, [self::CHANNEL_SMS, self::CHANNEL_WHATSAPP, self::CHANNEL_BOTH, self::CHANNEL_NONE], true)) {
            return $channel;
        }

        return ((int) $smsStatus === 1) ? self::CHANNEL_BOTH : self::CHANNEL_NONE;
    }

    public function shouldSendSms(string $channel): bool
    {
        return in_array($channel, [self::CHANNEL_SMS, self::CHANNEL_BOTH], true);
    }

    public function shouldSendWhatsApp(string $channel): bool
    {
        return in_array($channel, [self::CHANNEL_WHATSAPP, self::CHANNEL_BOTH], true);
    }

    public function studentPhone(?object $student): ?string
    {
        if (! $student) {
            return null;
        }

        $phone = $student->information_sent_to_phone ?: ($student->phone ?? null);

        return $phone ? trim((string) $phone) : null;
    }

    public function queueSms(?string $phone, string $message, string $userType, int $senderId, array $extra = []): ?SmsLog
    {
        if (! $phone) {
            return null;
        }

        return $this->smsService->queueLog($phone, $message, $userType, $senderId, $extra);
    }

    public function queueWhatsApp(
        ?string $phone,
        string $event,
        array $parameters = [],
        $studentId = null,
        ?string $referenceType = null,
        $referenceId = null
    ): ?WhatsAppLog {
        if (! $phone) {
            return null;
        }

        return $this->whatsAppService->queue(
            $phone,
            $event,
            $parameters,
            $studentId,
            $referenceType,
            $referenceId
        );
    }
}
