<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\TradeMetricsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RecalculateUserMetrics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Log::info('Starting metrics recalculation for all users');

        $users = User::all();
        $service = new TradeMetricsService();

        foreach ($users as $user) {
            try {
                $service->calculateAllUserMetrics($user);
                Log::info("Recalculated metrics for user {$user->id}");
            } catch (\Exception $e) {
                Log::error("Failed to recalculate metrics for user {$user->id}: " . $e->getMessage());
            }
        }

        Log::info('Metrics recalculation completed');
    }
}
