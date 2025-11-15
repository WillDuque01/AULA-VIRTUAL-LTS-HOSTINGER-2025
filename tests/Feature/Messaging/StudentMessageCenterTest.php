<?php

namespace Tests\Feature\Messaging;

use App\Livewire\Student\MessageCenter;
use App\Models\User;
use App\Notifications\StudentMessageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentMessageCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_message_teachers(): void
    {
        Notification::fake();

        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $studentRole = Role::firstOrCreate(['name' => 'student_free']);

        $teacher = User::factory()->create();
        $teacher->assignRole($teacherRole);

        $student = User::factory()->create();
        $student->assignRole($studentRole);

        $this->actingAs($student);

        Livewire::test(MessageCenter::class)
            ->call('compose')
            ->set('target', 'teacher_team')
            ->set('subject', 'Duda sobre la tarea')
            ->set('body', '¿Podrían aclarar la rúbrica del proyecto final?')
            ->call('send')
            ->assertHasNoErrors()
            ->assertSee(__('Mensaje enviado correctamente.'));

        $this->assertDatabaseCount('messages', 1);
        Notification::assertSentTo($teacher, StudentMessageNotification::class);
    }
}
