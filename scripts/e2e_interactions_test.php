<?php
/**
 * [AGENTE: OPUS 4.5] - Turno 35: Test E2E de Interacciones
 * 
 * Ejecuta simulaciones de:
 * - Gamificación (completar curso)
 * - Mensajería (Admin → Student, Student → Teacher)
 * - Certificados
 * - Notificaciones
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Message;
use App\Models\MessageRecipient;
use App\Models\User;
use App\Notifications\CertificateIssuedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║       TEST E2E DE INTERACCIONES - LTS Academy                    ║\n";
echo "║       " . date('Y-m-d H:i:s') . "                                       ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

// === FASE 1: PREPARACIÓN DE PERFILES ===
echo "═══════════════════════════════════════════════════════════════════\n";
echo "FASE 1: PREPARACIÓN DE PERFILES Y DATOS\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$admin = User::where('email', 'academy@letstalkspanish.io')->first();
$student = User::where('email', 'student@letstalkspanish.io')->first();
$teacher = User::where('email', 'teacher.admin.qa@letstalkspanish.io')->first();

echo "👤 Admin: " . ($admin ? "ID {$admin->id} - {$admin->name}" : "❌ No encontrado") . "\n";
echo "👤 Estudiante: " . ($student ? "ID {$student->id} - {$student->name}" : "❌ No encontrado") . "\n";
echo "👤 Teacher: " . ($teacher ? "ID {$teacher->id} - {$teacher->name}" : "❌ No encontrado") . "\n";

if (!$admin || !$student || !$teacher) {
    echo "\n❌ Error: Faltan usuarios requeridos para las pruebas.\n";
    exit(1);
}

// Verificar cursos publicados
$courses = Course::where('published', true)->get();
echo "\n📚 Cursos publicados: " . $courses->count() . "\n";
foreach ($courses->take(3) as $course) {
    echo "   - [{$course->id}] {$course->slug}\n";
}

// === FASE 2: SIMULACIÓN DE GAMIFICACIÓN ===
echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "FASE 2: SIMULACIÓN DE FLUJO DE CONTENIDO (GAMIFICACIÓN)\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$course = $courses->first();
if ($course) {
    // Verificar si ya existe certificado
    $existingCert = Certificate::where('user_id', $student->id)
        ->where('course_id', $course->id)
        ->first();
    
    if ($existingCert) {
        echo "ℹ️ Certificado ya existe para este estudiante/curso\n";
        echo "   Code: {$existingCert->code}\n";
        $certificate = $existingCert;
    } else {
        // Crear certificado (simula completar curso)
        $certificate = Certificate::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'code' => strtoupper(substr(md5(uniqid()), 0, 10)),
            'issued_at' => now(),
            'file_path' => 'certificates/test-certificate.pdf',
        ]);
        echo "✅ Certificado creado: {$certificate->code}\n";
        
        // Enviar notificación
        try {
            $student->notify(new CertificateIssuedNotification($certificate));
            echo "✅ Notificación de certificado enviada\n";
        } catch (\Exception $e) {
            echo "⚠️ Error enviando notificación: " . $e->getMessage() . "\n";
        }
    }
} else {
    echo "❌ No hay cursos publicados para simular\n";
}

// === FASE 3: SIMULACIÓN DE MENSAJERÍA ===
echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "FASE 3: GENERACIÓN DE EVENTOS DE MENSAJERÍA (INTER-ROLES)\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

// Mensaje 1: Admin → Estudiante
echo "📧 Mensaje 1: Admin → Estudiante\n";
try {
    $message1 = Message::create([
        'sender_id' => $admin->id,
        'subject' => '[E2E Test] Bienvenido a la plataforma',
        'body' => 'Este es un mensaje de prueba E2E enviado desde Admin a Estudiante. Fecha: ' . now()->toDateTimeString(),
        'sent_at' => now(),
    ]);
    
    MessageRecipient::create([
        'message_id' => $message1->id,
        'user_id' => $student->id,
        'status' => 'unread',
    ]);
    
    echo "   ✅ Mensaje creado (ID: {$message1->id})\n";
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Mensaje 2: Estudiante → Teacher
echo "📧 Mensaje 2: Estudiante → Teacher\n";
try {
    $message2 = Message::create([
        'sender_id' => $student->id,
        'subject' => '[E2E Test] Consulta sobre el curso',
        'body' => 'Este es un mensaje de prueba E2E enviado desde Estudiante a Teacher. Fecha: ' . now()->toDateTimeString(),
        'sent_at' => now(),
    ]);
    
    MessageRecipient::create([
        'message_id' => $message2->id,
        'user_id' => $teacher->id,
        'status' => 'unread',
    ]);
    
    echo "   ✅ Mensaje creado (ID: {$message2->id})\n";
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// === FASE 4: VERIFICACIÓN DE TABLAS ===
echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "FASE 4: VERIFICACIÓN DE TABLAS (BACKEND CHECK)\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

// Certificados del estudiante
$studentCerts = Certificate::where('user_id', $student->id)->count();
echo "📜 Certificados para student@: {$studentCerts}\n";

// Mensajes totales
$totalMessages = Message::count();
echo "📬 Mensajes totales en BD: {$totalMessages}\n";

// Mensajes para el estudiante
$studentMessages = MessageRecipient::where('user_id', $student->id)->count();
echo "📬 Mensajes para student@: {$studentMessages}\n";

// Mensajes para el teacher
$teacherMessages = MessageRecipient::where('user_id', $teacher->id)->count();
echo "📬 Mensajes para teacher.admin.qa@: {$teacherMessages}\n";

// Notificaciones
try {
    $studentNotifications = DB::table('notifications')
        ->where('notifiable_id', $student->id)
        ->where('notifiable_type', User::class)
        ->count();
    echo "🔔 Notificaciones para student@: {$studentNotifications}\n";
} catch (\Exception $e) {
    echo "🔔 Notificaciones: ⚠️ " . $e->getMessage() . "\n";
}

// === RESUMEN ===
echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "RESUMEN DE DATOS PARA VERIFICACIÓN VISUAL\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

echo "📋 Credenciales de prueba:\n";
echo "   - Admin: academy@letstalkspanish.io / AuditorQA2025!\n";
echo "   - Estudiante: student@letstalkspanish.io / AuditorQA2025!\n";
echo "   - Teacher: teacher.admin.qa@letstalkspanish.io / AuditorQA2025!\n";

echo "\n🔍 Verificaciones pendientes (Browser):\n";
echo "   FASE 5: Dashboard Estudiante → /en/student/dashboard\n";
echo "   FASE 6: Certificados → /en/student/certificates\n";
echo "   FASE 7: Message Center Estudiante → /en/student/messages\n";
echo "   FASE 8: Message Center Teacher → /en/admin/messages\n";

echo "\n✅ FASE 1-4 COMPLETADAS\n";
echo "══════════════════════════════════════════════════════════════════\n";

