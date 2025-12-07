<?php

declare(strict_types=1);

use App\Models\PracticePackage;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

function logCatalog(string $message): void
{
    echo '['.now()->toDateTimeString()."] {$message}".PHP_EOL;
}

$admin = User::where('email', 'admin.qa@letstalkspanish.io')->first();

if (! $admin || ! $admin->hasRole('Admin')) {
    logCatalog('Admin QA no disponible para Product Catalog.');

    return;
}

Auth::login($admin);

$teacher = User::role('teacher')->first();

if (! $teacher) {
    logCatalog('No hay teacher disponible para crear practice packages.');

    return;
}

$package = PracticePackage::create([
    'creator_id' => $teacher->id,
    'lesson_id' => null,
    'title' => 'QA Catalog Pack '.Str::upper(Str::random(2)),
    'subtitle' => 'Generado por admin_product_catalog_flow.',
    'description' => 'Visible en Product Catalog para pruebas.',
    'sessions_count' => 3,
    'price_amount' => 129,
    'price_currency' => 'USD',
    'is_global' => true,
    'visibility' => 'public',
    'delivery_platform' => 'discord',
    'delivery_url' => 'https://discord.gg/qa-lab',
    'status' => 'published',
    'meta' => ['thumbnail_path' => '/images/qa-pack.png'],
]);

$product = $package->product;
logCatalog("Producto publicado via PracticePackage: {$product->slug} (ID {$product->id}).");

$product->update([
    'price_amount' => 119,
    'compare_at_amount' => 149,
    'is_featured' => true,
]);

logCatalog("Producto {$product->id} actualizado (precio 119, featured true).");

$package->update(['status' => 'draft']);
logCatalog("Package {$package->id} movido a draft para validar estados.");

$package->delete();
logCatalog("Package {$package->id} eliminado (producto pasa a papelera).");

$summary = [
    'published_packages' => PracticePackage::where('status', 'published')->count(),
    'draft_packages' => PracticePackage::where('status', 'draft')->count(),
    'products_featured' => optional($product)->newQuery()->where('is_featured', true)->count(),
];

logCatalog('Resumen del catálogo: '.json_encode($summary));
logCatalog('Flujo Admin Product Catalog finalizado.');

