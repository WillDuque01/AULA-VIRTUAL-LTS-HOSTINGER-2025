<?php

return [
    'enabled' => env('SECURITY_HEADERS_ENABLED', env('APP_ENV') !== 'local'),

    'csp' => [
        'enabled' => env('SECURITY_CSP_ENABLED', true),
        'value' => env('SECURITY_CSP', implode(' ', [
            "default-src 'self';",
            "base-uri 'self';",
            "form-action 'self';",
            "object-src 'none';",
            "img-src 'self' data: https://images.unsplash.com https://i.vimeocdn.com https://img.youtube.com;",
            "font-src 'self' https://fonts.bunny.net;",
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net;",
            "script-src 'self' 'unsafe-inline';",
            "frame-src 'self' https://player.vimeo.com https://www.youtube-nocookie.com https://iframe.mediadelivery.net https://stream.cloudflare.com https://*.cloudflarestream.com;",
            "connect-src 'self' https://fonts.bunny.net https://player.vimeo.com https://*.pusher.com wss://*.pusher.com https://stream.cloudflare.com https://*.cloudflarestream.com;",
            "media-src 'self' https://stream.cloudflare.com https://*.cloudflarestream.com;",
        ])),
    ],

    'hsts' => [
        'enabled' => env('SECURITY_HSTS_ENABLED', true),
        'max_age' => env('SECURITY_HSTS_MAX_AGE', 31536000),
        'include_subdomains' => env('SECURITY_HSTS_INCLUDE_SUBDOMAINS', true),
        'preload' => env('SECURITY_HSTS_PRELOAD', false),
    ],

    'frame_options' => env('SECURITY_FRAME_OPTIONS', 'SAMEORIGIN'),
    'referrer_policy' => env('SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
    'permissions_policy' => env('SECURITY_PERMISSIONS_POLICY', 'camera=(), microphone=(), geolocation=(), payment=()'),
];
