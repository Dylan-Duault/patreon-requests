# Patreon Video Request Platform

A platform for Patreon subscribers to request YouTube videos for your reaction channel. Request limits are based on Patreon tier.

## Features

- **Patreon OAuth Authentication** - Login exclusively via Patreon
- **Tier-Based Limits** - Request limits based on subscription tier
- **Video Queue** - FIFO queue system for fair video ordering
- **Admin Panel** - Manage requests and mark videos as completed
- **Real-time Status** - Track your request status

## Requirements

- PHP 8.2+
- Node.js 20+
- MySQL 8.0+ or SQLite
- Composer
- Patreon Developer Account

## Installation

```bash
# Clone the repository
git clone <repo-url>
cd patreon-request-website

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Build frontend assets
npm run build
```

## Configuration

### Environment Variables

Add the following to your `.env` file:

```env
PATREON_CLIENT_ID=your_client_id
PATREON_CLIENT_SECRET=your_client_secret
PATREON_REDIRECT_URI=http://localhost:8000/auth/patreon/callback
PATREON_CAMPAIGN_ID=your_campaign_id
PATREON_WEBHOOK_SECRET=your_webhook_secret
```

### Tier Configuration

Configure your Patreon tiers in `config/patreon.php`:

```php
'tiers' => [
    // amount_in_cents => requests_per_month
    500 => 1,   // $5 tier gets 1 request/month
    1000 => 2,  // $10 tier gets 2 requests/month
],
```

## Patreon Setup

See [docs/patreon-setup.md](docs/patreon-setup.md) for detailed Patreon configuration instructions.

## Artisan Commands

| Command | Description |
|---------|-------------|
| `php artisan user:make-admin {email}` | Grant admin privileges to a user |
| `php artisan user:revoke-admin {email}` | Remove admin privileges |
| `php artisan patreon:refresh-memberships` | Sync all users' Patreon membership status |

## Routes

### Public
- `GET /` - Landing page
- `GET /auth/patreon` - Initiate Patreon login
- `GET /auth/patreon/callback` - OAuth callback

### Authenticated (all users)
- `GET /dashboard` - User dashboard
- `GET /subscribe` - Subscribe prompt (for non-patrons)
- `POST /logout` - Logout

### Patrons only
- `GET /requests` - View pending video queue
- `GET /requests/new` - Submit new request form
- `POST /requests` - Submit request
- `GET /my-requests` - View own requests

### Admin only
- `GET /admin/requests` - View all requests
- `PATCH /admin/requests/{id}/done` - Mark request as completed
- `PATCH /admin/requests/{id}/pending` - Revert to pending

## Scheduled Tasks

Add to your server's crontab:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

The following tasks run automatically:
- **Daily:** Refresh Patreon memberships for all users

## Development

```bash
# Start development server
composer dev

# Or run services separately
php artisan serve
npm run dev
```

## Architecture

```
app/
├── Console/Commands/       # Artisan commands
├── Http/
│   ├── Controllers/
│   │   ├── Auth/           # Patreon OAuth controllers
│   │   ├── Admin/          # Admin controllers
│   │   └── ...             # Other controllers
│   └── Middleware/         # Custom middleware
├── Models/                 # Eloquent models
└── Services/               # Business logic services

resources/js/
└── pages/                  # Inertia/Vue pages
    ├── auth/               # Authentication pages
    ├── admin/              # Admin pages
    └── ...                 # User pages
```

## Business Rules

- **Authentication:** Patreon OAuth only
- **Tier 1 ($5):** 1 request per month
- **Tier 2 ($10):** 2 requests per month
- **Non-subscribers:** Can login but see "subscribe to request" page
- **Requests:** YouTube links only, pending/done status
- **Queue:** Videos watched in chronological order (FIFO)
- **Monthly Reset:** Request limits reset on the 1st of each month

## License

MIT
