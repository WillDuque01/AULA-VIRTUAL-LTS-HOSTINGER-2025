<?php

return [
    'ga4' => [
        'enabled' => (bool) env('GA4_ENABLED', false),
        'measurement_id' => env('GA4_MEASUREMENT_ID'),
        'api_secret' => env('GA4_API_SECRET'),
        'endpoint' => env('GA4_ENDPOINT', 'https://www.google-analytics.com/mp/collect'),
    ],
    'mixpanel' => [
        'enabled' => (bool) env('MIXPANEL_ENABLED', false),
        'project_token' => env('MIXPANEL_PROJECT_TOKEN'),
        'api_secret' => env('MIXPANEL_API_SECRET'),
        'endpoint' => env('MIXPANEL_ENDPOINT', 'https://api.mixpanel.com/track'),
    ],
];

