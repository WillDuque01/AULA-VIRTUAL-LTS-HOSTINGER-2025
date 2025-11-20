<?php

namespace Tests\Feature\Professor;

use App\Livewire\Professor\DiscordPracticePlanner;
use App\Models\Chapter;
use App\Models\CohortTemplate;
use App\Models\Lesson;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlannerCohortTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('teacher');
        Role::findOrCreate('teacher_admin');
        Role::findOrCreate('Admin');
    }

    public function test_applying_cohort_template_sets_commerce_snapshot_and_publishes_on_planning(): void
    {
        $user = User::factory()->create();
        $user->assignRole('teacher');

        $course = Course::create([
            'slug' => 'english-b1',
            'level' => 1,
            'published' => true,
        ]);

        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Sprint 1',
            'position' => 1,
            'status' => 'published',
        ]);

        $lesson = Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'video',
            'config' => ['title' => 'Intro'],
            'position' => 1,
            'locked' => false,
            'status' => 'published',
        ]);
        $template = CohortTemplate::factory()->create([
            'status' => 'draft',
            'price_amount' => 149,
            'price_currency' => 'USD',
            'slots' => [
                ['weekday' => 'monday', 'time' => '09:00'],
            ],
        ]);

        Livewire::actingAs($user)
            ->test(DiscordPracticePlanner::class)
            ->call('applyCohortTemplate', "db:{$template->id}")
            ->assertSet('selectedCohortTemplate', "db:{$template->id}")
            ->assertSet('activeCohortTemplate.price_currency', 'USD')
            ->set('selectedLesson', $lesson->id)
            ->set('title', 'Cohorte piloto')
            ->set('description', 'Sesión de prueba')
            ->set('type', 'cohort')
            ->set('start_at', now()->addDay()->format('Y-m-d H:i:s'))
            ->set('duration_minutes', 60)
            ->set('capacity', 12)
            ->set('discord_channel_url', 'https://discord.com/channels/1/1')
            ->call('createPractice');

        $this->assertEquals('published', $template->fresh()->status);
        $this->assertDatabaseHas('discord_practices', [
            'title' => 'Cohorte piloto',
            'cohort_label' => $template->cohort_label,
        ]);
    }
}


