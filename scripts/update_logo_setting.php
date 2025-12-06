<?php
// [AGENTE: OPUS 4.5] - Actualizar BrandingSettings con logo correcto
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$settings = app(\App\Settings\BrandingSettings::class);
$oldLogo = $settings->logo_url;
$settings->logo_url = '/images/logo.png';
$settings->save();

echo "Logo URL actualizado:\n";
echo "  Anterior: $oldLogo\n";
echo "  Nuevo: {$settings->logo_url}\n";

