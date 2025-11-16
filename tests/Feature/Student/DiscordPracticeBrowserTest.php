<?php

namespace Tests\Feature\Student;

use App\Livewire\Student\DiscordPracticeBrowser;
use App\Notifications\DiscordPracticeSlotAvailableNotification;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class DiscordPracticeBrowserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('notifications');

        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_pack_reminder_is_exposed_when_notification_exists(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => DiscordPracticeSlotAvailableNotification::class,
            'data' => [
                'title' => 'Slot B2',
                'start_at' => now()->addDay()->toIso8601String(),
                'practice_url' => 'https://example.test/practices',
                'packs_url' => 'https://example.test/dashboard#practice-packs',
                'pack_recommendation' => [
                    'id' => 99,
                    'title' => 'Pack intensivo',
                    'sessions' => 3,
                    'price_amount' => 90,
                    'currency' => 'USD',
                    'price_per_session' => 30,
                    'requires_package' => true,
                    'has_order' => false,
                ],
            ],
        ]);

        Livewire::test(DiscordPracticeBrowser::class)
            ->assertSet('packReminder.pack.title', 'Pack intensivo');
    }

    public function test_dismissing_pack_reminder_marks_notification_as_read(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $notification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => DiscordPracticeSlotAvailableNotification::class,
            'data' => [
                'title' => 'Slot B2',
                'start_at' => now()->addDay()->toIso8601String(),
                'practice_url' => 'https://example.test/practices',
                'packs_url' => 'https://example.test/dashboard#practice-packs',
                'pack_recommendation' => [
                    'id' => 1,
                    'title' => 'Pack exprés',
                    'sessions' => 2,
                    'price_amount' => 60,
                    'currency' => 'USD',
                    'price_per_session' => 30,
                    'requires_package' => true,
                    'has_order' => false,
                ],
            ],
        ]);

        Livewire::test(DiscordPracticeBrowser::class)
            ->call('dismissPackReminder')
            ->assertSet('packReminder', null);

        $this->assertNotNull($notification->fresh()->read_at);
    }
}


