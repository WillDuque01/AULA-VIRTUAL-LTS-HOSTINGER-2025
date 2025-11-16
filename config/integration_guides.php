<?php

$appUrl = env('APP_URL', 'https://academy.test');

return [
    'categories' => [
        [
            'key' => 'video',
            'title' => 'Video y streaming',
            'description' => 'Controla qué proveedor se usa para reproducir contenidos. YouTube funciona como fallback gratuito; Vimeo y Cloudflare Stream añaden DRM liviano y analytics adicionales.',
            'providers' => [
                [
                    'slug' => 'youtube',
                    'name' => 'YouTube nocookie (fallback)',
                    'summary' => 'Solo necesitamos declarar el dominio autorizado para que la IFrame API reconozca el LMS.',
                    'docs' => 'https://developers.google.com/youtube/v3/docs',
                    'tokens' => [
                        ['label' => 'YOUTUBE_ORIGIN', 'hint' => 'Dominio permitido en la IFrame API'],
                    ],
                    'fields' => [
                        [
                            'label' => 'YouTube origin',
                            'binding' => 'integrations.video.YOUTUBE_ORIGIN',
                            'type' => 'text',
                            'placeholder' => $appUrl,
                            'hint' => 'URL completa (https://...) que coincida con el dominio público del LMS.',
                        ],
                    ],
                    'steps' => [
                        'Abre https://console.cloud.google.com/apis/dashboard y selecciona el proyecto que usa la API de YouTube.',
                        'Ve a “Credenciales › Pantalla de consentimiento” y comprueba que el dominio esté verificado.',
                        'En la sección “Restricciones de API”, añade el dominio del LMS dentro de los Orígenes autorizados.',
                        'Guarda y pega el dominio en el campo YOUTUBE_ORIGIN.',
                    ],
                    'validation' => [
                        [
                            'label' => 'Smoke test',
                            'description' => 'Abre una lección de video y revisa en DevTools que el iframe cargue desde https://www.youtube-nocookie.com. Si ves un error 401, el dominio no está autorizado.',
                        ],
                    ],
                    'troubleshooting' => [
                        'Si YouTube muestra “Playback ID” u “Origen no permitido”, verifica que el dominio incluya https:// y no termine con slash.',
                        'Cuando migras de staging a producción recuerda actualizar el dominio en Google Cloud.',
                    ],
                    'playbook' => [
                        'audiences' => ['admin'],
                        'env' => ['YOUTUBE_ORIGIN'],
                        'status_hint' => 'Define el dominio HTTPS público exactamente como se sirve el LMS.',
                        'next_steps' => [
                            'Ejecuta un video en /lessons/player y confirma que no aparezcan errores de CORS.',
                        ],
                    ],
                ],
                [
                    'slug' => 'vimeo',
                    'name' => 'Vimeo (token personal)',
                    'summary' => 'Permite activar el modo estricto con privacidad “Only embed on allowed domains”.',
                    'docs' => 'https://developer.vimeo.com/api/guides/start',
                    'tokens' => [
                        ['label' => 'VIMEO_TOKEN', 'hint' => 'Token personal con scope upload+video_files'],
                    ],
                    'fields' => [
                        [
                            'label' => 'Vimeo token',
                            'binding' => 'integrations.video.VIMEO_TOKEN',
                            'type' => 'password',
                            'placeholder' => 'vimeo_pat_xxx',
                            'hint' => 'Desde https://developer.vimeo.com/apps crea un access token con permisos video_files.',
                        ],
                    ],
                    'steps' => [
                        'Ingresa a https://developer.vimeo.com/apps y crea una app nueva.',
                        'Dentro de la app selecciona “Generate access token”, marca los scopes video_files y private.',
                        'En Vimeo › Settings › Upload defaults agrega el dominio del LMS en “Where can this be embedded?”.',
                        'Copia el token en el campo correspondiente y guarda.',
                    ],
                    'validation' => [
                        [
                            'label' => 'API /me',
                            'command' => 'curl -H "Authorization: bearer <TOKEN>" https://api.vimeo.com/me',
                            'description' => 'Debe responder 200 y mostrar tu cuenta. Si responde 401 revisa los scopes.',
                        ],
                    ],
                    'troubleshooting' => [
                        'Error “403 - Domain not allowed”: entra a cada video › Privacy › Where can this be embedded? y añade el dominio.',
                        'Si el token expira, vuelve a generarlo; los personales no se renuevan automáticamente.',
                    ],
                    'playbook' => [
                        'audiences' => ['admin'],
                        'env' => ['VIMEO_TOKEN'],
                        'status_hint' => 'Token personal con scopes video_files + private.',
                        'next_steps' => [
                            'Prueba un embed Vimeo en el player y valida que el botón “Modo estricto” funcione.',
                        ],
                    ],
                ],
                [
                    'slug' => 'cloudflare',
                    'name' => 'Cloudflare Stream',
                    'summary' => 'Streaming HLS con token rotatorio y analítica granular.',
                    'docs' => 'https://developers.cloudflare.com/stream',
                    'tokens' => [
                        ['label' => 'CLOUDFLARE_ACCOUNT_ID', 'hint' => 'ID del dashboard (Overview > API)'],
                        ['label' => 'CLOUDFLARE_STREAM_TOKEN', 'hint' => 'API token con permiso Stream:Edit'],
                    ],
                    'fields' => [
                        [
                            'label' => 'Stream token',
                            'binding' => 'integrations.video.CLOUDFLARE_STREAM_TOKEN',
                            'type' => 'password',
                            'placeholder' => 'cfpat_xxxxx',
                            'hint' => 'Dashboard Cloudflare › My profile › API tokens › Create custom token (permiso Stream:Edit).',
                        ],
                        [
                            'label' => 'Cloudflare Account ID',
                            'binding' => 'integrations.video.CLOUDFLARE_ACCOUNT_ID',
                            'type' => 'text',
                            'placeholder' => 'xxxxxxxxxxxxxxxxxxxx',
                            'hint' => 'Disponible en la parte superior derecha del dashboard o en Overview › API.',
                        ],
                    ],
                    'steps' => [
                        'Crea un token personalizado con permisos: Account.Stream:Edit y Zone.Cache Purge (opcional).',
                        'Ve a Stream › Settings y agrega el dominio del LMS en “Allowed origins / Signed URLs”.',
                        'Pega el Account ID y el token en el asistente y guarda.',
                    ],
                    'validation' => [
                        [
                            'label' => 'Listar videos',
                            'command' => 'curl -H "Authorization: Bearer <TOKEN>" https://api.cloudflare.com/client/v4/accounts/<ACCOUNT_ID>/stream/live_inputs',
                            'description' => 'Debe regresar 200 con la lista de assets.',
                        ],
                    ],
                    'troubleshooting' => [
                        'Si ves 403 revisa que el token incluya el permiso Account.Stream:Edit.',
                        'Para reproducción privada habilita Signed URLs y registra el dominio en Allowed Origins.',
                    ],
                    'playbook' => [
                        'audiences' => ['admin'],
                        'env' => ['CLOUDFLARE_ACCOUNT_ID', 'CLOUDFLARE_STREAM_TOKEN'],
                        'status_hint' => 'Necesitas Account ID + token API con permiso Stream:Edit.',
                        'next_steps' => [
                            'Sube un video y valida la reproducción en /lessons/player (proveedor cloudflare).',
                        ],
                    ],
                ],
            ],
        ],
        [
            'key' => 'storage',
            'title' => 'Storage & Realtime',
            'description' => 'Define dónde se almacenan archivos grandes y qué servicio manejará los eventos en vivo.',
            'providers' => [
                [
                    'slug' => 's3',
                    'name' => 'S3 / R2 / Wasabi',
                    'summary' => 'Compatibles con el driver S3 de Laravel. Las credenciales funcionan igual para AWS o proveedores S3-compatibles.',
                    'docs' => 'https://laravel.com/docs/filesystem#s3-driver',
                    'tokens' => [
                        ['label' => 'AWS_ACCESS_KEY_ID', 'hint' => 'Usuario con permisos PutObject/GetObject'],
                        ['label' => 'AWS_SECRET_ACCESS_KEY', 'hint' => 'Se genera al crear el usuario'],
                        ['label' => 'AWS_BUCKET', 'hint' => 'Nombre exacto del bucket/R2'],
                        ['label' => 'AWS_ENDPOINT', 'hint' => 'Requerido para R2/Wasabi (https://<account>.r2.cloudflarestorage.com)'],
                    ],
                    'fields' => [
                        [
                            'label' => 'Access key',
                            'binding' => 'integrations.storage.AWS_ACCESS_KEY_ID',
                            'type' => 'text',
                            'placeholder' => 'AKIA...',
                        ],
                        [
                            'label' => 'Secret key',
                            'binding' => 'integrations.storage.AWS_SECRET_ACCESS_KEY',
                            'type' => 'password',
                            'placeholder' => '********',
                        ],
                        [
                            'label' => 'Bucket',
                            'binding' => 'integrations.storage.AWS_BUCKET',
                            'type' => 'text',
                            'placeholder' => 'lts-academy',
                        ],
                        [
                            'label' => 'Endpoint (solo R2/Wasabi)',
                            'binding' => 'integrations.storage.AWS_ENDPOINT',
                            'type' => 'text',
                            'placeholder' => 'https://xxxx.r2.cloudflarestorage.com',
                        ],
                        [
                            'label' => 'Región',
                            'binding' => 'integrations.storage.AWS_DEFAULT_REGION',
                            'type' => 'text',
                            'placeholder' => 'auto',
                        ],
                        [
                            'label' => 'Usar path style',
                            'binding' => 'integrations.storage.AWS_USE_PATH_STYLE_ENDPOINT',
                            'type' => 'toggle',
                            'hint' => 'Déjalo activado para R2/Wasabi. En AWS clásico puedes desactivarlo.',
                        ],
                    ],
                    'steps' => [
                        'Crea un usuario IAM (o R2 API Token) con permisos PutObject/GetObject/ListBucket.',
                        'Si usas Cloudflare R2, obtén el endpoint desde R2 › Settings › S3 API.',
                        'Agrega el dominio del LMS en las reglas CORS del bucket (GET, PUT, HEAD).',
                    ],
                    'validation' => [
                        [
                            'label' => 'Listar bucket',
                            'command' => 'aws s3 ls s3://<BUCKET> --endpoint-url=<ENDPOINT>',
                            'description' => 'Debe listar objetos sin error. Ajusta el endpoint para R2/Wasabi.',
                        ],
                    ],
                    'troubleshooting' => [
                        'Error “SignatureDoesNotMatch”: revisa la región y si usas path style.',
                        'Para R2/Wasabi siempre activa el modo path style y define el endpoint HTTPS.',
                    ],
                    'playbook' => [
                        'audiences' => ['admin'],
                        'env' => ['AWS_ACCESS_KEY_ID', 'AWS_SECRET_ACCESS_KEY', 'AWS_BUCKET'],
                        'status_hint' => 'Usuario S3 con permisos Put/Get/List y bucket asignado.',
                        'next_steps' => [
                            'Ejecuta `php artisan storage:link` y prueba una carga desde Admin › Branding.',
                        ],
                    ],
                ],
                [
                    'slug' => 'pusher',
                    'name' => 'Pusher / Ably (Echo)',
                    'summary' => 'Los eventos Livewire (toasts, progreso, planner) dependen de un cluster en tiempo real.',
                    'docs' => 'https://pusher.com/docs/channels',
                    'tokens' => [
                        ['label' => 'PUSHER_APP_ID', 'hint' => 'ID del proyecto'],
                        ['label' => 'PUSHER_APP_KEY', 'hint' => 'Key pública'],
                        ['label' => 'PUSHER_APP_SECRET', 'hint' => 'Secret privado'],
                        ['label' => 'PUSHER_APP_CLUSTER', 'hint' => 'eu, mt1, etc.'],
                    ],
                    'fields' => [
                        ['label' => 'App ID', 'binding' => 'integrations.storage.PUSHER_APP_ID', 'type' => 'text'],
                        ['label' => 'Key', 'binding' => 'integrations.storage.PUSHER_APP_KEY', 'type' => 'text'],
                        ['label' => 'Secret', 'binding' => 'integrations.storage.PUSHER_APP_SECRET', 'type' => 'password'],
                        ['label' => 'Cluster', 'binding' => 'integrations.storage.PUSHER_APP_CLUSTER', 'type' => 'text', 'placeholder' => 'mt1'],
                    ],
                    'steps' => [
                        'Entra a https://dashboard.pusher.com/ y crea una app Channels.',
                        'Activa TLS y habilita los sub-dominios necesarios (app/lms).',
                        'Copia App ID, Key, Secret y Cluster.',
                        'Si usas Ably, toma el API Key y habilita el modo Pusher compatible.',
                    ],
                    'validation' => [
                        [
                            'label' => 'Handshake',
                            'description' => 'Abre el builder o el planner y revisa en DevTools → Network que el websocket `pusher` se conecte en wss://.',
                        ],
                    ],
                    'troubleshooting' => [
                        'Si ves 401 al conectar, verifica que APP_KEY coincida con el cluster configurado.',
                        'Activa “Force TLS” en Channels para evitar bloqueos mixed-content.',
                    ],
                    'playbook' => [
                        'audiences' => ['admin'],
                        'env' => ['PUSHER_APP_ID', 'PUSHER_APP_KEY', 'PUSHER_APP_SECRET', 'PUSHER_APP_CLUSTER'],
                        'status_hint' => 'El LMS usa Laravel Echo; sin estas llaves cae a polling.',
                        'next_steps' => [
                            'Abre dos pestañas del builder y confirma que las notificaciones llegan en tiempo real.',
                        ],
                    ],
                ],
                [
                    'slug' => 'local-modes',
                    'name' => 'Modos locales / sandbox',
                    'summary' => 'Útiles para ambientes de desarrollo donde no hay credenciales todavía.',
                    'tokens' => [
                        ['label' => 'FORCE_FREE_STORAGE', 'hint' => 'Usa storage local/public'],
                        ['label' => 'FORCE_FREE_REALTIME', 'hint' => 'Desactiva Pusher y usa polling'],
                        ['label' => 'FORCE_YOUTUBE_ONLY', 'hint' => 'Ignora Vimeo/CF incluso si hay claves'],
                    ],
                    'fields' => [
                        [
                            'label' => 'Forzar almacenamiento local',
                            'binding' => 'integrations.storage.FORCE_FREE_STORAGE',
                            'type' => 'toggle',
                        ],
                        [
                            'label' => 'Sin Pusher (modo polling)',
                            'binding' => 'integrations.storage.FORCE_FREE_REALTIME',
                            'type' => 'toggle',
                        ],
                        [
                            'label' => 'Solo YouTube',
                            'binding' => 'integrations.storage.FORCE_YOUTUBE_ONLY',
                            'type' => 'toggle',
                        ],
                    ],
                    'steps' => [
                        'Activa estas casillas solo en entornos locales. En producción deben permanecer desactivadas.',
                        'Cuando ingreses las credenciales reales, desmarca las casillas y vuelve a guardar.',
                    ],
                    'playbook' => [
                        'audiences' => ['admin'],
                        'env' => [],
                        'status_hint' => 'Solo úsalo en local. En producción debe quedar desactivado.',
                        'next_steps' => [
                            'Si notas que las notificaciones se envían dos veces, confirma que FORCEx derivados están en false.',
                        ],
                    ],
                ],
            ],
        ],
        [
            'key' => 'mail',
            'title' => 'Correo y notificaciones',
            'description' => 'SMTP hostinger (por defecto) o el proveedor que prefieras. El wizard acepta cualquier host compatible.',
            'providers' => [
                [
                    'slug' => 'smtp',
                    'name' => 'SMTP (Hostinger u otro)',
                    'summary' => 'El LMS envía onboarding, prácticas y recordatorios desde esta cuenta.',
                    'docs' => 'https://support.hostinger.com/en/articles/1583247-how-to-set-up-email-on-laravel',
                    'tokens' => [
                        ['label' => 'MAIL_HOST', 'hint' => 'smtp.hostinger.com'],
                        ['label' => 'MAIL_USERNAME', 'hint' => 'Cuenta completa: academy@...'],
                        ['label' => 'MAIL_PASSWORD', 'hint' => 'Contraseña o App Password'],
                    ],
                    'fields' => [
                        ['label' => 'Mailer', 'binding' => 'integrations.mail.MAIL_MAILER', 'type' => 'text', 'placeholder' => 'smtp'],
                        ['label' => 'Host', 'binding' => 'integrations.mail.MAIL_HOST', 'type' => 'text', 'placeholder' => 'smtp.hostinger.com'],
                        ['label' => 'Puerto', 'binding' => 'integrations.mail.MAIL_PORT', 'type' => 'number', 'placeholder' => '465'],
                        ['label' => 'Usuario', 'binding' => 'integrations.mail.MAIL_USERNAME', 'type' => 'text'],
                        ['label' => 'Contraseña', 'binding' => 'integrations.mail.MAIL_PASSWORD', 'type' => 'password'],
                        ['label' => 'Encriptación', 'binding' => 'integrations.mail.MAIL_ENCRYPTION', 'type' => 'text', 'placeholder' => 'ssl'],
                        ['label' => 'Correo remitente', 'binding' => 'integrations.mail.MAIL_FROM_ADDRESS', 'type' => 'email'],
                        ['label' => 'Nombre remitente', 'binding' => 'integrations.mail.MAIL_FROM_NAME', 'type' => 'text', 'placeholder' => 'LTS Aula Virtual'],
                    ],
                    'steps' => [
                        'Hostinger: hPanel › Emails › Configuración > copia host, puerto y usuario.',
                        'Activa 2FA y genera una App Password si usas Gmail/Outlook.',
                        'Haz un “Test email” desde el panel Admin › Integraciones para validar TLS.',
                    ],
                    'validation' => [
                        [
                            'label' => 'Smoke test',
                            'description' => 'Desde Admin › Integraciones utiliza el botón “Enviar correo de prueba” y confirma que llegue en menos de un minuto.',
                        ],
                    ],
                    'troubleshooting' => [
                        'Error 535 = credenciales incorrectas o App Password faltante.',
                        'Para puertos 465/587 habilita STARTTLS/SSL según lo requiera el proveedor.',
                    ],
                    'playbook' => [
                        'audiences' => ['admin'],
                        'env' => ['MAIL_MAILER', 'MAIL_HOST', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_FROM_ADDRESS'],
                        'status_hint' => 'SMTP responde con TLS y remitente configurado.',
                        'next_steps' => [
                            'Usa Admin › Notificaciones para enviar un correo de prueba después de cada cambio.',
                        ],
                    ],
                ],
            ],
        ],
        [
            'key' => 'marketing',
            'title' => 'Marketing & telemetría',
            'description' => 'Mide funnels y protege formularios.',
            'providers' => [
                [
                    'slug' => 'ga4',
                    'name' => 'Google Analytics 4 (Measurement Protocol)',
                    'summary' => 'Sincroniza eventos del player y DataPorter con GA4.',
                    'docs' => 'https://developers.google.com/analytics/devguides/collection/protocol/ga4',
                    'tokens' => [
                        ['label' => 'GA4_MEASUREMENT_ID', 'hint' => 'Formato G-XXXX'],
                        ['label' => 'GA4_API_SECRET', 'hint' => 'Secret generado en Admin › Data streams › Measurement protocol'],
                        ['label' => 'GA4_ENABLED', 'hint' => 'Activa/desactiva el driver desde el LMS'],
                    ],
                    'fields' => [
                        [
                            'label' => 'Habilitar GA4',
                            'binding' => 'integrations.marketing.GA4_ENABLED',
                            'type' => 'toggle',
                        ],
                        [
                            'label' => 'Measurement ID',
                            'binding' => 'integrations.marketing.GA4_MEASUREMENT_ID',
                            'type' => 'text',
                            'placeholder' => 'G-XXXXXXX',
                        ],
                        [
                            'label' => 'API Secret',
                            'binding' => 'integrations.marketing.GA4_API_SECRET',
                            'type' => 'password',
                        ],
                    ],
                    'steps' => [
                        'GA4 Admin › Data Streams › Web: copia el Measurement ID (G-XXXX).',
                        'En el mismo stream ve a “Measurement Protocol API secrets” y genera un secret nuevo.',
                        'Si quieres separar tráfico productivo vs staging, crea dos data streams y habilita según entorno.',
                    ],
                    'validation' => [
                        [
                            'label' => 'telemetry:sync',
                            'command' => 'php artisan telemetry:sync --limit=1',
                            'description' => 'Debe enviar los eventos pendientes y marcarlos como synced. Consulta GA4 › DebugView.',
                        ],
                    ],
                    'troubleshooting' => [
                        'Si DebugView no muestra eventos, verifica que la zona horaria del stream coincida.',
                        'Los secrets se revocan al regenerarlos; actualiza el .env tras cada cambio.',
                    ],
                    'playbook' => [
                        'audiences' => ['admin'],
                        'env' => ['GA4_MEASUREMENT_ID', 'GA4_API_SECRET'],
                        'status_hint' => 'Measurement ID (G-XXXX) y API Secret activos.',
                        'next_steps' => [
                            'Revisa Admin › DataPorter para asegurarte de que no haya eventos pendientes.',
                        ],
                    ],
                ],
                [
                    'slug' => 'mixpanel',
                    'name' => 'Mixpanel (opcional)',
                    'summary' => 'Driver adicional para funnels de práctica, builder y player.',
                    'docs' => 'https://developer.mixpanel.com/reference/ingestion',
                    'tokens' => [
                        ['label' => 'MIXPANEL_PROJECT_TOKEN', 'hint' => 'Project token (Project settings)'],
                        ['label' => 'MIXPANEL_API_SECRET', 'hint' => 'Service account / API secret'],
                        ['label' => 'MIXPANEL_ENABLED', 'hint' => 'Bandera para habilitar el driver'],
                    ],
                    'fields' => [
                        [
                            'label' => 'Habilitar Mixpanel',
                            'binding' => 'integrations.marketing.MIXPANEL_ENABLED',
                            'type' => 'toggle',
                        ],
                        [
                            'label' => 'Project token',
                            'binding' => 'integrations.marketing.MIXPANEL_PROJECT_TOKEN',
                            'type' => 'text',
                        ],
                        [
                            'label' => 'API secret',
                            'binding' => 'integrations.marketing.MIXPANEL_API_SECRET',
                            'type' => 'password',
                        ],
                    ],
                    'steps' => [
                        'Entra a https://mixpanel.com/settings/project y copia el Project Token.',
                        'Crea un Service Account (Settings › Access) y genera su password; se usa como API secret.',
                        'Opcional: define un “Region” (US/EU). Si usas EU cambia MIXPANEL_ENDPOINT a https://api-eu.mixpanel.com.',
                    ],
                    'validation' => [
                        [
                            'label' => 'telemetry:sync mixpanel',
                            'command' => 'php artisan telemetry:sync --driver=mixpanel',
                            'description' => 'Confirma que el comando responde “events sent” sin errores.',
                        ],
                    ],
                    'troubleshooting' => [
                        'Error 401 = token o service account incorrectos.',
                        'Si trabajas en la región EU, usa el endpoint api-eu.mixpanel.com.',
                    ],
                    'playbook' => [
                        'audiences' => ['admin'],
                        'env' => ['MIXPANEL_PROJECT_TOKEN', 'MIXPANEL_API_SECRET'],
                        'status_hint' => 'Sólo habilita el driver cuando ambos campos estén configurados.',
                        'next_steps' => [
                            'Mapea eventos en Mixpanel › Lexicon para que tengan etiquetas legibles.',
                        ],
                    ],
                ],
                [
                    'slug' => 'recaptcha',
                    'name' => 'reCAPTCHA v3',
                    'summary' => 'Protege formularios públicos (registro, requests de prácticas).',
                    'docs' => 'https://www.google.com/recaptcha/admin/create',
                    'tokens' => [
                        ['label' => 'RECAPTCHA_SITE_KEY', 'hint' => 'Clave pública'],
                        ['label' => 'RECAPTCHA_SECRET_KEY', 'hint' => 'Clave privada'],
                    ],
                    'fields' => [
                        [
                            'label' => 'Site key',
                            'binding' => 'integrations.marketing.RECAPTCHA_SITE_KEY',
                            'type' => 'text',
                        ],
                        [
                            'label' => 'Secret key',
                            'binding' => 'integrations.marketing.RECAPTCHA_SECRET_KEY',
                            'type' => 'password',
                        ],
                    ],
                    'steps' => [
                        'Crea una propiedad reCAPTCHA tipo v3 e ingresa el dominio del LMS.',
                        'Copia la site key y secret key en el asistente.',
                        'Desde Admin › Seguridad puedes forzar el threshold mínimo recomendado (0.4).',
                    ],
                    'validation' => [
                        [
                            'label' => 'Siteverify',
                            'command' => 'curl "https://www.google.com/recaptcha/api/siteverify" -d "secret=<SECRET>&response=test"',
                            'description' => 'Debe responder JSON con success=true. Úsalo para descartar errores de clave.',
                        ],
                    ],
                    'troubleshooting' => [
                        'Si el formulario siempre marca “robot”, revisa que el dominio HTTPS esté agregado en la consola.',
                        'Ajusta el threshold en config/security.php si recibes falsos positivos.',
                    ],
                    'playbook' => [
                        'audiences' => ['admin'],
                        'env' => ['RECAPTCHA_SITE_KEY', 'RECAPTCHA_SECRET_KEY'],
                        'status_hint' => 'Protección activa para forms públicos.',
                        'next_steps' => [
                            'Revisa el dashboard de reCAPTCHA y confirma que el score promedio sea >0.7.',
                        ],
                    ],
                ],
            ],
        ],
        [
            'key' => 'automation',
            'title' => 'Automatización & bots',
            'description' => 'Single sign-on, bots Discord y enlaces WhatsApp.',
            'providers' => [
                [
                    'slug' => 'google-oauth',
                    'name' => 'Google OAuth (Breeze + Socialite)',
                    'summary' => 'Permite registrarse o iniciar sesión con cuentas Google.',
                    'docs' => 'https://developers.google.com/identity/protocols/oauth2',
                    'tokens' => [
                        ['label' => 'GOOGLE_CLIENT_ID', 'hint' => 'OAuth client ID'],
                        ['label' => 'GOOGLE_CLIENT_SECRET', 'hint' => 'OAuth client secret'],
                        ['label' => 'GOOGLE_REDIRECT_URI', 'hint' => 'URL callback (`/auth/google/callback`)'],
                    ],
                    'fields' => [
                        ['label' => 'Client ID', 'binding' => 'integrations.automation.GOOGLE_CLIENT_ID', 'type' => 'text'],
                        ['label' => 'Client secret', 'binding' => 'integrations.automation.GOOGLE_CLIENT_SECRET', 'type' => 'password'],
                        ['label' => 'Redirect URI', 'binding' => 'integrations.automation.GOOGLE_REDIRECT_URI', 'type' => 'text', 'placeholder' => $appUrl.'/auth/google/callback'],
                    ],
                    'steps' => [
                        'Google Cloud Console › Credentials › Create OAuth client ID (tipo Web).',
                        'Agrega como “Authorized redirect URI” la ruta /auth/google/callback (con https).',
                        'Verifica el dominio en Search Console para evitar pantallas amarillas de verificación.',
                    ],
                    'validation' => [
                        [
                            'label' => 'Login social',
                            'description' => 'En /login haz clic en “Continuar con Google”. Si Google muestra pantalla amarilla, revisa la verificación del dominio.',
                        ],
                    ],
                    'troubleshooting' => [
                        'Error 400 redirect_uri_mismatch: asegúrate de incluir https://dominio/auth/google/callback.',
                        'Si el flujo dice “app no verificada” publica la app desde OAuth consent screen.',
                    ],
                    'playbook' => [
                        'audiences' => ['admin'],
                        'env' => ['GOOGLE_CLIENT_ID', 'GOOGLE_CLIENT_SECRET', 'GOOGLE_REDIRECT_URI'],
                        'status_hint' => 'Permite registro/login con Google y alimenta datos del perfil progresivo.',
                        'next_steps' => [
                            'Envía un correo de bienvenida usando Admin › Notificaciones y confirma que se llenen los datos básicos.',
                        ],
                    ],
                ],
                [
                    'slug' => 'google-sheets',
                    'name' => 'Google Sheets (Service Account)',
                    'summary' => 'Exporta leads y tareas a hojas compartidas via DataPorter.',
                    'docs' => 'https://developers.google.com/sheets/api/quickstart/service',
                    'tokens' => [
                        ['label' => 'GOOGLE_SERVICE_ACCOUNT_JSON_PATH', 'hint' => 'Ruta en storage/ con el JSON'],
                        ['label' => 'SHEET_ID', 'hint' => 'ID del spreadsheet'],
                        ['label' => 'GOOGLE_SHEETS_ENABLED', 'hint' => 'Bandera para habilitarlo'],
                    ],
                    'fields' => [
                        [
                            'label' => 'Habilitar Sheets',
                            'binding' => 'integrations.automation.GOOGLE_SHEETS_ENABLED',
                            'type' => 'toggle',
                        ],
                        [
                            'label' => 'Ruta JSON',
                            'binding' => 'integrations.automation.GOOGLE_SERVICE_ACCOUNT_JSON_PATH',
                            'type' => 'text',
                            'placeholder' => 'storage/app/keys/google.json',
                        ],
                        [
                            'label' => 'Sheet ID',
                            'binding' => 'integrations.automation.SHEET_ID',
                            'type' => 'text',
                        ],
                    ],
                    'steps' => [
                        'En https://console.cloud.google.com/iam-admin/serviceaccounts crea un servicio y descarga el JSON.',
                        'Sube el JSON a storage/app/keys/google.json y asegúrate de .gitignore.',
                        'Comparte la hoja de Google con el correo del service account en modo Editor.',
                    ],
                    'validation' => [
                        [
                            'label' => 'Exportar desde DataPorter',
                            'description' => 'En Admin › DataPorter selecciona “Google Sheets” y ejecuta un export manual para confirmar que el service account tenga permisos.',
                        ],
                    ],
                    'troubleshooting' => [
                        'Si ves 403 “The caller does not have permission”, comparte el Sheet con el service account.',
                        'Guarda el JSON fuera del repo y referencia la ruta relativa.',
                    ],
                    'playbook' => [
                        'audiences' => ['admin'],
                        'env' => ['GOOGLE_SERVICE_ACCOUNT_JSON_PATH', 'SHEET_ID'],
                        'status_hint' => 'Activa sólo cuando el JSON exista en el servidor.',
                        'next_steps' => [
                            'Define el rango por defecto en GOOGLE_SHEETS_RANGE si necesitas escribir en otra pestaña.',
                        ],
                    ],
                ],
                [
                    'slug' => 'make',
                    'name' => 'Make.com / Integromat',
                    'summary' => 'Webhook firmado para campañas y sincronizaciones externas.',
                    'docs' => 'https://www.make.com/en/help/modules/webhooks',
                    'tokens' => [
                        ['label' => 'MAKE_WEBHOOK_URL', 'hint' => 'URL del webhook personalizado'],
                        ['label' => 'WEBHOOKS_MAKE_SECRET', 'hint' => 'Se usa para el header X-Signature'],
                    ],
                    'fields' => [
                        ['label' => 'Webhook URL', 'binding' => 'integrations.automation.MAKE_WEBHOOK_URL', 'type' => 'text'],
                        ['label' => 'Secret HMAC', 'binding' => 'integrations.automation.WEBHOOKS_MAKE_SECRET', 'type' => 'password'],
                    ],
                    'steps' => [
                        'En Make crea un “Custom webhook” y copia la URL.',
                        'Genera un secret (puede ser con `php artisan key:generate --show`) y colócalo en el campo.',
                        'Cada payload se envía con header `X-Signature: hash_hmac(\'sha256\', body, secret)`.',
                    ],
                    'validation' => [
                        [
                            'label' => 'Ping webhook',
                            'command' => 'curl -X POST "<WEBHOOK>" -H "X-Signature: test" -d \'{"ping":true}\'',
                            'description' => 'Verifica que Make registre el request. Usa el secret real para firmas válidas.',
                        ],
                    ],
                    'troubleshooting' => [
                        'Si Make marca “Invalid signature”, confirma que tanto el escenario como el LMS compartan el mismo secret HMAC.',
                        'Protege la URL del webhook; no debe exponerse en clientes públicos.',
                    ],
                    'playbook' => [
                        'audiences' => ['admin','teacher'],
                        'env' => ['MAKE_WEBHOOK_URL', 'WEBHOOKS_MAKE_SECRET'],
                        'status_hint' => 'Sin secret, los escenarios quedarán vulnerables.',
                        'next_steps' => [
                            'Activa el módulo Make en Admin › Integraciones para ver los logs por evento.',
                        ],
                    ],
                ],
                [
                    'slug' => 'discord',
                    'name' => 'Discord webhook + planner',
                    'summary' => 'Envía alertas de prácticas, packs y colas de solicitudes.',
                    'docs' => 'https://support.discord.com/hc/en-us/articles/228383668-Intro-to-Webhooks',
                    'tokens' => [
                        ['label' => 'DISCORD_WEBHOOK_URL', 'hint' => 'Webhook general'],
                        ['label' => 'DISCORD_WEBHOOK_USERNAME', 'hint' => 'Nombre mostrado'],
                        ['label' => 'DISCORD_WEBHOOK_AVATAR', 'hint' => 'Avatar opcional'],
                        ['label' => 'DISCORD_WEBHOOK_THREAD_ID', 'hint' => 'Thread específico'],
                        ['label' => 'DISCORD_PRACTICES_REQUEST_THRESHOLD', 'hint' => 'Cuántas solicitudes disparan alerta'],
                        ['label' => 'DISCORD_PRACTICES_REQUEST_COOLDOWN_MINUTES', 'hint' => 'Tiempo de enfriamiento'],
                    ],
                    'fields' => [
                        ['label' => 'Webhook URL', 'binding' => 'integrations.automation.DISCORD_WEBHOOK_URL', 'type' => 'text'],
                        ['label' => 'Username', 'binding' => 'integrations.automation.DISCORD_WEBHOOK_USERNAME', 'type' => 'text', 'placeholder' => 'AulaVirtual LTS'],
                        ['label' => 'Avatar URL', 'binding' => 'integrations.automation.DISCORD_WEBHOOK_AVATAR', 'type' => 'text'],
                        ['label' => 'Thread ID', 'binding' => 'integrations.automation.DISCORD_WEBHOOK_THREAD_ID', 'type' => 'text'],
                        [
                            'label' => 'Threshold solicitudes',
                            'binding' => 'integrations.automation.DISCORD_PRACTICES_REQUEST_THRESHOLD',
                            'type' => 'number',
                        ],
                        [
                            'label' => 'Cooldown (minutos)',
                            'binding' => 'integrations.automation.DISCORD_PRACTICES_REQUEST_COOLDOWN_MINUTES',
                            'type' => 'number',
                        ],
                    ],
                    'steps' => [
                        'En Discord abre el canal › Edit Channel › Integrations › Webhooks › New Webhook.',
                        'Si quieres enviar a un Thread específico, copia su ID (Developer mode > Copy ID).',
                        'Ajusta el threshold/cooldown para controlar cuándo se avisa a los Teacher Admin.',
                    ],
                    'validation' => [
                        [
                            'label' => 'Webhook test',
                            'command' => 'curl -H "Content-Type: application/json" -d \'{"content":"Ping desde LMS"}\' <WEBHOOK_URL>',
                            'description' => 'El mensaje debe aparecer en el canal/Thread configurado.',
                        ],
                    ],
                    'troubleshooting' => [
                        'Si no llega el mensaje revisa que el webhook tenga permisos en ese canal.',
                        'Asegúrate de activar el Modo desarrollador de Discord para copiar Thread IDs.',
                    ],
                    'playbook' => [
                        'audiences' => ['admin','teacher'],
                        'env' => ['DISCORD_WEBHOOK_URL'],
                        'status_hint' => 'El planner y las alertas se apoyan en este webhook.',
                        'next_steps' => [
                            'Comprueba que en Admin › Planner las duplicaciones disparen el evento DiscordPracticeScheduled.',
                        ],
                    ],
                ],
                [
                    'slug' => 'whatsapp',
                    'name' => 'WhatsApp deeplink',
                    'summary' => 'Botón global para contacto rápido (CTA en dashboards y Player).',
                    'docs' => 'https://faq.whatsapp.com/591746941422197',
                    'tokens' => [
                        ['label' => 'WHATSAPP_DEEPLINK', 'hint' => 'Formato https://wa.me/<phone>?text=...'],
                    ],
                    'fields' => [
                        [
                            'label' => 'Enlace WhatsApp',
                            'binding' => 'integrations.automation.WHATSAPP_DEEPLINK',
                            'type' => 'text',
                            'placeholder' => 'https://wa.me/51999999999?text=Hola+LTS',
                        ],
                    ],
                    'steps' => [
                        'Arma el enlace con el número en formato internacional y un texto URL encoded.',
                        'Puedes generar textos dinámicos usando Make; este campo es el fallback global.',
                        'Úsalo en banners, player CTA y recordatorios automáticos.',
                    ],
                    'validation' => [
                        [
                            'label' => 'Abrir deeplink',
                            'description' => 'Haz clic en cualquier CTA de WhatsApp dentro del dashboard para verificar que abra la app o WhatsApp Web con el texto precargado.',
                        ],
                    ],
                    'troubleshooting' => [
                        'Si al abrir muestra número inválido, revisa que uses formato internacional sin + ni 00 (ej. 51999999999).',
                        'Codifica el texto usando %20 para espacios.',
                    ],
                    'playbook' => [
                        'audiences' => ['admin','teacher'],
                        'env' => ['WHATSAPP_DEEPLINK'],
                        'status_hint' => 'Fallback global para CTA de soporte y recordatorios.',
                        'next_steps' => [
                            'Si usas la API Cloud de Meta, complementa con WHATSAPP_TOKEN/PHONE_ID en config/services.php.',
                        ],
                    ],
                ],
            ],
        ],
        [
            'key' => 'observability',
            'title' => 'Observabilidad & seguridad',
            'description' => 'Centraliza errores críticos del LMS.',
            'providers' => [
                [
                    'slug' => 'sentry',
                    'name' => 'Sentry Laravel',
                    'summary' => 'Captura excepciones del backend y jobs.',
                    'docs' => 'https://docs.sentry.io/platforms/php/guides/laravel/',
                    'tokens' => [
                        ['label' => 'SENTRY_LARAVEL_DSN', 'hint' => 'DSN del proyecto'],
                    ],
                    'fields' => [
                        [
                            'label' => 'Sentry DSN',
                            'binding' => 'integrations.observability.SENTRY_LARAVEL_DSN',
                            'type' => 'text',
                        ],
                    ],
                    'steps' => [
                        'Crea un proyecto en https://sentry.io › Projects.',
                        'En Settings copia el DSN público y pégalo aquí.',
                        'Opcional: configura Environments (production, staging) desde la UI de Sentry.',
                    ],
                    'validation' => [
                        [
                            'label' => 'sentry:test',
                            'command' => 'php artisan sentry:test',
                            'description' => 'Debe aparecer un evento “This is a test exception” en el proyecto.',
                        ],
                    ],
                    'troubleshooting' => [
                        'Si no llegan eventos confirma que `SENTRY_LARAVEL_DSN` esté definido antes de bootstrap (en .env).',
                        'Para ignorar entornos locales define `SENTRY_ENABLED=false` en local.',
                    ],
                    'playbook' => [
                        'audiences' => ['admin'],
                        'env' => ['SENTRY_LARAVEL_DSN'],
                        'status_hint' => 'Sin Sentry perderás trazas en producción.',
                        'next_steps' => [
                            'Mapea issues críticos y resuélvelos antes del deploy a Hostinger.',
                        ],
                    ],
                ],
            ],
        ],
    ],
];


