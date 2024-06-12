<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Examination\Services\OnlineExamScoringService;

/**
 * Submits (and marks) every online-exam attempt whose time ran out. Without
 * this an attempt the student abandoned — closed tab, lost connection — stayed
 * "started" forever and never reached results or the mark transfer.
 */
class AutoSubmitOnlineExams extends Command
{
    protected $signature = 'online-exams:auto-submit';

    protected $description = 'Auto-submit and mark online exam attempts whose time has expired';

    public function handle(OnlineExamScoringService $scoring): int
    {
        $count = $scoring->autoSubmitExpired();
        $this->info("Auto-submitted {$count} expired attempt(s).");

        return self::SUCCESS;
    }
}
