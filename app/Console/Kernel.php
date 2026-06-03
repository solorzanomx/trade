<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\GenerateDailyNewsSummaries;
use App\Jobs\RecalculateUserMetrics;
use App\Console\Commands\GenerateDailyReport;

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

        // Reporte diario QQQ — 9:35 AM CDMX (14:35 UTC) lunes a viernes
        $schedule->command('reports:generate')
            ->weekdays()
            ->at('14:35')
            ->timezone('UTC')
            ->withoutOverlapping()
            ->onOneServer();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
