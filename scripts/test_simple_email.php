<?php
// [AGENTE: OPUS 4.5] - Script para probar envío de email simple

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

$testEmail = $argv[1] ?? 'wilsabduque@gmail.com';

echo "=== TEST DE EMAIL SIMPLE ===\n";
echo "Enviando a: {$testEmail}\n";
echo "SMTP Host: " . config('mail.mailers.smtp.host') . "\n";
echo "SMTP Port: " . config('mail.mailers.smtp.port') . "\n";
echo "From: " . config('mail.from.address') . "\n\n";

try {
    Mail::raw('Este es un email de prueba del sistema LTS Academy.', function ($message) use ($testEmail) {
        $message->to($testEmail)
                ->subject('✅ Test SMTP - LTS Academy');
    });
    
    echo "✅ Email enviado exitosamente!\n";
    echo "\n📧 Revisa tu bandeja en: {$testEmail}\n";
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nDetalles:\n" . $e->getTraceAsString() . "\n";
}

