<?php

namespace App\Console\Commands;

use App\Jobs\SendWhatsAppJob;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Modules\Academic\Models\WhatsAppLog;

class ProcessWhatsAppLogs extends Command
{
    protected $signature = 'whatsapp:process-logs
                            {--limit=500 : Maximum logs to dispatch in a single run}
                            {--chunk=100 : Rows loaded into memory at a time}';

    protected $description = 'Dispatch pending WhatsApp logs and re-queue failed logs that have not exceeded the retry limit';

    public function handle(): int
    {
        // Recover rows stuck mid-send: SendWhatsAppJob claims a log by flipping it
        // to 'sending' before calling the provider. If that worker dies, the row
        // would otherwise stay 'sending' forever. Reclaim anything older than 10
        // minutes (matches the SmsLog status-2 recovery window).
        WhatsAppLog::where('status', 'sending')
            ->where('updated_at', '<', now()->subMinutes(10))
            ->update(['status' => 'pending']);

        WhatsAppLog::where('status', 'failed')
            ->where('retry_count', '<', WhatsAppService::MAX_RETRIES)
            ->update(['status' => 'pending']);

        $limit = max(1, (int) $this->option('limit'));
        $chunk = max(1, min((int) $this->option('chunk'), $limit));
        $dispatched = 0;

        // Streamed in id order rather than loaded with a single unbounded get().
        // This command has never actually been scheduled, so the first real runs
        // face the entire accumulated backlog; hydrating that in one go would
        // exhaust memory. chunkById() is required here rather than chunk() because
        // the loop mutates the very column the query filters on.
        WhatsAppLog::where('status', 'pending')
            ->orderBy('id')
            ->chunkById($chunk, function ($logs) use (&$dispatched, $limit) {
                foreach ($logs as $log) {
                    if ($dispatched >= $limit) {
                        return false;
                    }

                    $log->update(['status' => 'queued']);
                    dispatch(new SendWhatsAppJob($log));
                    $dispatched++;
                }

                return true;
            });

        $this->info("WhatsApp logs dispatched: {$dispatched}");

        if ($dispatched >= $limit) {
            $this->comment('Run limit reached — remaining logs will be picked up on the next run.');
        }

        return self::SUCCESS;
    }
}
