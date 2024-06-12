<?php

namespace App\Console;

use App\Console\Commands\ProcessNotificationLogs;
use App\Console\Commands\ProcessSmsLogs;
use App\Console\Commands\ProcessWhatsAppLogs;
use App\Console\Commands\ResetSystemData;
use App\Console\Commands\SeedWhatsAppTemplatesCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ProcessSmsLogs::class,
        ProcessWhatsAppLogs::class,
        ProcessNotificationLogs::class,
        SeedWhatsAppTemplatesCommand::class,
        ResetSystemData::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('sms:process-logs')->everyMinute()->withoutOverlapping();
        $schedule->command('whatsapp:process-logs')->everyMinute()->withoutOverlapping();

        // `system:reset` runs migrate:fresh + full reseed — destroys all data.
        // Intentionally not scheduled. Run it manually (`php artisan system:reset`)
        // only against a disposable demo/staging environment, never production.
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
