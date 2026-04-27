# 🚀 Production Deployment Guide

## Pre-Deployment Checklist

### Environment Setup
```bash
# Copy production environment
cp .env.example .env

# Generate app key (if not done)
php artisan key:generate

# Update critical environment variables
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database (use MySQL/PostgreSQL for production)
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=trading_journal
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@trading-journal.com

# Redis (for caching & sessions - optional but recommended)
REDIS_HOST=your-redis-host
REDIS_PORT=6379
REDIS_PASSWORD=your_redis_password

# API Keys
PERPLEXITY_API_KEY=your_perplexity_key
ANTHROPIC_API_KEY=your_anthropic_key
```

---

## Database Setup

### 1. Create Database
```bash
mysql -u root -p
CREATE DATABASE trading_journal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 2. Run Migrations
```bash
php artisan migrate --force
```

### 3. Seed Optional Demo Data
```bash
php artisan db:seed
```

---

## Build Frontend Assets

```bash
# Install dependencies
npm install --production

# Build for production
npm run build

# Verify output in public/build/
ls -la public/build/
```

---

## Optimization Commands

### Cache Configuration
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Clear Any Dev Caches
```bash
php artisan cache:clear
php artisan view:clear
```

---

## Server Configuration

### Nginx Example
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/trade/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Apache .htaccess
```
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect to HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    # Rewrite rules
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [QSA,L]
</IfModule>
```

---

## SSL Certificate

### Let's Encrypt (Free)
```bash
# Install Certbot
sudo apt-get install certbot python3-certbot-nginx

# Get certificate
sudo certbot certonly --nginx -d your-domain.com

# Auto-renewal
sudo systemctl enable certbot.timer
```

---

## Systemd Service Setup

Create `/etc/systemd/system/trading-journal-scheduler.service`:

```ini
[Unit]
Description=Trading Journal Scheduler
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/trade
ExecStart=/usr/bin/php /var/www/trade/artisan schedule:work
Restart=always

[Install]
WantedBy=multi-user.target
```

Enable:
```bash
sudo systemctl enable trading-journal-scheduler
sudo systemctl start trading-journal-scheduler
```

---

## Monitoring

### Setup Application Monitoring
```bash
# Install Horizon for queue monitoring (optional)
composer require laravel/horizon

php artisan horizon:install
```

### Log Rotation
Create `/etc/logrotate.d/trading-journal`:
```
/var/www/trade/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

---

## Backup Strategy

### Daily Database Backups
```bash
# Create backup script
#!/bin/bash
BACKUP_DIR="/backups/trading-journal"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")

mkdir -p $BACKUP_DIR

mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > \
  $BACKUP_DIR/trading_journal_$TIMESTAMP.sql.gz

# Keep only last 30 days
find $BACKUP_DIR -mtime +30 -delete
```

Add to crontab:
```bash
0 2 * * * /scripts/backup-trading-journal.sh
```

---

## Security Checklist

- [ ] Set `APP_DEBUG=false`
- [ ] Disable directory listing: `php artisan storage:link`
- [ ] Set proper file permissions:
  ```bash
  chmod -R 755 /var/www/trade
  chmod -R 775 /var/www/trade/storage
  chmod -R 775 /var/www/trade/bootstrap/cache
  ```
- [ ] Configure CORS if API is public
- [ ] Setup rate limiting in `config/rate-limit.php`
- [ ] Enable HTTPS redirect
- [ ] Setup firewall rules
- [ ] Regular security updates: `composer update`

---

## Performance Optimization

### Database Optimization
```bash
# Add indexes
php artisan tinker
# Inside tinker:
# Schema::table('trades', function ($table) {
#    $table->index('user_id');
#    $table->index('entry_date');
# });
```

### Query Optimization
- Eager load relations: `->with('asset', 'comments')`
- Use pagination for large datasets
- Cache expensive queries

### Caching Strategy
```php
// Cache metrics for 1 hour
Cache::remember('user_metrics_'.$userId, 3600, function () {
    return User::find($userId)->calculateMetrics();
});
```

---

## Monitoring Dashboard

Setup basic monitoring:
```bash
# Check storage
df -h /var/www/trade/storage

# Monitor logs
tail -f storage/logs/laravel.log

# Check scheduler
ps aux | grep "schedule:work"
```

---

## Uptime Monitoring

Use external service like:
- Uptime Robot (free)
- Pingdom
- New Relic
- Datadog

Setup health endpoint:
```php
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});
```

---

## Post-Deployment

### Verification
```bash
# Test API
curl -H "Accept: application/json" https://your-domain.com/api/user

# Test web
curl https://your-domain.com/dashboard

# Check logs
tail -100 storage/logs/laravel.log
```

### Load Testing
```bash
# Using Apache Bench
ab -n 1000 -c 10 https://your-domain.com/
```

---

## Scaling Tips

### High Traffic
1. Use Redis for caching & sessions
2. Enable query caching
3. Use CDN for assets
4. Setup load balancing

### Scheduled Jobs
- Move to separate job server
- Use worker processes with Supervisor
- Monitor with Horizon

---

## Troubleshooting

### Permission Issues
```bash
sudo chown -R www-data:www-data /var/www/trade
chmod -R 775 storage/ bootstrap/cache/
```

### Composer Issues
```bash
composer install --no-dev --optimize-autoloader
```

### Database Connection
```bash
php artisan tinker
# Test connection
DB::connection()->getPdo();
```

---

**Deploy with confidence!** 🚀
