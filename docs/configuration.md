# Configuration Guide

## Environment Variables

### Required Variables

```env
# Application
APP_NAME="Video Requests"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=patreon_requests
DB_USERNAME=root
DB_PASSWORD=

# Patreon OAuth (Required)
PATREON_CLIENT_ID=your_client_id
PATREON_CLIENT_SECRET=your_client_secret
PATREON_REDIRECT_URI=http://localhost:8000/auth/patreon/callback
PATREON_CAMPAIGN_ID=your_campaign_id

# Patreon Subscribe URL (Required)
PATREON_SUBSCRIBE_URL=https://www.patreon.com/c/yourcreatorname

# Patreon Webhook (Optional but recommended)
PATREON_WEBHOOK_SECRET=your_webhook_secret
```

## Credit System Configuration

### Tier Configuration

Tiers are configured in `config/patreon.php`. Each tier maps a pledge amount (in cents) to the number of credits earned per month.

```php
'tiers' => [
    500 => 1,   // $5.00/month = 1 credit/month
    1000 => 2,  // $10.00/month = 2 credits/month
],
```

**Important:** Credits accumulate over time. They are NOT reset monthly. A user who earns 2 credits/month and doesn't use them will have 4 credits after 2 months.

### Custom Configuration

To add more tiers, simply add more entries:

```php
'tiers' => [
    300 => 1,   // $3.00/month = 1 credit
    500 => 2,   // $5.00/month = 2 credits
    1000 => 4,  // $10.00/month = 4 credits
    2500 => 10, // $25.00/month = 10 credits
],
```

### How Tier Matching Works

The system finds the highest tier the user qualifies for:

- User pledging $7.00 (700 cents) matches the $5.00 tier = 2 credits/month
- User pledging $15.00 (1500 cents) matches the $10.00 tier = 4 credits/month

### Default Credit Amount

For patrons whose tier isn't explicitly configured:

```php
'default_requests' => 0, // Set to 0 to require explicit tier configuration
```

### Request Cost Configuration

Video request costs are based on duration. Configure the base duration in `config/services.php`:

```php
'youtube' => [
    'max_duration_minutes' => 20,  // 1 credit per this many minutes
],
```

**Cost calculation:** `ceil(video_duration_minutes / max_duration_minutes)`

Examples with default 20-minute setting:
- 0-20 minutes = 1 credit
- 21-40 minutes = 2 credits
- 41-60 minutes = 3 credits
- 61-80 minutes = 4 credits

## Session Configuration

For production, ensure you configure session storage:

```env
SESSION_DRIVER=database  # or redis
SESSION_LIFETIME=120
```

## Queue Configuration

For processing webhooks and scheduled tasks:

```env
QUEUE_CONNECTION=database  # or redis
```

## Cache Configuration

```env
CACHE_DRIVER=database  # or redis
```

## Security Configuration

### CSRF Protection

The Patreon webhook endpoint is excluded from CSRF verification in `routes/web.php`:

```php
Route::post('/webhooks/patreon', PatreonWebhookController::class)
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
```

### Admin Access

Admin users can only be created via artisan command:

```bash
php artisan user:make-admin user@example.com
```

## Production Considerations

### HTTPS

Always use HTTPS in production. Update your `.env`:

```env
APP_URL=https://yourdomain.com
PATREON_REDIRECT_URI=https://yourdomain.com/auth/patreon/callback
```

### Database

Use MySQL or PostgreSQL in production:

```env
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-secure-password
```

### Caching

Enable config caching for better performance:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Scheduled Tasks

Set up the Laravel scheduler in your crontab:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```
