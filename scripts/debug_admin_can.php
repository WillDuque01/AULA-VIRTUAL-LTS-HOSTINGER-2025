<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Throwable;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$user = User::where('email', 'admin.qa@letstalkspanish.io')->first();

if (! $user) {
    echo "Admin QA no existe".PHP_EOL;
    exit(1);
}

$roles = $user->getRoleNames()->toArray();
echo 'Roles: '.implode(', ', $roles).PHP_EOL;
echo 'hasRole(Admin)? '.($user->hasRole('Admin') ? 'sí' : 'no').PHP_EOL;

try {
    $canManage = $user->can('manage-settings');
    echo 'manage-settings? '.($canManage ? 'sí' : 'no').PHP_EOL;
} catch (Throwable $exception) {
    echo 'manage-settings? error -> '.$exception->getMessage().PHP_EOL;
}

