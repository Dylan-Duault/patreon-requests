# Patreon Video Request Platform

A platform for Patreon subscribers to request YouTube videos for your reaction channel. Credits accumulate based on Patreon tier, and video requests cost credits based on duration.

## Features

- **Patreon OAuth Authentication** - Login exclusively via Patreon
- **Credit-Based System** - Credits accumulate monthly based on subscription tier
- **Duration-Based Costs** - Longer videos cost more credits (1 credit per 20 minutes)
- **Video Queue** - FIFO queue system for fair video ordering
- **Admin Panel** - Manage requests and mark videos as completed
- **Real-time Status** - Track your request and credit status

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
    // amount_in_cents => credits_per_month
    500 => 1,   // $5 tier earns 1 credit/month
    1000 => 2,  // $10 tier earns 2 credits/month
],
```

Credits accumulate over time as long as the user remains subscribed. They are not reset monthly.

## Patreon Setup

See [docs/patreon-setup.md](docs/patreon-setup.md) for detailed Patreon configuration instructions.

## Artisan Commands

| Command | Description |
|---------|-------------|
| `php artisan user:make-admin {email}` | Grant admin privileges to a user |
| `php artisan user:revoke-admin {email}` | Remove admin privileges |
| `php artisan patreon:refresh-memberships` | Sync all users' Patreon membership status |
| `php artisan credits:grant-monthly` | Grant monthly credits to active patrons |

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
- **Monthly (1st):** Grant monthly credits to active patrons

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
- **Credit System:** Credits accumulate monthly based on tier (not reset)
  - Tier 1 ($5): Earns 1 credit/month
  - Tier 2 ($10): Earns 2 credits/month
- **Request Costs:** Based on video duration (1 credit per 20 minutes)
  - 0-20 min = 1 credit
  - 21-40 min = 2 credits
  - 41-60 min = 3 credits, etc.
- **Non-subscribers:** Can login but see "subscribe to request" page
- **Requests:** YouTube links only, pending/done status
- **Queue:** Videos watched in chronological order (FIFO)

## License

MIT
