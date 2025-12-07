<?php

return [
    'title' => 'Provision integrations',
    'status' => [
        'forced' => 'Forced mode',
        'missing_credentials' => 'No credentials detected (using fallback).',
        'driver' => 'Driver',
    ],
    'sections' => [
        'make_discord' => [
            'sheets_toggle' => 'Enable Google Sheets',
            'thread_placeholder' => 'Optional: thread ID',
            'cert_hint' => 'Used to sign the `/api/certificates/verify` endpoint.',
            'deeplink_hint' => 'Use the deeplink if you only need a quick contact link. With token and phone ID you can enable automated alerts.',
        ],
        'whatsapp' => [
            'toggle' => 'Enable Cloud API',
        ],
        'free_modes' => [
            'storage' => 'Force local storage',
            'realtime' => 'Force local realtime',
            'youtube' => 'Force YouTube mode',
        ],
    ],
    'buttons' => [
        'rotate' => 'Rotate',
        'save' => 'Save',
    ],
    'notifications' => [
        'saved' => 'Saved',
        'error' => 'Error',
    ],
];

