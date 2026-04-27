# 📊 Trading Journal Platform - Setup Guide

## Quick Start

### 1. Install Dependencies
```bash
composer install
npm install
```

### 2. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database Setup
```bash
php artisan migrate
```

### 4. Start Development Server
```bash
# Terminal 1: Run Laravel server
php artisan serve

# Terminal 2: Run Vite for frontend assets
npm run dev

# Terminal 3: Run scheduler (for daily jobs)
php artisan schedule:work
```

Visit `http://localhost:8000`

---

## API Configuration

### Perplexity AI (for news summaries)
1. Get API key from https://www.perplexity.ai/
2. Add to `.env`:
```env
PERPLEXITY_API_KEY=your_api_key_here
```

### Claude API (for news summaries fallback)
1. Get API key from https://console.anthropic.com
2. Add to `.env`:
```env
ANTHROPIC_API_KEY=your_api_key_here
```

---

## User Registration & Login

1. Go to `http://localhost:8000`
2. Click "Sign Up" to create account
3. Login with your credentials
4. Access dashboard at `/dashboard`

---

## Test Workflow

### 1. Create First Trade
- Go to Trades → New Trade
- Fill in:
  - Symbol: AAPL
  - Trade Type: Stock
  - Position: Long
  - Entry Price: 150.25
  - Quantity: 100
  - Capital Used: 15025
- Submit

### 2. Close the Trade
- Go to Trades → View Trade
- Click "Edit"
- Add Exit Price: 152.50
- Add Exit Date: (Today)
- Submit
- Verify P&L calculated automatically

### 3. Check Metrics
- Go to Metrics
- View today's P&L
- See win/loss stats

### 4. View News Summaries
- Go to News
- Filter by asset
- View daily summaries (generated at 8:00 AM)

---

## API Endpoints (with Authentication)

### Trades
```
GET    /api/trades                      # List trades
POST   /api/trades                      # Create trade
GET    /api/trades/{id}                 # Get single trade
PUT    /api/trades/{id}                 # Update trade
DELETE /api/trades/{id}                 # Delete trade
POST   /api/trades/{id}/comments        # Add comment
```

### Metrics
```
GET    /api/metrics/daily               # Daily metrics
GET    /api/metrics/consistency         # Streak data
GET    /api/metrics/summary             # Overall stats
```

### News
```
GET    /api/news/summaries              # List news
POST   /api/news/generate               # Generate summary
```

### Assets
```
GET    /api/assets                      # List monitored assets
POST   /api/assets                      # Add asset
DELETE /api/assets/{id}                 # Remove asset
```

---

## Scheduler Jobs

### Daily News Generation (8:00 AM)
Automatically generates news summaries for all monitored assets using Perplexity/Claude.

Command to run manually:
```bash
php artisan dispatch:job App\\Jobs\\GenerateDailyNewsSummaries
```

### Metrics Recalculation (11:00 PM)
Recalculates win rates, streaks, and consistency metrics for all users.

Command to run manually:
```bash
php artisan dispatch:job App\\Jobs\\RecalculateUserMetrics
```

---

## Database Schema

### Trades Table
- `id`, `user_id`, `asset_id`
- `symbol`, `trade_type`, `position_direction`
- `entry_price`, `entry_date`, `entry_time`, `entry_reason`
- `exit_price`, `exit_date`, `exit_time`, `exit_reason`
- `quantity`, `capital_used`, `stop_loss`, `take_profit`
- `p_l`, `p_l_percent`, `status`
- `emotional_state`, `mistakes_made`
- timestamps

### Assets Table
- `id`, `user_id`, `symbol`, `name`, `asset_type`
- `sector`, `is_active`
- timestamps

### Daily Metrics Table
- `id`, `user_id`, `date`
- `trades_count`, `wins`, `losses`, `win_rate`
- `daily_pnl`, `daily_pnl_percent`
- `best_trade`, `worst_trade`, `avg_risk_reward`
- timestamps

### Consistency Metrics Table
- `id`, `user_id`, `metric_type`
- `count`, `start_date`, `end_date`
- timestamps

---

## Features Implemented

✅ Multi-user trading journal
✅ Automatic P&L calculation
✅ Win rate & consistency tracking
✅ Daily news summaries (AI-powered)
✅ Sentiment analysis on news
✅ RESTful API with authentication
✅ Scheduled jobs with Laravel scheduler
✅ Responsive web interface
✅ User authentication & authorization
✅ Trade CRUD operations

---

## Next Steps

1. **Configure API Keys** for news generation
2. **Monitor Assets** for daily news summaries
3. **Log Trades** and track performance
4. **Review Metrics** daily
5. **Export Data** (coming soon)

---

## Troubleshooting

### Jobs not running?
Make sure to run the scheduler:
```bash
php artisan schedule:work
```

### News summaries empty?
Check API keys in `.env` and ensure you have active assets monitored.

### Can't login?
Make sure database migrations ran:
```bash
php artisan migrate
```

---

## Support

For issues or questions, check the Laravel/Fortify documentation:
- Laravel: https://laravel.com/docs
- Fortify: https://laravel.com/docs/fortify
