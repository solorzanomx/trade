<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Asset;
use App\Models\Trade;
use App\Models\DailyNewsSummary;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo user
        $user = User::firstOrCreate(
            ['email' => 'demo@trading.local'],
            [
                'name' => 'Demo Trader',
                'password' => bcrypt('password123'),
            ]
        );

        // Create assets
        $assets = [
            ['symbol' => 'AAPL', 'name' => 'Apple Inc.', 'asset_type' => 'stock'],
            ['symbol' => 'MSFT', 'name' => 'Microsoft Corp.', 'asset_type' => 'stock'],
            ['symbol' => 'TSLA', 'name' => 'Tesla Inc.', 'asset_type' => 'stock'],
            ['symbol' => 'QQQ', 'name' => 'Nasdaq ETF', 'asset_type' => 'etf'],
            ['symbol' => 'SPY', 'name' => 'S&P 500 ETF', 'asset_type' => 'etf'],
        ];

        $createdAssets = [];
        foreach ($assets as $asset) {
            $createdAssets[$asset['symbol']] = Asset::firstOrCreate(
                ['user_id' => $user->id, 'symbol' => $asset['symbol']],
                array_merge($asset, ['user_id' => $user->id])
            );
        }

        // Create demo trades
        $trades = [
            ['symbol' => 'AAPL', 'entry_price' => 150.25, 'exit_price' => 152.50, 'entry_date' => now()->subDays(5), 'exit_date' => now()->subDays(4), 'quantity' => 100, 'capital_used' => 15025, 'stop_loss' => 148.00, 'take_profit' => 155.00],
            ['symbol' => 'MSFT', 'entry_price' => 380.50, 'exit_price' => 378.20, 'entry_date' => now()->subDays(4), 'exit_date' => now()->subDays(3), 'quantity' => 50, 'capital_used' => 19025, 'stop_loss' => 375.00, 'take_profit' => 390.00],
            ['symbol' => 'TSLA', 'entry_price' => 240.00, 'exit_price' => 248.50, 'entry_date' => now()->subDays(3), 'exit_date' => now()->subDays(2), 'quantity' => 25, 'capital_used' => 6000, 'stop_loss' => 235.00, 'take_profit' => 255.00],
            ['symbol' => 'QQQ', 'entry_price' => 420.75, 'exit_price' => 425.30, 'entry_date' => now()->subDays(2), 'exit_date' => now()->subDays(1), 'quantity' => 20, 'capital_used' => 8415, 'stop_loss' => 415.00, 'take_profit' => 435.00],
            ['symbol' => 'SPY', 'entry_price' => 510.20, 'exit_price' => 508.50, 'entry_date' => now()->subDays(1), 'exit_date' => now(), 'quantity' => 15, 'capital_used' => 7653, 'stop_loss' => 505.00, 'take_profit' => 520.00],
        ];

        foreach ($trades as $tradeData) {
            $symbol = $tradeData['symbol'];
            $entryDate = $tradeData['entry_date'];
            $exitDate = $tradeData['exit_date'];

            $trade = Trade::create([
                'user_id' => $user->id,
                'asset_id' => $createdAssets[$symbol]->id,
                'symbol' => $symbol,
                'trade_type' => 'stock',
                'position_direction' => 'long',
                'entry_price' => $tradeData['entry_price'],
                'entry_date' => $entryDate,
                'entry_time' => '09:30',
                'entry_reason' => 'Technical breakout with volume confirmation',
                'exit_price' => $tradeData['exit_price'],
                'exit_date' => $exitDate,
                'exit_time' => '15:00',
                'exit_reason' => 'Hit target resistance',
                'quantity' => $tradeData['quantity'],
                'capital_used' => $tradeData['capital_used'],
                'stop_loss' => $tradeData['stop_loss'],
                'take_profit' => $tradeData['take_profit'],
                'status' => 'closed',
                'emotional_state' => ['Calm', 'Focused', 'Confident'][rand(0, 2)],
                'mistakes_made' => rand(0, 1) ? 'Exited too early' : null,
            ]);

            // Calculate P&L
            $trade->calculatePnL();
            $trade->save();

            // Add demo news summaries
            DailyNewsSummary::create([
                'user_id' => $user->id,
                'asset_id' => $createdAssets[$symbol]->id,
                'date' => $exitDate,
                'summary' => "📊 {$symbol}: Strong momentum observed. Volume indicators show healthy participation. Key resistance at \${$tradeData['exit_price']}. Analysts remain bullish on fundamentals.",
                'source' => 'claude',
                'key_points' => [
                    "Strong volume at resistance level",
                    "Technical indicators align with bullish trend",
                    "Earnings report upcoming next week"
                ],
                'sentiment' => ['positive', 'positive', 'neutral', 'positive'][rand(0, 3)],
            ]);
        }

        echo "✓ Demo data created for user: demo@trading.local (password: password123)\n";
    }
}
