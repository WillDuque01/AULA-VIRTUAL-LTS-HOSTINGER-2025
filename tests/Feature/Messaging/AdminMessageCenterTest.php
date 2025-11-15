<?php

namespace Tests\Feature\Messaging;

use App\Livewire\Admin\MessageCenter;
use App\Models\User;
use App\Notifications\TeacherMessageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminMessageCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_send_message_from_component(): void
    {
        Notification::fake();

        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $studentRole = Role::firstOrCreate(['name' => 'student_free']);

        $teacher = User::factory()->create();
        $teacher->assignRole($teacherRole);

        $student = User::factory()->create();
        $student->assignRole($studentRole);

        $this->actingAs($teacher);

        Livewire::test(MessageCenter::class)
            ->call('compose')
            ->set('target', 'students_all')
            ->set('subject', 'Bienvenida')
            ->set('body', 'Hola equipo, recuerden revisar el nuevo módulo.')
            ->set('notifyEmail', true)
            ->call('send')
            ->assertHasNoErrors()
            ->assertSee(__('Mensaje enviado correctamente.'));

        $this->assertDatabaseCount('messages', 1);
        Notification::assertSentTo($student, TeacherMessageNotification::class);
    }
}
