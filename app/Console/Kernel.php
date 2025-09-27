<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Check for rent payments daily at 9 AM
        $schedule->command('rent:check')
                 ->dailyAt('09:00')
                 ->withoutOverlapping()
                 ->onOneServer();

        // Additional check at 6 PM for any late payments
        $schedule->command('rent:check')
                 ->dailyAt('18:00')
                 ->withoutOverlapping()
                 ->onOneServer();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}