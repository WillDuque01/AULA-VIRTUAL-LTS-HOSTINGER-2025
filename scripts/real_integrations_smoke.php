<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$results = [];

$envValue = function (string $key, ?string $default = null): ?string {
    $value = env($key);

    if ($value !== null && $value !== '') {
        return $value;
    }

    $path = base_path('.env');

    if (! file_exists($path)) {
        return $default;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (! str_starts_with($line, $key.'=')) {
            continue;
        }

        $raw = substr($line, strlen($key) + 1);

        return trim($raw, " \"'");
    }

    return $default;
};

/**
 * @param  callable():string|null  $callback
 */
$run = function (string $label, callable $callback) use (&$results): void {
    try {
        $message = $callback();
        $results[] = [
            'label' => $label,
            'status' => 'ok',
            'message' => $message,
        ];
        echo '['.now()->toDateTimeString()."] [OK] {$label}: {$message}".PHP_EOL;
    } catch (\Throwable $exception) {
        $results[] = [
            'label' => $label,
            'status' => 'failed',
            'message' => $exception->getMessage(),
        ];
        echo '['.now()->toDateTimeString()."] [FAIL] {$label}: {$exception->getMessage()}".PHP_EOL;
    }
};

$run('Pusher trigger', function (): string {
    $config = config('services.pusher');
    $appId = $config['app_id'] ?? null;
    $key = $config['key'] ?? null;
    $secret = $config['secret'] ?? null;
    $cluster = $config['cluster'] ?? 'mt1';

    if (! $appId || ! $key || ! $secret) {
        return 'Credenciales incompletas o no configuradas.';
    }

    $eventData = [
        'message' => 'QA realtime handshake',
        'timestamp' => now()->toIso8601String(),
    ];

    $body = json_encode([
        'name' => 'env-updated',
        'channels' => ['qa-tests'],
        'data' => json_encode($eventData),
    ], JSON_THROW_ON_ERROR);

    $queryParams = [
        'auth_key' => $key,
        'auth_timestamp' => time(),
        'auth_version' => '1.0',
        'body_md5' => md5($body),
    ];
    ksort($queryParams);

    $path = sprintf('/apps/%s/events', $appId);
    $signaturePayload = sprintf(
        "POST\n%s\n%s",
        $path,
        http_build_query($queryParams)
    );

    $queryParams['auth_signature'] = hash_hmac('sha256', $signaturePayload, $secret);

    $url = sprintf(
        'https://api-%s.pusher.com%s?%s',
        $cluster,
        $path,
        http_build_query($queryParams)
    );

    $response = Http::withHeaders(['Content-Type' => 'application/json'])
        ->timeout(10)
        ->send('POST', $url, ['body' => $body]);

    if ($response->failed()) {
        throw new \RuntimeException('HTTP '.$response->status().' al llamar a la API de Pusher.');
    }

    return 'Evento HTTP enviado a qa-tests/env-updated.';
});

$run('Mixpanel track', function (): string {
    $config = config('telemetry.mixpanel');
    $enabled = (bool) ($config['enabled'] ?? false);
    $token = $config['project_token'] ?? null;
    $endpoint = $config['endpoint'] ?? 'https://api.mixpanel.com/track';

    if (! $enabled || ! $token) {
        return 'Mixpanel deshabilitado o sin token.';
    }

    $payload = [
        'event' => 'qa_env_check',
        'properties' => [
            'token' => $token,
            'distinct_id' => 'qa-env-smoke',
            'integration' => 'cli-smoke',
            'time' => now()->timestamp,
        ],
    ];

    $response = Http::asForm()
        ->timeout(10)
        ->post($endpoint, [
            'data' => base64_encode(json_encode($payload)),
        ]);

    if ($response->failed() || trim($response->body()) !== '1') {
        throw new \RuntimeException('Respuesta inesperada de Mixpanel: '.$response->body());
    }

    return 'Evento qa_env_check registrado.';
});

$run('reCAPTCHA verify', function () use ($envValue): string {
    $secret = $envValue('RECAPTCHA_SECRET_KEY');

    if (! $secret) {
        return 'Clave secreta no configurada.';
    }

    $response = Http::asForm()
        ->timeout(10)
        ->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secret,
            'response' => 'qa-test-token',
        ]);

    if ($response->failed()) {
        throw new \RuntimeException('HTTP '.$response->status().' al contactar reCAPTCHA.');
    }

    $json = $response->json();
    $success = (bool) ($json['success'] ?? false);

    return 'Respuesta recibida (success='.$success.').';
});

$run('Make webhook', function (): string {
    $config = config('services.make');
    $url = $config['webhook_url'] ?? null;
    $secret = $config['secret'] ?? null;

    if (! $url || ! $secret) {
        return 'Webhook no configurado.';
    }

    $response = Http::withHeaders([
        'X-Signature' => $secret,
    ])
        ->timeout(10)
        ->post($url, [
            'event' => 'qa_env_check',
            'sent_at' => now()->toIso8601String(),
        ]);

    if ($response->failed()) {
        throw new \RuntimeException('HTTP '.$response->status().' al disparar Make.');
    }

    return 'Escenario Make recibió payload (status '.$response->status().').';
});

$run('Discord webhook', function (): string {
    $config = config('services.discord');
    $url = $config['webhook_url'] ?? null;

    if (! $url) {
        return 'Webhook no configurado.';
    }

    $payload = [
        'content' => sprintf('QA env smoke %s', now()->toIso8601String()),
        'username' => $config['username'] ?: 'LTS QA Bot',
    ];

    $response = Http::timeout(10)->post($url, $payload);

    if ($response->failed()) {
        throw new \RuntimeException('HTTP '.$response->status().' al disparar Discord.');
    }

    return 'Mensaje enviado a Discord.';
});

$run('PayPal token', function (): string {
    $config = config('services.paypal');
    $clientId = $config['client_id'] ?? null;
    $clientSecret = $config['client_secret'] ?? null;
    $mode = strtolower($config['mode'] ?? 'sandbox');
    $baseUrl = $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

    if (! $clientId || ! $clientSecret) {
        return 'Credenciales PayPal incompletas.';
    }

    $response = Http::withBasicAuth($clientId, $clientSecret)
        ->asForm()
        ->timeout(10)
        ->post($baseUrl.'/v1/oauth2/token', [
            'grant_type' => 'client_credentials',
        ]);

    if ($response->failed()) {
        throw new \RuntimeException('HTTP '.$response->status().' al solicitar token.');
    }

    $json = $response->json();

    if (! isset($json['access_token'])) {
        throw new \RuntimeException('Token no presente en la respuesta de PayPal.');
    }

    return 'Token '.$mode.' recibido (expires_in='.$json['expires_in'].').';
});

$run('Sentry self-test', function () use ($envValue): string {
    if (! $envValue('SENTRY_LARAVEL_DSN')) {
        return 'DSN no configurado.';
    }

    $commands = Artisan::all();

    if (! class_exists(\Sentry\State\Hub::class) || ! array_key_exists('sentry:test', $commands)) {
        return 'SDK de Sentry no instalado en esta build.';
    }

    Artisan::call('sentry:test');

    return 'Comando sentry:test ejecutado (verificar issue en dashboard).';
});

echo PHP_EOL.'Resumen:'.PHP_EOL;

foreach ($results as $result) {
    echo sprintf('- [%s] %s → %s', strtoupper($result['status']), $result['label'], $result['message']).PHP_EOL;
}


