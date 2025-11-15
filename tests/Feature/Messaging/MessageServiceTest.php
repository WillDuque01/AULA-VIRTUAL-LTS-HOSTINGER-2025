<?php

namespace Tests\Feature\Messaging;

use App\Models\User;
use App\Notifications\TeacherMessageNotification;
use App\Support\Messaging\MessageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MessageServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_send_message_to_students_and_trigger_notification(): void
    {
        Notification::fake();

        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $studentRole = Role::firstOrCreate(['name' => 'student_free']);

        $teacher = User::factory()->create();
        $teacher->assignRole($teacherRole);

        $student = User::factory()->create();
        $student->assignRole($studentRole);

        $service = app(MessageService::class);

        $message = $service->send($teacher, collect([$student]), [
            'subject' => 'Recordatorio de sesión',
            'body' => 'Recuerda tu sesión en vivo mañana.',
            'notify_email' => true,
        ]);

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'subject' => 'Recordatorio de sesión',
        ]);

        $this->assertDatabaseHas('message_recipients', [
            'message_id' => $message->id,
            'user_id' => $student->id,
        ]);

        Notification::assertSentTo($student, TeacherMessageNotification::class);
    }
}
