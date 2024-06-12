<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessNotificationLogs extends Command
{
    protected $signature = 'notifications:process';

    protected $description = 'Process pending SMS and WhatsApp notification logs';

    public function handle(): int
    {
        $this->call('sms:process-logs');
        $this->call('whatsapp:process-logs');

        return self::SUCCESS;
    }
}
