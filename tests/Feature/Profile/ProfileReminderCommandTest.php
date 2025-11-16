<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use App\Notifications\ProfileCompletionReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileReminderCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('teacher');
    }

    public function test_command_sends_reminders_to_incomplete_profiles(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'profile_completion_score' => 40,
            'profile_completed_at' => null,
            'profile_last_reminded_at' => now()->subDays(10),
        ]);

        $this->artisan('profile:remind-incomplete --threshold=80')
            ->expectsOutput('Recordatorios enviados: 1')
            ->assertExitCode(0);

        Notification::assertSentTo([$user], ProfileCompletionReminderNotification::class);
        $this->assertNotNull($user->fresh()->profile_last_reminded_at);
    }
}

