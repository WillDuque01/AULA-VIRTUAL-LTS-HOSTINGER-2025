<?php

return [
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
    ],
];


