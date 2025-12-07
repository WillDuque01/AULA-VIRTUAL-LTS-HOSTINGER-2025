<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

require __DIR__.'/../vendor/autoload.php';

$baseUrl = 'https://app.letstalkspanish.io';
$locale = 'es';

$client = new Client([
    'base_uri' => $baseUrl,
    'verify' => '/etc/ssl/certs/ca-certificates.crt',
    'timeout' => 30,
    'http_errors' => false,
    'headers' => [
        'User-Agent' => 'LTS-Register-Smoke/2025-11',
        'Accept-Language' => 'es-ES,es;q=0.9,en;q=0.8',
    ],
]);

$jar = new CookieJar();

$response = $client->get("/{$locale}/register", [
    'cookies' => $jar,
    'allow_redirects' => true,
]);

$body = (string) $response->getBody();

if (! preg_match('/name=\"_token\" value=\"([^\"]+)\"/', $body, $matches)) {
    fwrite(STDERR, "[ERROR] No se encontró token CSRF en el formulario.\n");

    return;
}

$token = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5);
$timestamp = date('YmdHis');
$email = sprintf('qa.register+%s@letstalkspanish.io', $timestamp);
$name = sprintf('QA Register %s', $timestamp);
$password = 'StudentQA2025!';

$registerResp = $client->post("/{$locale}/register", [
    'cookies' => $jar,
    'allow_redirects' => false,
    'form_params' => [
        '_token' => $token,
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'password_confirmation' => $password,
    ],
    'headers' => [
        'Referer' => "/{$locale}/register",
    ],
]);

$status = $registerResp->getStatusCode();
$location = $registerResp->getHeaderLine('Location');

$result = [
    'executed_at' => date(DATE_ATOM),
    'email' => $email,
    'status' => $status,
    'location' => $location,
];

if ($status !== 302) {
    $result['error_body'] = (string) $registerResp->getBody();
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), PHP_EOL;


