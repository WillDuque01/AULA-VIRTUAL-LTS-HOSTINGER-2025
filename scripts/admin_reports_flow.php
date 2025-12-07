<?php

declare(strict_types=1);

use App\Models\AssignmentSubmission;
use App\Models\CohortTemplate;
use App\Models\DiscordPractice;
use App\Models\IntegrationEvent;
use App\Models\PaymentEvent;
use App\Models\PracticePackageOrder;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

function logReports(string $message): void
{
    echo '['.now()->toDateTimeString()."] {$message}".PHP_EOL;
}

$admin = User::where('email', 'admin.qa@letstalkspanish.io')->first();

if (! $admin || ! $admin->hasRole('Admin')) {
    logReports('Admin QA no disponible para reports.');

    return;
}

Auth::login($admin);

$assignmentStats = AssignmentSubmission::selectRaw('status, COUNT(*) as total')
    ->groupBy('status')
    ->pluck('total', 'status');

$orderTotals = PracticePackageOrder::selectRaw('status, COUNT(*) as total')
    ->groupBy('status')
    ->pluck('total', 'status');

$practiceStats = DiscordPractice::selectRaw('status, COUNT(*) as total')
    ->groupBy('status')
    ->pluck('total', 'status');

$cohortStats = CohortTemplate::selectRaw('status, COUNT(*) as total')
    ->groupBy('status')
    ->pluck('total', 'status');

$paymentStats = PaymentEvent::selectRaw('status, COUNT(*) as total')
    ->groupBy('status')
    ->pluck('total', 'status');

$outboxStats = IntegrationEvent::selectRaw('status, COUNT(*) as total')
    ->groupBy('status')
    ->pluck('total', 'status');

logReports('Assignments status: '.json_encode($assignmentStats));
logReports('Practice package orders: '.json_encode($orderTotals));
logReports('Discord practices: '.json_encode($practiceStats));
logReports('Cohort templates: '.json_encode($cohortStats));
logReports('Payment events: '.json_encode($paymentStats));
logReports('Integration outbox: '.json_encode($outboxStats));

logReports('Flujo Admin Reports finalizado.');

