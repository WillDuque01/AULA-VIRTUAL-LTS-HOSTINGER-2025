<?php
// [AGENTE: OPUS 4.5] - Script temporal para listar usuarios y roles
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$users = App\Models\User::with('roles')->get();

echo "ID | Nombre | Email | Roles\n";
echo str_repeat('-', 80) . "\n";

foreach ($users as $user) {
    $roles = $user->roles->pluck('name')->join(', ');
    echo "{$user->id} | {$user->name} | {$user->email} | {$roles}\n";
}

