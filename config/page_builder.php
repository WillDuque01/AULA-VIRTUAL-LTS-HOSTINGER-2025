<?php

return [
    'theme_presets' => [
        'noir' => [
            'label' => 'Noir',
            'primary' => '#0f172a',
            'secondary' => '#14b8a6',
            'background' => '#f8fafc',
            'font_family' => 'Inter, sans-serif',
        ],
        'sunset' => [
            'label' => 'Sunset',
            'primary' => '#be123c',
            'secondary' => '#f97316',
            'background' => '#fff7ed',
            'font_family' => 'Poppins, sans-serif',
        ],
        'matcha' => [
            'label' => 'Matcha',
            'primary' => '#064e3b',
            'secondary' => '#65a30d',
            'background' => '#f0fdf4',
            'font_family' => 'Nunito, sans-serif',
        ],
    ],
    'kits' => [
        'hero_simple' => [
            'label' => 'Hero básico',
            'type' => 'hero',
            'props' => [
                'headline' => 'Título inspirador',
                'subheadline' => 'Explica la propuesta de valor en una sola frase clara.',
                'cta_label' => 'Comenzar',
                'cta_url' => '#',
                'image' => null,
            ],
        ],
        'cta_split' => [
            'label' => 'Call to action',
            'type' => 'cta',
            'props' => [
                'title' => 'Activa tu programa hoy',
                'description' => 'Agenda una llamada o inscríbete directamente.',
                'primary_label' => 'Inscribirme',
                'primary_url' => '#',
                'secondary_label' => 'Hablar con un advisor',
                'secondary_url' => '#',
            ],
        ],
        'pricing_three' => [
            'label' => 'Tabla de precios (3 columnas)',
            'type' => 'pricing',
            'props' => [
                'title' => 'Planes disponibles',
                'items' => [
                    [
                        'name' => 'Starter',
                        'price' => '49',
                        'currency' => 'USD',
                        'features' => ['2 cohortes', 'Soporte comunitario'],
                        'cta_label' => 'Elegir',
                        'cta_url' => '#',
                        'highlight' => false,
                    ],
                    [
                        'name' => 'Pro',
                        'price' => '99',
                        'currency' => 'USD',
                        'features' => ['Cohortes ilimitadas', 'Mentorías 1:1', 'Plantillas premium'],
                        'cta_label' => 'Quiero este',
                        'cta_url' => '#',
                        'highlight' => true,
                    ],
                    [
                        'name' => 'Teams',
                        'price' => '129',
                        'currency' => 'USD',
                        'features' => ['Hasta 10 licencias', 'Onboarding asistido'],
                        'cta_label' => 'Contactar ventas',
                        'cta_url' => '#',
                        'highlight' => false,
                    ],
                ],
            ],
        ],
        'testimonials_carousel' => [
            'label' => 'Testimonios',
            'type' => 'testimonials',
            'props' => [
                'title' => 'Historias reales',
                'items' => [
                    [
                        'quote' => 'El programa me permitió lanzar mi emprendimiento en 6 semanas.',
                        'author' => 'María A.',
                        'role' => 'Founder, Cohorte 5',
                    ],
                    [
                        'quote' => 'Las sesiones en vivo y las plantillas aceleraron a mi equipo comercial.',
                        'author' => 'Luis M.',
                        'role' => 'Director Comercial',
                    ],
                ],
            ],
        ],
        'featured_products' => [
            'label' => 'Bloque destacado (productos)',
            'type' => 'featured-products',
            'props' => [
                'title' => 'Programas recomendados',
                'max_items' => 3,
                'show_badges' => true,
                'category' => null,
                'product_ids' => null,
            ],
        ],
        'gallery_masonry' => [
            'label' => 'Galería',
            'type' => 'gallery',
            'props' => [
                'title' => 'Momentos destacados',
                'items' => [
                    ['image' => null, 'caption' => 'Sesión de práctica'],
                    ['image' => null, 'caption' => 'Mentoría uno a uno'],
                    ['image' => null, 'caption' => 'Demo pública'],
                ],
            ],
        ],
        'team_grid' => [
            'label' => 'Equipo docente',
            'type' => 'team',
            'props' => [
                'title' => 'Conoce al equipo',
                'members' => [
                    ['name' => 'Alex R.', 'role' => 'Lead Mentor', 'avatar' => null, 'bio' => '10 años impulsando cohortes globales.'],
                    ['name' => 'Sara M.', 'role' => 'Coach Conversacional', 'avatar' => null, 'bio' => 'Especialista en sesiones live.'],
                ],
            ],
        ],
        'faq_list' => [
            'label' => 'Preguntas frecuentes',
            'type' => 'faq',
            'props' => [
                'title' => 'FAQ',
                'items' => [
                    ['question' => '¿Cómo accedo a las cohortes?', 'answer' => 'Recibirás un enlace en tu panel y en tu correo.'],
                    ['question' => '¿Hay reembolsos?', 'answer' => 'Ofrecemos garantía de 7 días sin preguntas.'],
                ],
            ],
        ],
        'timeline_steps' => [
            'label' => 'Timeline',
            'type' => 'timeline',
            'props' => [
                'title' => 'Cómo funciona',
                'steps' => [
                    ['title' => 'Kickoff', 'description' => 'Sesión inicial con tu mentor.', 'badge' => 'Día 1'],
                    ['title' => 'Sprints', 'description' => 'Trabaja con cohortes y retos semanales.', 'badge' => 'Semanas 1-4'],
                    ['title' => 'Demo Day', 'description' => 'Presenta tu proyecto y recibe feedback.', 'badge' => 'Semana 5'],
                ],
            ],
        ],
        'lead_form' => [
            'label' => 'Formulario de lead',
            'type' => 'lead-form',
            'props' => [
                'title' => 'Solicita más información',
                'description' => 'Déjanos tu correo y nos pondremos en contacto.',
                'fields' => [
                    ['label' => 'Nombre', 'placeholder' => 'Tu nombre', 'type' => 'text'],
                    ['label' => 'Correo', 'placeholder' => 'correo@ejemplo.com', 'type' => 'email'],
                ],
                'cta_label' => 'Enviar',
            ],
        ],
        'video_testimonial' => [
            'label' => 'Testimonio en video',
            'type' => 'video-testimonial',
            'props' => [
                'title' => 'Lo que dicen nuestros alumnos',
                'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                'quote' => 'Este programa cambió la forma en la que opero mi negocio.',
                'author' => 'Laura P.',
                'role' => 'Cohorte Growth 2025',
            ],
        ],
        'countdown' => [
            'label' => 'Countdown',
            'type' => 'countdown',
            'props' => [
                'title' => 'Próxima cohorte comienza en',
                'target_date' => now()->addDays(7)->toDateTimeString(),
                'cta_label' => 'Reservar mi lugar',
                'cta_url' => '#',
            ],
        ],
    ],
];


