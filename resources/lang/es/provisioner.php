<?php

return [
    'title' => 'Provisionar integraciones',
    'status' => [
        'forced' => 'Modo forzado',
        'missing_credentials' => 'Sin credenciales detectadas (usando fallback).',
        'driver' => 'Driver',
    ],
    'sections' => [
        'make_discord' => [
            'sheets_toggle' => 'Habilitar Google Sheets',
            'thread_placeholder' => 'Opcional: ID de hilo',
            'cert_hint' => 'Se usa para firmar el endpoint `/api/certificates/verify`.',
            'deeplink_hint' => 'Usa el deeplink si solo necesitas compartir un enlace rápido. Con token y phone ID se habilitan alertas automáticas.',
        ],
        'whatsapp' => [
            'toggle' => 'Activar Cloud API',
        ],
        'free_modes' => [
            'storage' => 'Forzar almacenamiento local',
            'realtime' => 'Forzar realtime local',
            'youtube' => 'Forzar modo YouTube',
        ],
    ],
    'buttons' => [
        'rotate' => 'Rotar',
        'save' => 'Guardar',
    ],
    'notifications' => [
        'saved' => 'Guardado',
        'error' => 'Error',
    ],
];

