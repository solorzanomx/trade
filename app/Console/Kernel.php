<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\GenerateDailyNewsSummaries;
use App\Jobs\RecalculateUserMetrics;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Generate daily news summaries at 8:00 AM
        $schedule->job(new GenerateDailyNewsSummaries)
            ->dailyAt('08:00')
            ->onOneServer();

        // Recalculate metrics nightly at 11:00 PM
        $schedule->job(new RecalculateUserMetrics)
            ->dailyAt('23:00')
            ->onOneServer();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
