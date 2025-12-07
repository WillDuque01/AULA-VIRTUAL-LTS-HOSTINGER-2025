<?php
// [AGENTE: OPUS 4.5] - Script temporal para actualizar contraseñas QA

require __DIR__.'/../vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$password = 'AuditorQA2025!';
$hashedPassword = Hash::make($password);

$emails = [
    'academy@letstalkspanish.io',
    'teacher.admin.qa@letstalkspanish.io',
    'student.paid@letstalkspanish.io',
    'student.pending@letstalkspanish.io',
    'student.waitlist@letstalkspanish.io',
    'admin.qa@letstalkspanish.io',
    'student.qa01@letstalkspanish.io',
    'student.qa02@letstalkspanish.io',
    'student.qa03@letstalkspanish.io',
];

echo "=== ACTUALIZANDO CONTRASEÑAS QA ===\n";
echo "Nueva contraseña: {$password}\n\n";

$updated = 0;
foreach ($emails as $email) {
    $user = User::where('email', $email)->first();
    if ($user) {
        $user->password = $hashedPassword;
        $user->save();
        echo "✅ {$email}\n";
        $updated++;
    } else {
        echo "⏭️ {$email} (no encontrado)\n";
    }
}

echo "\n📊 Resultado: {$updated} contraseñas actualizadas\n";
echo "🔐 Contraseña: AuditorQA2025!\n";

