<?php
// [AGENTE: OPUS 4.5] - Script temporal para diagnóstico

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::take(15)->get(['id', 'name', 'email']);

echo "=== USUARIOS DISPONIBLES ===\n";
foreach ($users as $user) {
    echo "ID: {$user->id} | {$user->name} | {$user->email}\n";
}

// Verificar usuarios con rol student
echo "\n=== USUARIOS CON ROL STUDENT ===\n";
$students = App\Models\User::role('student_paid')->take(5)->get(['id', 'name', 'email']);
foreach ($students as $student) {
    echo "ID: {$student->id} | {$student->name} | {$student->email}\n";
}

if ($students->isEmpty()) {
    $students = App\Models\User::role('student')->take(5)->get(['id', 'name', 'email']);
    foreach ($students as $student) {
        echo "ID: {$student->id} | {$student->name} | {$student->email}\n";
    }
}

// Verificar certificados
echo "\n=== CERTIFICADOS ===\n";
$certificates = App\Models\Certificate::with(['user', 'course'])->take(5)->get();
echo "Total certificados: " . App\Models\Certificate::count() . "\n";
foreach ($certificates as $cert) {
    echo "ID: {$cert->id} | User: {$cert->user->name} | Course: {$cert->course->slug} | Code: {$cert->code}\n";
}

// Verificar mensajes
echo "\n=== MENSAJES (Message Model) ===\n";
if (class_exists('App\Models\Message')) {
    $messages = App\Models\Message::count();
    echo "Total mensajes: {$messages}\n";
} else {
    echo "Model Message no existe\n";
}

// Verificar notificaciones
echo "\n=== NOTIFICACIONES ===\n";
$notifications = DB::table('notifications')->count();
echo "Total notificaciones: {$notifications}\n";

// Verificar progreso de cursos
echo "\n=== PROGRESO DE CURSOS ===\n";
$progress = App\Models\CourseProgress::with(['user', 'course'])->take(5)->get();
echo "Total registros de progreso: " . App\Models\CourseProgress::count() . "\n";
foreach ($progress as $p) {
    echo "User: {$p->user->name} | Course: {$p->course->slug} | Percent: {$p->percent}%\n";
}

// Verificar configuración de email
echo "\n=== CONFIG EMAIL ===\n";
echo "MAIL_MAILER: " . config('mail.default') . "\n";
echo "MAIL_HOST: " . config('mail.mailers.smtp.host') . "\n";
echo "MAIL_FROM: " . config('mail.from.address') . "\n";
