<?php

return [
    'contexts' => [
        'setup_integrations' => [
            'title' => 'Checklist de credenciales',
            'subtitle' => 'Repasa qué servicios debes tener listos antes de finalizar el asistente.',
            'cards' => [
                'video_streaming' => [
                    'title' => 'Video & streaming',
                    'summary' => 'Decide si usarás únicamente YouTube o activarás Vimeo/Cloudflare.',
                    'description' => 'Para producción recomendamos activar al menos un proveedor con protección (Vimeo o Cloudflare Stream).',
                    'tokens' => [
                        ['label' => 'YouTube', 'hint' => 'Define el dominio en YOUTUBE_ORIGIN'],
                        ['label' => 'Vimeo', 'hint' => 'Token con scopes video_files + private'],
                        ['label' => 'Cloudflare', 'hint' => 'Account ID + token Stream:Edit'],
                    ],
                    'steps' => [
                        'Revisa la política de privacidad del cliente (¿se permite YouTube?).',
                        'Si no tendrás DRM desde el día uno, al menos añade el dominio al fallback YouTube.',
                        'Cuando cargues clases largas, realiza un smoke test en /lessons/player.',
                    ],
                ],
                'automations' => [
                    'title' => 'Automatizaciones mínimas',
                    'summary' => 'Google OAuth, Discord y Make habilitan las microinteracciones del planner.',
                    'description' => 'Sin estas credenciales el planner y los recordatorios usarán solo correos locales.',
                    'tokens' => [
                        ['label' => 'Google OAuth', 'hint' => 'Client ID / Secret verificados'],
                        ['label' => 'Discord', 'hint' => 'Webhook dedicado para prácticas'],
                        ['label' => 'Make', 'hint' => 'Webhook seguro con HMAC'],
                    ],
                    'steps' => [
                        'Abre el modo Desarrollador de Discord y copia el thread donde se archivarán slots.',
                        'Genera un secret único para Make y guárdalo también en el escenario.',
                        'Valida el login social en /login → “Continuar con Google”.',
                    ],
                ],
            ],
        ],
        'admin_dashboard' => [
            'title' => 'Cómo leer este panel',
            'subtitle' => 'Checklist operativo para el rol Admin.',
            'cards' => [
                'status' => [
                    'title' => 'Estado de integraciones',
                    'summary' => 'El bloque inferior resume si S3, Pusher, SMTP y telemetría responden.',
                    'description' => 'Cuando veas “Pendiente” abre Admin › Provisioner para actualizar credenciales.',
                    'tokens' => [
                        ['label' => 'DataPorter', 'hint' => 'drivers activos y eventos pendientes'],
                        ['label' => 'S3 / R2', 'hint' => 'Bucket sincronizado'],
                    ],
                    'steps' => [
                        'Haz clic en “Ver outbox” si pending/failed > 0.',
                        'Ejecuta `php artisan integration:status` en consola para confirmar credenciales.',
                        'Repite después de cada deploy (workflow smoke).',
                    ],
                ],
                'telemetry' => [
                    'title' => 'Telemetría y QA',
                    'summary' => 'Los bloques de horas vistas, abandono y XP dependen de GA4/Mixpanel.',
                    'tokens' => [
                        ['label' => 'GA4 Enabled', 'hint' => 'Debe estar en true para enviar player events'],
                        ['label' => 'Mixpanel', 'hint' => 'Opcional para funnels'],
                    ],
                    'steps' => [
                        'Abre Admin › DataPorter y revisa el panel de sincronización.',
                        'Si hay eventos “pending”, ejecuta `php artisan telemetry:sync` o programa el cron.',
                        'Documenta los hallazgos en /docs/player_signals_playbook.md.',
                    ],
                ],
            ],
        ],
        'professor_dashboard' => [
            'title' => 'Atajos para Teacher Admin',
            'subtitle' => 'Planifica prácticas y seguimiento desde un solo lugar.',
            'cards' => [
                'planner' => [
                    'title' => 'Planner Discord',
                    'summary' => 'El widget “Prácticas Discord” usa los datos del planner Livewire.',
                    'tokens' => [
                        ['label' => 'Templates', 'hint' => 'Configura cohortes en config/practice.php'],
                        ['label' => 'Discord threshold', 'hint' => 'Controla cuándo se alerta a Admin'],
                    ],
                    'steps' => [
                        'Duplica slots desde el planner y verifica que las reservas cambien en este panel.',
                        'Cuando un alumno reserve, se actualizará el contador “Reservas”.',
                        'Si no ves datos, revisa que el cron `practice:sync` esté activo.',
                    ],
                ],
                'heatmap' => [
                    'title' => 'Heatmap & insights',
                    'summary' => 'Se alimenta de `video_heatmap_segments`. Necesita TelemetryRecorder activo.',
                    'tokens' => [
                        ['label' => 'playerSignals', 'hint' => 'Debe cargarse en resources/js/app.js'],
                    ],
                    'steps' => [
                        'Da play a la lección con mayor abandono; el heatmap debe coincidir.',
                        'Exporta la data desde Admin › DataPorter si necesitas reporte mensual.',
                    ],
                ],
            ],
        ],
        'student_dashboard' => [
            'title' => 'Cómo aprovechar tu panel',
            'subtitle' => 'Guía rápida para Students.',
            'cards' => [
                'progress' => [
                    'title' => 'Barra de progreso y packs',
                    'summary' => 'El widget superior combina XP, racha y recordatorios de prácticas.',
                    'tokens' => [
                        ['label' => 'XP', 'hint' => 'Se actualiza al completar videos y tareas'],
                        ['label' => 'Pack reminder', 'hint' => 'Aparece cuando hay un slot recomendado'],
                    ],
                    'steps' => [
                        'Haz clic en “Ver prácticas” para saltar directo al browser filtrado.',
                        'Si no necesitas el recordatorio, usa “Descartar” para liberar el banner.',
                    ],
                ],
                'assignments' => [
                    'title' => 'Asignaciones pendientes',
                    'summary' => 'El bloque inferior resume tareas y feedback.',
                    'steps' => [
                        'Utiliza el botón WhatsApp si necesitas soporte y se generará un deep link con el contexto.',
                        'Cada chip (Pendiente, Entregada, Aprobada) se alimenta de tus envíos reales.',
                    ],
                ],
            ],
        ],
    ],
    'routes' => [
        'lessons_player' => [
            'cards' => [
                'player' => [
                    'title' => 'Player UIX 2030',
                    'summary' => 'Explora la barra segmentada y los CTA contextuales.',
                    'steps' => [
                        'Los marcadores indican el final de cada capítulo; haz clic para saltar.',
                        'La tarjeta contextual cambia entre prácticas, packs y recursos guardados.',
                        'El banner “Retoma desde…” aparece cuando vuelves a una lección a mitad.',
                    ],
                ],
            ],
        ],
        'courses_builder' => [
            'cards' => [
                'builder' => [
                    'title' => 'Course Builder',
                    'summary' => 'Atajos clave: N crea capítulo, Ctrl/Cmd+S guarda la lección enfocada.',
                    'steps' => [
                        'El panel de enfoque tiene pestañas de Contenido, Práctica y Gamificación.',
                        'Usa los chips de prácticas/packs para abrir el planner en una pestaña nueva.',
                        'Duplica o convierte lecciones desde el menú rápido (…).',
                    ],
                ],
            ],
        ],
        'admin_data_porter' => [
            'cards' => [
                'dataporter' => [
                    'title' => 'DataPorter Hub',
                    'summary' => 'Exporta CSV/JSON filtrados y monitorea la sincronización GA4/Mixpanel.',
                    'steps' => [
                        'Selecciona el dataset (video_player_events, student_activity_snapshots, etc.).',
                        'Aplica filtros por curso, categoría o fecha antes de exportar.',
                        'Usa “Sincronizar telemetría” para forzar el envío manual.',
                    ],
                ],
            ],
        ],
        'student_discord_practices' => [
            'cards' => [
                'discord' => [
                    'title' => 'Reservas en Discord',
                    'summary' => 'Requiere un pack activo si el slot tiene el candado.',
                    'steps' => [
                        'Filtra por cohorte o profesor desde el lateral.',
                        'Haz clic en “Reservar” para consumir una sesión del pack.',
                    ],
                ],
            ],
        ],
        'professor_discord_practices' => [
            'cards' => [
                'advanced_planner' => [
                    'title' => 'Planner avanzado',
                    'summary' => 'Guarda plantillas con múltiples slots y duplica cohortes.',
                    'steps' => [
                        'Configura la plantilla con los campos Lesson, Canal, Cupos y requisitos.',
                        'Usa “Duplicación masiva” para generar series semanales.',
                        'Aplica un Template de cohorte para precargar horarios sugeridos.',
                    ],
                ],
            ],
        ],
        'dashboard' => [
            'cards' => [
                'executive' => [
                    'title' => 'Resumen ejecutivo',
                    'summary' => 'Este dashboard cambia según tu rol.',
                    'steps' => [
                        'El bloque superior muestra métricas generales y estado de integraciones.',
                        'El Playbook te ayuda a validar credenciales antes de cada deploy.',
                        'Los paneles inferiores agrupan WhatsApp, XP, certificados y outbox.',
                    ],
                ],
                'teacher_mode' => [
                    'title' => 'Modo Teacher Admin',
                    'summary' => 'Combina planner, prácticas y heatmaps.',
                    'steps' => [
                        'Revisa el bloque de integraciones críticas (Discord, Make, WhatsApp).',
                        'Duplica sesiones desde el widget “Prácticas Discord” y monitorea reservas.',
                        'El heatmap resalta la lección con más reproducciones; úsalo para planear refuerzos.',
                    ],
                ],
                'student_panel' => [
                    'title' => 'Panel estudiante',
                    'summary' => 'Gamificación + recordatorios en un solo lugar.',
                    'steps' => [
                        'Los cuatro contadores superiores resumen progreso, tiempo y XP.',
                        'Cuando veas un pack recomendado, abre el browser de prácticas para reservar.',
                        'Los recordatorios de tareas incluyen un deeplink a WhatsApp para soporte inmediato.',
                    ],
                ],
            ],
        ],
    ],
];


