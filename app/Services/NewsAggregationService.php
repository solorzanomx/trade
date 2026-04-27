<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\DailyNewsSummary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsAggregationService
{
    private string $perplexityKey;
    private string $claudeKey;

    public function __construct()
    {
        $this->perplexityKey = config('services.perplexity.api_key');
        $this->claudeKey = config('services.anthropic.api_key');
    }

    public function generateNewsSummary(Asset $asset, Carbon $date = null): DailyNewsSummary
    {
        $date = $date ?? now()->subDay();

        // Check if summary already exists
        $existing = DailyNewsSummary::where('asset_id', $asset->id)
            ->where('user_id', $asset->user_id)
            ->whereDate('date', $date)
            ->first();

        if ($existing) {
            return $existing;
        }

        try {
            // Try Perplexity first
            $summary = $this->fetchFromPerplexity($asset, $date);

            if (!$summary) {
                // Fallback to Claude
                $summary = $this->fetchFromClaude($asset, $date);
            }

            $source = $summary['source'];
            $summaryText = $summary['text'];
            $keyPoints = $summary['key_points'] ?? [];
            $sentiment = $summary['sentiment'] ?? 'neutral';
        } catch (\Exception $e) {
            Log::error('News generation error: ' . $e->getMessage());
            $source = 'manual';
            $summaryText = 'Unable to generate summary. Please check back later.';
            $keyPoints = [];
            $sentiment = 'neutral';
        }

        return DailyNewsSummary::create([
            'user_id' => $asset->user_id,
            'asset_id' => $asset->id,
            'date' => $date,
            'summary' => $summaryText,
            'source' => $source,
            'key_points' => $keyPoints,
            'sentiment' => $sentiment,
        ]);
    }

    private function fetchFromPerplexity(Asset $asset, Carbon $date): ?array
    {
        if (!$this->perplexityKey) {
            return null;
        }

        try {
            $query = "Provide a concise summary of the latest news and market developments for {$asset->symbol} on {$date->format('Y-m-d')}. Include key points and overall sentiment.";

            $response = Http::timeout(30)
                ->withHeaders(['Authorization' => "Bearer {$this->perplexityKey}"])
                ->post('https://api.perplexity.ai/openai/deployments/pplx-7b-online/chat/completions', [
                    'model' => 'pplx-7b-online',
                    'messages' => [
                        ['role' => 'user', 'content' => $query],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 500,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');

                return [
                    'source' => 'perplexity',
                    'text' => $content,
                    'key_points' => $this->extractKeyPoints($content),
                    'sentiment' => $this->analyzeSentiment($content),
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Perplexity API error: ' . $e->getMessage());
        }

        return null;
    }

    private function fetchFromClaude(Asset $asset, Carbon $date): array
    {
        if (!$this->claudeKey) {
            return [
                'source' => 'manual',
                'text' => 'No API keys configured.',
                'key_points' => [],
                'sentiment' => 'neutral',
            ];
        }

        try {
            $query = "Summarize the latest news about {$asset->symbol} as of {$date->format('Y-m-d')}. Include key market developments and sentiment. Keep it concise.";

            $response = Http::timeout(30)
                ->withHeaders([
                    'x-api-key' => $this->claudeKey,
                    'anthropic-version' => '2023-06-01',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => 'claude-3-5-sonnet-20241022',
                    'max_tokens' => 500,
                    'messages' => [
                        ['role' => 'user', 'content' => $query],
                    ],
                ]);

            if ($response->successful()) {
                $content = $response->json('content.0.text');

                return [
                    'source' => 'claude',
                    'text' => $content,
                    'key_points' => $this->extractKeyPoints($content),
                    'sentiment' => $this->analyzeSentiment($content),
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Claude API error: ' . $e->getMessage());
        }

        return [
            'source' => 'manual',
            'text' => 'Unable to fetch news summary.',
            'key_points' => [],
            'sentiment' => 'neutral',
        ];
    }

    private function extractKeyPoints(string $text): array
    {
        // Simple extraction of sentences that look like key points
        $sentences = explode('.', $text);
        $points = [];

        foreach (array_slice($sentences, 0, 5) as $sentence) {
            $trimmed = trim($sentence);
            if (strlen($trimmed) > 20 && strlen($trimmed) < 200) {
                $points[] = $trimmed;
            }
        }

        return array_slice($points, 0, 3);
    }

    private function analyzeSentiment(string $text): string
    {
        $positive = ['positive', 'gains', 'up', 'growth', 'strong', 'bullish', 'surge', 'rally'];
        $negative = ['decline', 'down', 'loss', 'weak', 'bearish', 'fall', 'crash', 'drop'];

        $textLower = strtolower($text);
        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($positive as $word) {
            $positiveCount += substr_count($textLower, $word);
        }
        foreach ($negative as $word) {
            $negativeCount += substr_count($textLower, $word);
        }

        if ($positiveCount > $negativeCount) {
            return 'positive';
        } elseif ($negativeCount > $positiveCount) {
            return 'negative';
        }

        return 'neutral';
    }
}
