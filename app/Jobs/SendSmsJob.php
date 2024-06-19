<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Academic\Models\SmsLog;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $smsLog;

    public function __construct(SmsLog $smsLog)
    {
        $this->smsLog = $smsLog;
    }

    public function handle()
    {
        $smsLog = $this->smsLog?->fresh();
        if (! $smsLog || (int) $smsLog->status === 1) {
            return;
        }

        $claimed = SmsLog::where('id', $smsLog->id)
            ->where('status', 0)
            ->update(['status' => 2]);

        if (! $claimed) {
            return;
        }

        try {
            $response = sent_sms($smsLog->receiver, $smsLog->message);
            $smsLog->status = ! empty($response['success']) ? 1 : 0;
            $smsLog->save();
        } catch (\Throwable $e) {
            $smsLog->status = 0;
            $smsLog->save();

            throw $e;
        }
    }
}
