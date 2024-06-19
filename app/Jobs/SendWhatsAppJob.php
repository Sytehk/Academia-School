<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Academic\Models\WhatsAppLog;
use Throwable;

class SendWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Kill a stuck provider HTTP call rather than hanging the worker. */
    public int $timeout = 60;

    public function __construct(protected WhatsAppLog $whatsAppLog) {}

    public function handle(WhatsAppService $whatsAppService): void
    {
        $log = $this->whatsAppLog->fresh();
        if (! $log || $log->status === 'sent') {
            return;
        }

        // Atomic claim to prevent a double-send race. This log can be dispatched
        // from two places almost simultaneously: immediately by queue()/queueForTenant(),
        // and again by the whatsapp:process-logs cron (which flips pending -> queued).
        // Without a claim, two workers could both read a non-'sent' status and each
        // call send(), delivering the same message twice. Only the worker that wins
        // this conditional update (dispatchable status -> 'sending') proceeds; this
        // mirrors the proven SmsLog 0 -> 2 claim in SendSmsJob.
        $claimed = WhatsAppLog::where('id', $log->id)
            ->whereIn('status', ['pending', 'queued'])
            ->update(['status' => 'sending']);

        if (! $claimed) {
            return;
        }

        try {
            // send() records the outcome itself: 'sent' on success, or 'failed'
            // with retry_count++ on failure. The cron re-queues 'failed' rows while
            // retry_count < MAX_RETRIES, so that remains the single retry loop — we
            // intentionally do NOT add queue-level retries here (that would double it).
            $whatsAppService->send($log);
        } catch (Throwable $e) {
            // send() catches provider errors internally, but a storage error (e.g.
            // the status update) could still bubble up and leave the row stuck at
            // 'sending'. Reset it so the cron can pick it up again, then rethrow for
            // failed_jobs visibility — same contract as SendSmsJob.
            WhatsAppLog::where('id', $log->id)
                ->where('status', 'sending')
                ->update(['status' => 'pending']);

            throw $e;
        }
    }
}
