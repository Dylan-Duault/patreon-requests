# Patreon Setup Guide

This guide walks you through setting up Patreon OAuth and webhooks for the video request platform.

## 1. Create a Patreon Developer Application

1. Go to the [Patreon Developer Portal](https://www.patreon.com/portal/registration/register-clients)
2. Click "Create Client"
3. Fill in the application details:
   - **App Name:** Your application name
   - **Description:** Brief description of your app
   - **App Category:** Select appropriate category
   - **Redirect URIs:** Add your callback URL(s):
     - Development: `http://localhost:8000/auth/patreon/callback`
     - Production: `https://yourdomain.com/auth/patreon/callback`

4. Save the application

## 2. Get Your Credentials

After creating the application, you'll receive:

- **Client ID** - Public identifier for your app
- **Client Secret** - Keep this secret! Never commit to version control

Add these to your `.env` file:

```env
PATREON_CLIENT_ID=your_client_id_here
PATREON_CLIENT_SECRET=your_client_secret_here
PATREON_REDIRECT_URI=http://localhost:8000/auth/patreon/callback
```

## 3. Get Your Campaign ID

Your Campaign ID is needed to verify memberships:

1. Go to your Patreon creator page
2. Look at the URL - it contains your campaign ID
3. Or use the Patreon API to fetch it:

```bash
curl -X GET \
  'https://www.patreon.com/api/oauth2/v2/campaigns' \
  -H 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

Add to your `.env`:

```env
PATREON_CAMPAIGN_ID=your_campaign_id
```

## 4. Configure Webhooks (Recommended)

Webhooks allow real-time updates when patrons change their pledges.

### Create Webhook

1. In the Patreon Developer Portal, go to your application
2. Navigate to the "Webhooks" section
3. Add a new webhook:
   - **URL:** `https://yourdomain.com/webhooks/patreon`
   - **Triggers:** Select the following:
     - `members:pledge:create`
     - `members:pledge:update`
     - `members:pledge:delete`

4. Copy the **Webhook Secret**

Add to your `.env`:

```env
PATREON_WEBHOOK_SECRET=your_webhook_secret
```

### Webhook Security

The application automatically verifies webhook signatures using the secret. Invalid signatures are rejected with a 401 response.

## 5. Required OAuth Scopes

The application requests the following scopes during authentication:

- `identity` - Basic user identity
- `identity[email]` - User's email address
- `campaigns` - Your campaign information
- `campaigns.members` - Your campaign's member information

These are configured in `PatreonController.php`:

```php
Socialite::driver('patreon')
    ->scopes(['identity', 'identity[email]', 'campaigns', 'campaigns.members'])
    ->redirect();
```

## 6. Testing the Integration

### Local Development

1. Ensure your `.env` has the correct `PATREON_REDIRECT_URI`:
   ```env
   PATREON_REDIRECT_URI=http://localhost:8000/auth/patreon/callback
   ```

2. Make sure this URL is registered in your Patreon app's redirect URIs

3. Start your development server:
   ```bash
   composer dev
   ```

4. Visit `http://localhost:8000` and click "Login with Patreon"

### Verify User Data

After successful login, check that the user was created correctly:

```bash
php artisan tinker
>>> User::latest()->first()->toArray()
```

You should see:
- `patreon_id` - User's Patreon ID
- `patron_status` - `active_patron`, `former_patron`, or `null`
- `patron_tier_cents` - Pledge amount in cents

## 7. Production Checklist

- [ ] Update `PATREON_REDIRECT_URI` to production URL
- [ ] Add production URL to Patreon app's redirect URIs
- [ ] Set up webhook with production URL
- [ ] Verify HTTPS is enabled (required for webhooks)
- [ ] Test login flow with a test Patreon account
- [ ] Configure scheduled task for membership refresh

## Troubleshooting

### "Invalid redirect_uri" Error

Ensure the redirect URI in your `.env` exactly matches one registered in your Patreon app.

### Membership Not Detected

1. Check that your Campaign ID is correct
2. Verify the user is actually a patron of your campaign
3. Run `php artisan patreon:refresh-memberships --user=USER_ID` to manually refresh

### Webhook Not Working

1. Ensure the webhook URL is accessible from the internet (not localhost)
2. Verify the webhook secret is correct
3. Check Laravel logs for webhook processing errors

### Token Expired

Tokens automatically refresh. If issues persist, have the user log out and back in.
