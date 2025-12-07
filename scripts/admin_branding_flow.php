<?php

declare(strict_types=1);

use App\Models\User;
use App\Settings\BrandingSettings;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$admin = User::where('email', 'admin.qa@letstalkspanish.io')->first();

if (! $admin || ! $admin->hasRole('Admin')) {
    echo '['.now()->toDateTimeString()."] Admin QA no disponible.\n";

    return;
}

Auth::login($admin);

/** @var BrandingSettings $settings */
$settings = app(BrandingSettings::class);

$palette = [
    'primary_color' => '#111927',
    'secondary_color' => '#0ea5e9',
    'accent_color' => '#f59e0b',
    'neutral_color' => '#c7cfdc',
    'font_family' => 'Clash Display, "Space Grotesk", system-ui',
    'body_font_family' => 'Inter, "Segoe UI", system-ui',
    'type_scale_ratio' => '1.2',
    'base_font_size' => '1rem',
    'line_height' => '1.58',
    'letter_spacing' => '0.01em',
    'spacing_unit' => '0.625rem',
    'border_radius' => '0.85rem',
    'shadow_soft' => '0 20px 50px rgba(15,23,42,0.12)',
    'shadow_bold' => '0 35px 80px rgba(8,47,73,0.35)',
    'container_max_width' => '1280px',
    'dark_mode' => false,
    'logo_url' => 'https://cdn.letstalkspanish.io/qa/logo-horizontal.png',
    'logo_text' => 'LTS QA Academy',
    'logo_mode' => 'image',
    'logo_svg' => '',
    'logo_horizontal_path' => '',
    'logo_square_path' => '',
];

foreach ($palette as $key => $value) {
    $settings->{$key} = $value;
}

$settings->save();

cache()->forget('branding.info');

echo '['.now()->toDateTimeString()."] Branding actualizado por admin_branding_flow.\n";

