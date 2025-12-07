<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\Provisioning\CredentialProvisioner;
use App\Support\Provisioning\Dto\ProvisioningMeta;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

if ($argc < 3) {
    fwrite(STDERR, "Uso: php scripts/apply_provisioning_payload.php <admin_email> <payload_json_path>\n");

    return;
}

[, $adminEmail, $jsonPath] = $argv;

if (! file_exists($jsonPath)) {
    fwrite(STDERR, "No se encontró el archivo {$jsonPath}\n");

    return;
}

$rawJson = file_get_contents($jsonPath);
$payload = json_decode($rawJson, true);

if (! is_array($payload)) {
    fwrite(STDERR, "El archivo {$jsonPath} no contiene JSON válido.\n");

    return;
}

$admin = User::where('email', $adminEmail)->first();

if (! $admin) {
    fwrite(STDERR, "No existe un usuario con el email {$adminEmail}.\n");

    return;
}

Auth::login($admin);

/** @var CredentialProvisioner $provisioner */
$provisioner = app(CredentialProvisioner::class);
$meta = ProvisioningMeta::make(
    user: $admin,
    ipAddress: '127.0.0.1',
    userAgent: 'apply_provisioning_payload',
    shouldWriteEnv: true,
    shouldCacheConfig: true,
    shouldPersistAudit: true,
);

$provisioner->apply($payload, $meta);

echo '['.now()->toDateTimeString()."] Provisioning completado para {$adminEmail} usando {$jsonPath}.\n";


