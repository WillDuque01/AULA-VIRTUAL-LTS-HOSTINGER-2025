<?php

declare(strict_types=1);

use App\Models\Page;
use App\Models\User;
use App\Services\PageBuilderService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

function logPages(string $message): void
{
    echo '['.now()->toDateTimeString()."] {$message}".PHP_EOL;
}

$admin = User::where('email', 'admin.qa@letstalkspanish.io')->first();

if (! $admin || ! $admin->hasRole('Admin')) {
    logPages('Admin QA no disponible para Page Builder.');

    return;
}

Auth::login($admin);

/** @var PageBuilderService $builder */
$builder = app(PageBuilderService::class);

$layout = [
    [
        'type' => 'hero',
        'title' => 'QA Landing Hero',
        'subtitle' => 'Página generada por admin_page_builder_flow',
        'cta' => [
            'label' => 'Explorar pruebas',
            'url' => '/es/catalog',
        ],
    ],
    [
        'type' => 'features',
        'items' => [
            ['title' => 'Cobertura total', 'description' => '55 flujos QA automatizados.'],
            ['title' => 'Integraciones listas', 'description' => 'Provisioner + Outbox validados.'],
        ],
    ],
];

$settings = [
    'theme' => 'slate',
    'show_nav' => true,
];

$page = $builder->createPage([
    'title' => 'QA Landing '.Str::upper(Str::random(2)),
    'slug' => 'qa-landing-'.Str::lower(Str::random(4)),
    'type' => 'landing',
    'locale' => 'es',
    'status' => 'draft',
], $layout, $settings, $admin->id);

logPages("Página creada: {$page->slug} (ID {$page->id}).");

$draft = $builder->saveDraft($page, [
    'label' => 'QA Iteration',
    'layout' => array_merge($layout, [[
        'type' => 'cta',
        'title' => 'Inicia tus pruebas',
        'subtitle' => 'Todo listo para la simulación multi-rol.',
        'button' => ['label' => 'Ir al dashboard', 'url' => '/es/dashboard'],
    ]]),
    'settings' => array_merge($settings, ['show_footer' => true]),
], $admin->id);

logPages("Nuevo draft guardado: {$draft->label} (rev ID {$draft->id}).");

$builder->publish($page, $draft);
$page->refresh();

logPages("Página publicada con revision {$page->published_revision_id}.");

$publishedCount = Page::published()->count();
logPages("Total de páginas publicadas: {$publishedCount}.");

logPages('Flujo Admin Page Builder finalizado.');

