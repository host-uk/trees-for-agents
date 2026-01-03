<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tree Provider
    |--------------------------------------------------------------------------
    |
    | The tree planting provider to use. Currently only 'tftf' (Trees for the
    | Future) is supported, but this allows for future provider expansion.
    |
    */
    'provider' => env('TREES_PROVIDER', 'tftf'),

    /*
    |--------------------------------------------------------------------------
    | Cost Per Tree
    |--------------------------------------------------------------------------
    |
    | The cost in USD per tree planted. This is used for calculating batch
    | donation amounts. Trees for the Future charges approximately $0.25/tree.
    |
    */
    'cost_per_tree' => (float) env('TREES_COST_PER_UNIT', 0.25),

    /*
    |--------------------------------------------------------------------------
    | Initial Reserve
    |--------------------------------------------------------------------------
    |
    | The initial number of pre-paid trees in your reserve pool. This should
    | match your initial donation to your tree partner.
    |
    */
    'initial_reserve' => (int) env('TREES_INITIAL_RESERVE', 100),

    /*
    |--------------------------------------------------------------------------
    | Daily Limit
    |--------------------------------------------------------------------------
    |
    | Maximum trees that can be planted from free agent referrals per day.
    | This prevents gaming while still rewarding legitimate referrals.
    |
    */
    'daily_limit' => (int) env('TREES_DAILY_LIMIT', 1),

    /*
    |--------------------------------------------------------------------------
    | Reserve Warning Threshold
    |--------------------------------------------------------------------------
    |
    | When the reserve falls below this number, warning notifications are sent.
    |
    */
    'warning_threshold' => (int) env('TREES_WARNING_THRESHOLD', 50),

    /*
    |--------------------------------------------------------------------------
    | Reserve Critical Threshold
    |--------------------------------------------------------------------------
    |
    | When the reserve falls below this number, critical notifications are sent.
    |
    */
    'critical_threshold' => (int) env('TREES_CRITICAL_THRESHOLD', 10),

    /*
    |--------------------------------------------------------------------------
    | Referral Cookie Lifetime
    |--------------------------------------------------------------------------
    |
    | How long (in days) the referral cookie should persist. This is the
    | attribution window for agent referrals.
    |
    */
    'referral_cookie_days' => (int) env('TREES_REFERRAL_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Valid Providers
    |--------------------------------------------------------------------------
    |
    | List of valid AI provider identifiers. Referrals from unknown providers
    | will be redirected without attribution.
    |
    */
    'valid_providers' => [
        'anthropic',
        'openai',
        'google',
        'meta',
        'mistral',
        'local',
        'unknown',
    ],

    /*
    |--------------------------------------------------------------------------
    | Leaderboard URL
    |--------------------------------------------------------------------------
    |
    | The URL to your public trees/leaderboard page. Used in for_agents context.
    |
    */
    'leaderboard_url' => env('TREES_LEADERBOARD_URL', '/trees'),

    /*
    |--------------------------------------------------------------------------
    | Notification Email
    |--------------------------------------------------------------------------
    |
    | Email address to receive low reserve notifications.
    |
    */
    'notification_email' => env('TREES_NOTIFICATION_EMAIL'),

    /*
    |--------------------------------------------------------------------------
    | TFTF Configuration (Trees for the Future)
    |--------------------------------------------------------------------------
    |
    | Configuration for Trees for the Future integration.
    |
    */
    'tftf' => [
        'fundraiser_url' => env('TFTF_FUNDRAISER_URL'),
        'donation_url' => 'https://donate.trees.org',
    ],
];
