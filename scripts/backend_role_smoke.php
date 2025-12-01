<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

// [AGENTE: OPUS 4.5] - Fix: RuntimeException y Throwable ya son globales, no requieren use

/**
 * Backend smoke script that logs in with each LMS role and pings the critical dashboards/APIs.
 * Designed to be executed via SSH before running manual browser tests.
 */
$baseUrl = 'https://app.letstalkspanish.io';
$locale = 'es';

$roleMatrix = [
    'admin' => [
        'label' => 'Admin + TeacherAdmin',
        'login_path' => "/{$locale}/admin/login",
        'email' => 'academy@letstalkspanish.io',
        'password' => 'AcademyVPS2025!',
        'checks' => [
            ['label' => 'Dashboard global', 'method' => 'GET', 'path' => "/{$locale}/dashboard"],
            ['label' => 'Dashboard admin', 'method' => 'GET', 'path' => "/{$locale}/admin/dashboard"],
            ['label' => 'Notificaciones admin', 'method' => 'GET', 'path' => "/{$locale}/admin/notifications"],
            ['label' => 'Branding Designer', 'method' => 'GET', 'path' => "/{$locale}/admin/branding"],
            ['label' => 'Provisioner', 'method' => 'GET', 'path' => "/{$locale}/provisioner"],
            ['label' => 'Assignments Manager', 'method' => 'GET', 'path' => "/{$locale}/admin/assignments"],
            ['label' => 'Practice Planner', 'method' => 'GET', 'path' => "/{$locale}/professor/practices"],
            ['label' => 'Practice Pack Manager', 'method' => 'GET', 'path' => "/{$locale}/professor/practice-packs"],
            ['label' => 'Cohort Templates', 'method' => 'GET', 'path' => "/{$locale}/admin/planner/templates"],
            ['label' => 'Teacher Manager', 'method' => 'GET', 'path' => "/{$locale}/admin/teachers"],
            ['label' => 'Teacher Submissions Hub', 'method' => 'GET', 'path' => "/{$locale}/admin/teacher-submissions"],
            ['label' => 'Teacher Performance', 'method' => 'GET', 'path' => "/{$locale}/admin/teacher-performance"],
            ['label' => 'Group Manager', 'method' => 'GET', 'path' => "/{$locale}/admin/groups"],
            ['label' => 'Tier Manager', 'method' => 'GET', 'path' => "/{$locale}/admin/tiers"],
            ['label' => 'Page Manager', 'method' => 'GET', 'path' => "/{$locale}/admin/pages"],
            ['label' => 'Product Catalog', 'method' => 'GET', 'path' => "/{$locale}/admin/products"],
            ['label' => 'Payment Simulator', 'method' => 'GET', 'path' => "/{$locale}/admin/payments/simulator"],
            ['label' => 'Integration Outbox', 'method' => 'GET', 'path' => "/{$locale}/admin/integrations/outbox"],
            ['label' => 'DataPorter Hub', 'method' => 'GET', 'path' => "/{$locale}/admin/data-porter"],
            ['label' => 'Admin Messages', 'method' => 'GET', 'path' => "/{$locale}/admin/messages"],
            ['label' => 'Course Builder', 'method' => 'GET', 'path' => "/{$locale}/courses/1/builder"],
            ['label' => 'Lesson Player', 'method' => 'GET', 'path' => "/{$locale}/lessons/1/player"],
            [
                'label' => 'WhatsApp redirect (firma requerida)',
                'method' => 'GET',
                'path' => '/whatsapp/redirect',
                'expect' => 403,
                'options' => ['allow_redirects' => false],
            ],
            ['label' => 'Catálogo público', 'method' => 'GET', 'path' => "/{$locale}/catalog"],
            ['label' => 'Shop catálogo', 'method' => 'GET', 'path' => "/{$locale}/catalogo"],
        ],
    ],
    'teacher_admin' => [
        'label' => 'Teacher Admin',
        'login_path' => "/{$locale}/teacher-admin/login",
        'email' => 'teacher.admin@letstalkspanish.io',
        'password' => 'TeacherAdmin2025!',
        'checks' => [
            ['label' => 'Dashboard teacher-admin', 'method' => 'GET', 'path' => "/{$locale}/teacher-admin/dashboard"],
            ['label' => 'Assignments restricted', 'method' => 'GET', 'path' => "/{$locale}/admin/assignments"],
            ['label' => 'Practice Planner', 'method' => 'GET', 'path' => "/{$locale}/professor/practices"],
            ['label' => 'Practice Packs Manager', 'method' => 'GET', 'path' => "/{$locale}/professor/practice-packs"],
            ['label' => 'Cohort Templates', 'method' => 'GET', 'path' => "/{$locale}/admin/planner/templates"],
            ['label' => 'Admin Messages', 'method' => 'GET', 'path' => "/{$locale}/admin/messages"],
            ['label' => 'DataPorter lectura', 'method' => 'GET', 'path' => "/{$locale}/admin/data-porter"],
            ['label' => 'Payments monitor', 'method' => 'GET', 'path' => "/{$locale}/admin/payments/simulator"],
            ['label' => 'Branding lectura', 'method' => 'GET', 'path' => "/{$locale}/admin/branding"],
            ['label' => 'Course Builder', 'method' => 'GET', 'path' => "/{$locale}/courses/1/builder"],
            ['label' => 'Lesson Player', 'method' => 'GET', 'path' => "/{$locale}/lessons/1/player"],
        ],
    ],
    'teacher' => [
        'label' => 'Teacher',
        'login_path' => "/{$locale}/teacher/login",
        'email' => 'teacher@letstalkspanish.io',
        'password' => 'TeacherQA2025!',
        'checks' => [
            ['label' => 'Teacher dashboard', 'method' => 'GET', 'path' => "/{$locale}/teacher/dashboard"],
            ['label' => 'Mensajería docente', 'method' => 'GET', 'path' => "/{$locale}/admin/messages"],
            ['label' => 'Perfil docente', 'method' => 'GET', 'path' => "/{$locale}/profile"],
            ['label' => 'Player acceso docente', 'method' => 'GET', 'path' => "/{$locale}/lessons/1/player"],
            [
                'label' => 'WhatsApp soporte (firma requerida)',
                'method' => 'GET',
                'path' => '/whatsapp/redirect',
                'expect' => 403,
                'options' => ['allow_redirects' => false],
            ],
        ],
    ],
    'student' => [
        'label' => 'Student pago',
        'login_path' => "/{$locale}/student/login",
        'email' => 'student@letstalkspanish.io',
        'password' => 'StudentQA2025!',
        'checks' => [
            ['label' => 'Dashboard student', 'method' => 'GET', 'path' => "/{$locale}/student/dashboard"],
            ['label' => 'Mensajes student', 'method' => 'GET', 'path' => "/{$locale}/student/messages"],
            ['label' => 'Practice browser', 'method' => 'GET', 'path' => "/{$locale}/student/practices"],
            ['label' => 'Shop packs', 'method' => 'GET', 'path' => "/{$locale}/shop/packs"],
            ['label' => 'Shop cart', 'method' => 'GET', 'path' => "/{$locale}/shop/cart"],
            ['label' => 'Shop checkout', 'method' => 'GET', 'path' => "/{$locale}/shop/checkout"],
            ['label' => 'Checkout success', 'method' => 'GET', 'path' => "/{$locale}/shop/checkout/success"],
            ['label' => 'Student notifications', 'method' => 'GET', 'path' => "/{$locale}/student/notifications"],
            ['label' => 'Lesson Player', 'method' => 'GET', 'path' => "/{$locale}/lessons/1/player"],
            [
                'label' => 'WhatsApp redirect (firma requerida)',
                'method' => 'GET',
                'path' => '/whatsapp/redirect',
                'expect' => 403,
                'options' => ['allow_redirects' => false],
            ],
            ['label' => 'Catálogo cursos', 'method' => 'GET', 'path' => "/{$locale}/catalog"],
            [
                'label' => 'Player telemetry event',
                'method' => 'POST',
                'path' => "/{$locale}/api/player/events",
                'options' => [
                    'headers' => ['Accept' => 'application/json'],
                    'json' => [
                        'lesson_id' => 1,
                        'event' => 'qa_smoke_ping',
                        'playback_seconds' => 5,
                        'video_duration' => 10,
                        'watched_seconds' => 5,
                        'provider' => 'qa-script',
                        'context_tag' => 'smoke',
                        'metadata' => ['source' => 'backend-script'],
                    ],
                ],
            ],
        ],
    ],
];

$client = new Client([
    'base_uri' => $baseUrl,
    'verify' => '/etc/ssl/certs/ca-certificates.crt',
    'timeout' => 30,
    'http_errors' => false,
    'headers' => [
        'User-Agent' => 'LTS-Backend-Smoke/2025-11',
        'Accept-Language' => 'es-ES,es;q=0.9,en;q=0.8',
    ],
]);

$summary = [
    'executed_at' => date(DATE_ATOM),
    'base_url' => $baseUrl,
    'locale' => $locale,
    'roles' => [],
];

foreach ($roleMatrix as $key => $config) {
    $jar = new CookieJar();
    $roleResult = [
        'label' => $config['label'],
        'login' => null,
        'checks' => [],
        'errors' => [],
    ];

    try {
        $loginResp = $client->get($config['login_path'], [
            'cookies' => $jar,
            'allow_redirects' => true,
        ]);
        $loginBody = (string) $loginResp->getBody();

        if (! preg_match('/name=\"_token\" value=\"([^\"]+)\"/', $loginBody, $matches)) {
            throw new \RuntimeException('No se encontró el token CSRF en el formulario de login');
        }

        $token = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5);

        $postResp = $client->post("/{$locale}/login", [
            'cookies' => $jar,
            'allow_redirects' => false,
            'form_params' => [
                '_token' => $token,
                'email' => $config['email'],
                'password' => $config['password'],
            ],
            'headers' => [
                'Referer' => $config['login_path'],
            ],
        ]);

        $roleResult['login'] = [
            'status' => $postResp->getStatusCode(),
            'location' => $postResp->getHeaderLine('Location'),
        ];

        if ($postResp->getStatusCode() !== 302) {
            throw new \RuntimeException('Login fallido con status '.$postResp->getStatusCode());
        }

        $landing = $postResp->getHeaderLine('Location') ?: "/{$locale}/dashboard";
        $client->get($landing, ['cookies' => $jar]);

        foreach ($config['checks'] as $check) {
            $method = $check['method'] ?? 'GET';
            $path = $check['path'];
            $label = $check['label'];
            $expected = $check['expect'] ?? 200;
            $options = $check['options'] ?? [];
            $options['cookies'] = $jar;
            if (! isset($options['http_errors'])) {
                $options['http_errors'] = false;
            }
            if (! isset($options['headers'])) {
                $options['headers'] = [];
            }
            if (! isset($options['headers']['Accept'])) {
                $options['headers']['Accept'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
            }
            if ($method !== 'GET') {
                $xsrfCookie = $jar->getCookieByName('XSRF-TOKEN');
                if ($xsrfCookie && ! isset($options['headers']['X-XSRF-TOKEN'])) {
                    $options['headers']['X-XSRF-TOKEN'] = rawurldecode($xsrfCookie->getValue());
                }
            }

            $started = microtime(true);
            try {
                $response = $client->request($method, $path, $options);
                $duration = round((microtime(true) - $started) * 1000, 2);
                $body = (string) $response->getBody();
                $snippet = trim(mb_substr(strip_tags($body), 0, 180));

                $roleResult['checks'][] = [
                    'label' => $label,
                    'path' => $path,
                    'method' => $method,
                    'status' => $response->getStatusCode(),
                    'expected' => $expected,
                    'ok' => $response->getStatusCode() === $expected,
                    'duration_ms' => $duration,
                    'body_snippet' => $snippet,
                ];
            } catch (\Throwable $checkError) {
                $roleResult['checks'][] = [
                    'label' => $label,
                    'path' => $path,
                    'method' => $method,
                    'status' => null,
                    'expected' => $expected,
                    'ok' => false,
                    'error' => $checkError->getMessage(),
                ];
            }
        }
    } catch (\Throwable $e) {
        $roleResult['errors'][] = $e->getMessage();
    }

    $summary['roles'][$key] = $roleResult;
}

echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), PHP_EOL;

