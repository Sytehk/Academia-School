<?php

namespace App\Console\Commands;

use App\Jobs\SendSmsJob;
use Illuminate\Console\Command;
use Modules\Academic\Models\SmsLog;

class ProcessSmsLogs extends Command
{
    protected $signature = 'sms:process-logs
                            {--limit=500 : Maximum logs to dispatch in a single run}
                            {--chunk=100 : Rows loaded into memory at a time}';

    protected $description = 'Process unsent SMS logs';

    public function handle(): int
    {
        // Reclaim rows a dead worker left mid-send (status 2 = claimed).
        SmsLog::where('status', 2)
            ->where('updated_at', '<', now()->subMinutes(10))
            ->update(['status' => 0]);

        $limit = max(1, (int) $this->option('limit'));
        $chunk = max(1, min((int) $this->option('chunk'), $limit));
        $dispatched = 0;

        // Streamed rather than loaded with a single unbounded get(). The bulk SMS
        // compose screen writes SmsLog rows directly at status 0 without dispatching
        // a job, so this command is the only thing that sends them — and it has
        // never been scheduled, meaning the first real runs face a large backlog.
        //
        // SendSmsJob claims each row (0 -> 2) itself, so a row already dispatched in
        // an earlier chunk cannot be picked up twice; chunkById keeps that safe even
        // though the status column is the one being filtered on.
        SmsLog::where('status', 0)
            ->orderBy('id')
            ->chunkById($chunk, function ($smsLogs) use (&$dispatched, $limit) {
                foreach ($smsLogs as $smsLog) {
                    if ($dispatched >= $limit) {
                        return false;
                    }

                    dispatch(new SendSmsJob($smsLog));
                    $dispatched++;
                }

                return true;
            });

        $this->info("SMS logs dispatched: {$dispatched}");

        if ($dispatched >= $limit) {
            $this->comment('Run limit reached — remaining logs will be picked up on the next run.');
        }

        return self::SUCCESS;
    }
}
