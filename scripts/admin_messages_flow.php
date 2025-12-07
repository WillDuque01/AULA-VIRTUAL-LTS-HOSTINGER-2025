<?php

declare(strict_types=1);

use App\Models\Message;
use App\Models\MessageRecipient;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

function logMessages(string $message): void
{
    echo '['.now()->toDateTimeString()."] {$message}".PHP_EOL;
}

$admin = User::where('email', 'admin.qa@letstalkspanish.io')->first();
$teacher = User::role('teacher')->first();
$student = User::role('student_paid')->first();

if (! $admin || ! $teacher || ! $student) {
    logMessages('Faltan usuarios para el flujo de mensajes.');

    return;
}

Auth::login($admin);

$message = Message::create([
    'sender_id' => $admin->id,
    'type' => 'announcement',
    'subject' => 'Actualización QA',
    'body' => 'Mensaje generado por admin_messages_flow para validar el Message Center.',
    'notify_email' => true,
    'metadata' => ['origin' => 'admin_messages_flow'],
    'sent_at' => now(),
]);

MessageRecipient::insert([
    [
        'message_id' => $message->id,
        'user_id' => $teacher->id,
        'status' => 'sent',
        'metadata' => json_encode(['role' => 'teacher']),
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'message_id' => $message->id,
        'user_id' => $student->id,
        'status' => 'sent',
        'metadata' => json_encode(['role' => 'student']),
        'created_at' => now(),
        'updated_at' => now(),
    ],
]);

logMessages("Mensaje {$message->uuid} enviado a {$teacher->email} y {$student->email}.");

Auth::logout();
Auth::login($teacher);

$reply = Message::create([
    'sender_id' => $teacher->id,
    'parent_id' => $message->id,
    'type' => 'reply',
    'subject' => 'Re: Actualización QA',
    'body' => 'Recibido, procederé con las pruebas docentes.',
    'notify_email' => false,
    'sent_at' => now(),
]);

MessageRecipient::create([
    'message_id' => $reply->id,
    'user_id' => $admin->id,
    'status' => 'sent',
]);

logMessages("Teacher respondió en el hilo (mensaje {$reply->id}).");

Auth::logout();
Auth::login($student);

$recipient = MessageRecipient::where('message_id', $message->id)
    ->where('user_id', $student->id)
    ->first();

if ($recipient) {
    $recipient->markAsRead();
    logMessages("Estudiante {$student->email} marcó como leído el mensaje {$message->uuid}.");
}

$counts = MessageRecipient::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
logMessages('Estado de recipients: '.json_encode($counts));

logMessages('Flujo Admin Messages finalizado.');

