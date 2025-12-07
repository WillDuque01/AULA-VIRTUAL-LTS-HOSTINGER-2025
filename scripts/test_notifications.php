<?php
// [AGENTE: OPUS 4.5] - Script para probar todas las notificaciones por email

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Course;
use App\Models\Certificate;
use App\Models\DiscordPractice;
use App\Models\PracticePackage;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Notifications\CertificateIssuedNotification;
use App\Notifications\CourseUnlockedNotification;
use App\Notifications\ModuleUnlockedNotification;
use App\Notifications\DiscordPracticeReservedNotification;
use App\Notifications\DiscordPracticeScheduledNotification;
use App\Notifications\PracticePackagePurchasedNotification;
use App\Notifications\AssignmentApprovedNotification;
use App\Notifications\AssignmentRejectedNotification;
use App\Notifications\SimulatedPaymentNotification;
use App\Notifications\ProfileCompletionReminderNotification;
use App\Notifications\StudentMessageNotification;
use Illuminate\Support\Facades\Notification;

// Email de destino para pruebas
$testEmail = $argv[1] ?? 'wilsabduque@gmail.com';

echo "=== TEST DE NOTIFICACIONES POR EMAIL ===\n";
echo "Enviando a: {$testEmail}\n\n";

// Crear usuario temporal para pruebas o usar existente
$testUser = User::firstOrCreate(
    ['email' => $testEmail],
    [
        'name' => 'Test User QA',
        'password' => bcrypt('TestQA2025!'),
        'email_verified_at' => now(),
    ]
);

echo "Usuario de prueba: ID {$testUser->id} | {$testUser->name}\n\n";

// Obtener datos de prueba
$course = Course::first();
$certificate = Certificate::first();
$practice = DiscordPractice::first();
$package = PracticePackage::first();

$results = [];

// 1. CERTIFICADO EMITIDO
echo "1. CertificateIssuedNotification... ";
try {
    if ($certificate) {
        $testUser->notify(new CertificateIssuedNotification($certificate));
        $results['CertificateIssued'] = '✅ Enviado';
        echo "✅\n";
    } else {
        $results['CertificateIssued'] = '⚠️ Sin datos de prueba';
        echo "⚠️ (sin certificado)\n";
    }
} catch (\Throwable $e) {
    $results['CertificateIssued'] = '❌ Error: ' . $e->getMessage();
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// 2. CURSO DESBLOQUEADO
echo "2. CourseUnlockedNotification... ";
try {
    if ($course) {
        $testUser->notify(new CourseUnlockedNotification(
            $course,
            $course->slug ?? 'Español A1',
            'Curso completo de español nivel A1',
            'Estudiantes principiantes',
            url('/courses/' . ($course->slug ?? 'espanol-a1')),
            '¡Felicitaciones! Se ha desbloqueado un nuevo curso para ti.'
        ));
        $results['CourseUnlocked'] = '✅ Enviado';
        echo "✅\n";
    } else {
        $results['CourseUnlocked'] = '⚠️ Sin datos de prueba';
        echo "⚠️ (sin curso)\n";
    }
} catch (\Throwable $e) {
    $results['CourseUnlocked'] = '❌ Error: ' . $e->getMessage();
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// 3. RECORDATORIO DE PERFIL
echo "3. ProfileCompletionReminderNotification... ";
try {
    $profileSummary = [
        'percent' => 60,
        'steps' => [
            ['label' => 'Foto de perfil', 'completed' => true],
            ['label' => 'Teléfono', 'completed' => false],
            ['label' => 'País', 'completed' => false],
        ],
    ];
    $testUser->notify(new ProfileCompletionReminderNotification($profileSummary));
    $results['ProfileReminder'] = '✅ Enviado';
    echo "✅\n";
} catch (\Throwable $e) {
    $results['ProfileReminder'] = '❌ Error: ' . $e->getMessage();
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// 4. PAGO SIMULADO - Saltamos por ahora (requiere Subscription model)
echo "4. SimulatedPaymentNotification... ";
$results['SimulatedPayment'] = '⏭️ Saltado (requiere Subscription)';
echo "⏭️ (requiere modelo Subscription)\n";

// 5. PRÁCTICA PROGRAMADA
echo "5. DiscordPracticeScheduledNotification... ";
try {
    if ($practice) {
        $testUser->notify(new DiscordPracticeScheduledNotification($practice));
        $results['PracticeScheduled'] = '✅ Enviado';
        echo "✅\n";
    } else {
        $results['PracticeScheduled'] = '⚠️ Sin datos de prueba';
        echo "⚠️ (sin práctica)\n";
    }
} catch (\Throwable $e) {
    $results['PracticeScheduled'] = '❌ Error: ' . $e->getMessage();
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// 6. PAQUETE COMPRADO
echo "6. PracticePackagePurchasedNotification... ";
try {
    // Buscar una orden real
    $order = App\Models\PracticePackageOrder::first();
    if ($order) {
        $testUser->notify(new PracticePackagePurchasedNotification($order));
        $results['PackagePurchased'] = '✅ Enviado';
        echo "✅\n";
    } else {
        $results['PackagePurchased'] = '⚠️ Sin órdenes de prueba';
        echo "⚠️ (sin orden)\n";
    }
} catch (\Throwable $e) {
    $results['PackagePurchased'] = '❌ Error: ' . $e->getMessage();
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// 7. MENSAJE DE ESTUDIANTE
echo "7. StudentMessageNotification... ";
try {
    // Buscar un mensaje real
    $message = App\Models\Message::first();
    if ($message) {
        $testUser->notify(new StudentMessageNotification($message));
        $results['StudentMessage'] = '✅ Enviado';
        echo "✅\n";
    } else {
        $results['StudentMessage'] = '⚠️ Sin mensajes de prueba';
        echo "⚠️ (sin mensaje)\n";
    }
} catch (\Throwable $e) {
    $results['StudentMessage'] = '❌ Error: ' . $e->getMessage();
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// RESUMEN
echo "\n=== RESUMEN DE PRUEBAS ===\n";
foreach ($results as $notification => $status) {
    echo "{$notification}: {$status}\n";
}

$successful = count(array_filter($results, fn($s) => str_starts_with($s, '✅')));
$total = count($results);

echo "\n📊 Resultado: {$successful}/{$total} notificaciones enviadas exitosamente\n";
echo "\n📧 Revisa tu bandeja de entrada en: {$testEmail}\n";
echo "   (También revisa la carpeta de SPAM)\n";

