<?php

namespace Tests\Feature;

use App\Livewire\Student\Dashboard;
use App\Models\User;
use App\Notifications\DiscordPracticeSlotAvailableNotification;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class StudentDashboardPackReminderTest extends TestCase
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

    public function test_pack_banner_is_visible_when_notification_exists(): void
    {
        $user = User::factory()->create();

        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => DiscordPracticeSlotAvailableNotification::class,
            'data' => [
                'title' => 'Conversación B2',
                'start_at' => now()->addDay()->toIso8601String(),
                'practice_url' => 'https://example.com/practices',
                'packs_url' => 'https://example.com/dashboard?pack=10#practice-packs',
                'pack_recommendation' => [
                    'id' => 10,
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

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSet('packReminder.pack.title', 'Pack intensivo')
            ->assertSet('highlightPackageId', 10)
            ->assertSee('?pack=10#practice-packs', false);
    }

    public function test_dismissing_pack_banner_marks_notification_as_read(): void
    {
        $user = User::factory()->create();

        $notification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => DiscordPracticeSlotAvailableNotification::class,
            'data' => [
                'title' => 'Slot premium',
                'start_at' => now()->addDays(2)->toIso8601String(),
                'pack_recommendation' => [
                    'id' => 11,
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

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('dismissPackReminder')
            ->assertSet('packReminder', null);

        $this->assertNotNull($notification->fresh()->read_at);
    }
}


