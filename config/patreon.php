<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Patreon Subscribe URL
    |--------------------------------------------------------------------------
    |
    | The URL to your Patreon page where users can subscribe.
    | Example: https://www.patreon.com/c/yourcreatorname
    |
    */

    'subscribe_url' => env('PATREON_SUBSCRIBE_URL', 'https://www.patreon.com'),

    /*
    |--------------------------------------------------------------------------
    | Patreon Campaign ID
    |--------------------------------------------------------------------------
    |
    | Your Patreon campaign ID. This is required for fetching membership data.
    | Find this in your Patreon creator settings or API responses.
    |
    */

    'campaign_id' => env('PATREON_CAMPAIGN_ID'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Secret
    |--------------------------------------------------------------------------
    |
    | The secret used to verify webhook signatures from Patreon.
    | Set this up in your Patreon developer portal.
    |
    */

    'webhook_secret' => env('PATREON_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Tier Configuration
    |--------------------------------------------------------------------------
    |
    | Map pledge amounts (in cents) to the number of video requests per month.
    | Configure these values to match your actual Patreon tier prices.
    |
    | Example:
    |   500 => 1,   // $5.00 tier gets 1 request/month
    |   1000 => 2,  // $10.00 tier gets 2 requests/month
    |
    */

    'tiers' => [
        // amount_in_cents => requests_per_month
        100 => 1,
        300 => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Request Limit
    |--------------------------------------------------------------------------
    |
    | Default number of requests for patrons whose tier isn't explicitly
    | configured above. Set to 0 to require explicit tier configuration.
    |
    */

    'default_requests' => 0,

];
