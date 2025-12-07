<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Models\User;

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = User::where('email', 'student@letstalkspanish.io')->first();
if ($user) {
    $user->password = bcrypt('AuditorQA2025!');
    $user->save();
    echo "✅ Password actualizado para: " . $user->email . "\n";
} else {
    echo "❌ Usuario no encontrado\n";
}

