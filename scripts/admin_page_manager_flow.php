<?php

declare(strict_types=1);

use App\Models\Page;
use App\Models\PageRevision;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

function logPageManager(string $message): void
{
    echo '['.now()->toDateTimeString()."] {$message}".PHP_EOL;
}

$admin = User::where('email', 'admin.qa@letstalkspanish.io')->first();

if (! $admin || ! $admin->hasRole('Admin')) {
    logPageManager('Admin QA no disponible para Page Manager.');

    return;
}

Auth::login($admin);

$page = Page::with('revisions')->latest('id')->first();

if (! $page) {
    $page = Page::create([
        'title' => 'QA Manager Landing',
        'slug' => 'qa-manager-landing',
        'type' => 'landing',
        'locale' => 'es',
        'status' => 'draft',
    ]);

    $page->revisions()->create([
        'label' => 'Inicial',
        'layout' => [],
        'settings' => [],
        'author_id' => $admin->id,
    ]);

    logPageManager("Se creó una página base (ID {$page->id}).");
}

$clone = $page->replicate(['slug', 'status', 'published_revision_id']);
$clone->slug = $page->slug.'-clone-'.Str::lower(Str::random(3));
$clone->title = $page->title.' Clone';
$clone->status = 'draft';
$clone->save();

if ($page->latestRevision) {
    PageRevision::create([
        'page_id' => $clone->id,
        'label' => 'Clonado',
        'layout' => $page->latestRevision->layout,
        'settings' => $page->latestRevision->settings,
        'author_id' => $admin->id,
    ]);
}

logPageManager("Clon generada: {$clone->slug} (ID {$clone->id}).");

$page->update([
    'status' => 'archived',
    'meta' => array_merge($page->meta ?? [], [
        'seo_title' => 'QA Archived Page',
        'seo_description' => 'Página administrada para pruebas de Page Manager',
    ]),
]);

logPageManager("Página {$page->id} movida a archived y meta SEO actualizada.");

$clone->update(['status' => 'published']);
logPageManager("Clon {$clone->id} publicada.");

$trashCandidate = Page::where('id', '!=', $page->id)->latest('id')->first();
if ($trashCandidate) {
    $trashCandidate->delete();
    logPageManager("Página {$trashCandidate->id} enviada a papelera.");
}

$counts = [
    'draft' => Page::where('status', 'draft')->count(),
    'published' => Page::where('status', 'published')->count(),
    'archived' => Page::where('status', 'archived')->count(),
];
logPageManager('Resumen de páginas por estado: '.json_encode($counts));

logPageManager('Flujo Admin Page Manager finalizado.');

