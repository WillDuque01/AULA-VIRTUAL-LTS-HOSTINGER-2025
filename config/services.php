<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Complemento de integraciones
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        'service_account_json' => env('GOOGLE_SERVICE_ACCOUNT_JSON_PATH', 'storage/app/keys/google.json'),
        'sheet_id' => env('SHEET_ID'),
        'enabled' => (bool) env('GOOGLE_SHEETS_ENABLED', false),
        'range' => env('GOOGLE_SHEETS_RANGE', 'Integraciones!A1'),
    ],

    'pusher' => [
        'app_id' => env('PUSHER_APP_ID'),
        'key' => env('PUSHER_APP_KEY'),
        'secret' => env('PUSHER_APP_SECRET'),
        'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
    ],

    's3' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'bucket' => env('AWS_BUCKET'),
        'endpoint' => env('AWS_ENDPOINT'),
        'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', true),
        'region' => env('AWS_DEFAULT_REGION', 'auto'),
    ],

    'vimeo' => [
        'token' => env('VIMEO_TOKEN'),
    ],

    'cf' => [
        'token' => env('CLOUDFLARE_STREAM_TOKEN'),
        'account_id' => env('CLOUDFLARE_ACCOUNT_ID'),
    ],

    'make' => [
        'webhook_url' => env('MAKE_WEBHOOK_URL'),
        'secret' => env('WEBHOOKS_MAKE_SECRET'),
    ],

    'discord' => [
        'webhook_url' => env('DISCORD_WEBHOOK_URL'),
    ],

    'mailerlite' => [
        'api_key' => env('MAILERLITE_API_KEY'),
        'group_id' => env('MAILERLITE_GROUP_ID'),
    ],

    'certificates' => [
        'verify_secret' => env('CERTIFICATES_VERIFY_SECRET'),
    ],

    'whatsapp' => [
        'enabled' => (bool) env('WHATSAPP_ENABLED', false),
        'token' => env('WHATSAPP_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_ID'),
        'default_to' => env('WHATSAPP_DEFAULT_TO'),
        'deeplink' => env('WHATSAPP_DEEPLINK'),
    ],

];
