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
            $query = "Dame un resumen conciso de las últimas noticias y movimientos de mercado para {$asset->symbol} ({$asset->name}) el {$date->format('d/m/Y')}. Incluye: catalizadores clave, sentimiento del mercado, y si hay eventos relevantes (earnings, FDA, macro). Máximo 3 puntos clave.";

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->perplexityKey}",
                    'Content-Type'  => 'application/json',
                ])
                ->post('https://api.perplexity.ai/chat/completions', [
                    'model' => 'sonar',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Eres un analista financiero experto. Responde siempre en español. Sé conciso y directo.'],
                        ['role' => 'user', 'content' => $query],
                    ],
                    'temperature' => 0.2,
                    'max_tokens' => 600,
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
            $query = "Resume las últimas noticias sobre {$asset->symbol} ({$asset->name}) al {$date->format('d/m/Y')}. Incluye catalizadores clave, sentimiento del mercado y eventos relevantes. Sé conciso, máximo 3 puntos.";

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
