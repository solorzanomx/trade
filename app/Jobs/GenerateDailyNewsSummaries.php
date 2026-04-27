<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\NewsAggregationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GenerateDailyNewsSummaries implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Log::info('Starting daily news summaries generation');

        $users = User::all();
        $service = new NewsAggregationService();
        $yesterday = now()->subDay();

        foreach ($users as $user) {
            $activeAssets = $user->assets()->where('is_active', true)->get();

            foreach ($activeAssets as $asset) {
                try {
                    $service->generateNewsSummary($asset, $yesterday);
                    Log::info("Generated news for {$asset->symbol} for user {$user->id}");
                } catch (\Exception $e) {
                    Log::error("Failed to generate news for {$asset->symbol}: " . $e->getMessage());
                }
            }
        }

        Log::info('Daily news summaries generation completed');
    }
}
