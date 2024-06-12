<?php

namespace App\Console\Commands;

use App\Jobs\GenerateMonthlyInvoices;
use Illuminate\Console\Command;

class RunInvoiceJobLoop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:loop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run GenerateMonthlyInvoices job every 5 seconds (for testing)';

    public function handle()
    {
        $this->info('Starting job loop... Press Ctrl+C to stop.');

        // while (true) {
        //     dispatch(new GenerateMonthlyInvoices);
        //     $this->info('Job dispatched at '.now());
        //     sleep(300); // wait 5 seconds
        // }
    }
}
