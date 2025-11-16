<?php

namespace Tests\Feature;

use App\Events\DiscordPracticeScheduled;
use App\Livewire\Professor\DiscordPracticePlanner;
use App\Models\Chapter;
use App\Models\CohortTemplate;
use App\Models\Course;
use App\Models\DiscordPractice;
use App\Models\Lesson;
use App\Models\PracticeTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class DiscordPracticeTemplateSeriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_template_persists_slots_and_lesson(): void
    {
        $user = User::factory()->create();
        $lesson = $this->createLesson();

        Livewire::actingAs($user)
            ->test(DiscordPracticePlanner::class)
            ->set('selectedLesson', $lesson->id)
            ->set('title', 'Cohorte Demo')
            ->set('templateName', 'Pack mañanas')
            ->set('templateSlots', [
                ['weekday' => 'monday', 'time' => '08:30'],
                ['weekday' => 'wednesday', 'time' => '19:00'],
            ])
            ->call('saveTemplate')
            ->assertDispatched('practice-template-saved');

        $template = PracticeTemplate::where('name', 'Pack mañanas')->first();

        $this->assertNotNull($template);
        $this->assertEquals($lesson->id, $template->payload['lesson_id']);
        $this->assertCount(2, $template->payload['slots']);
        $this->assertEquals('wednesday', $template->payload['slots'][1]['weekday']);
        $this->assertEquals('19:00', $template->payload['slots'][1]['time']);
    }

    public function test_schedule_template_series_creates_practices(): void
    {
        $user = User::factory()->create();
        $lesson = $this->createLesson();
        $template = PracticeTemplate::create([
            'user_id' => $user->id,
            'name' => 'Serie B2',
            'payload' => [
                'lesson_id' => $lesson->id,
                'title' => 'Sesión B2',
                'duration_minutes' => 60,
                'capacity' => 12,
                'slots' => [
                    ['weekday' => 'monday', 'time' => '10:00'],
                    ['weekday' => 'thursday', 'time' => '18:00'],
                ],
            ],
        ]);

        $startDate = Carbon::now()->addWeek()->startOfWeek()->toDateString();

        Event::fake(DiscordPracticeScheduled::class);

        Livewire::actingAs($user)
            ->test(DiscordPracticePlanner::class)
            ->set('seriesForm.template_id', $template->id)
            ->set('seriesForm.start_date', $startDate)
            ->set('seriesForm.weeks', 2)
            ->call('scheduleTemplateSeries')
            ->assertHasNoErrors();

        $this->assertCount(4, DiscordPractice::all());

        $firstPractice = DiscordPractice::orderBy('start_at')->first();
        $this->assertTrue($firstPractice->start_at->dayOfWeek === Carbon::MONDAY);
        $this->assertEquals('10:00', $firstPractice->start_at->format('H:i'));

        Event::assertDispatched(DiscordPracticeScheduled::class, 4);
    }

    public function test_apply_cohort_template_prefills_form(): void
    {
        config()->set('practice.cohort_templates', [
            'demo' => [
                'name' => 'Demo',
                'cohort_label' => 'B2-TEST',
                'type' => 'cohort',
                'duration_minutes' => 75,
                'capacity' => 9,
                'requires_package' => true,
                'slots' => [
                    ['weekday' => 'monday', 'time' => '10:00'],
                    ['weekday' => 'thursday', 'time' => '18:30'],
                ],
            ],
        ]);

        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(DiscordPracticePlanner::class)
            ->call('applyCohortTemplate', 'demo')
            ->assertSet('cohort_label', 'B2-TEST')
            ->assertSet('duration_minutes', 75)
            ->assertSet('capacity', 9)
            ->assertSet('templateSlots.0.weekday', 'monday')
            ->assertSet('templateSlots.1.time', '18:30')
            ->assertSet('selectedCohortTemplate', 'demo');
    }

    public function test_apply_database_cohort_template_prefills_form(): void
    {
        $template = CohortTemplate::factory()->create([
            'name' => 'Equipo B1',
            'type' => 'cohort',
            'cohort_label' => 'B1-AM',
            'duration_minutes' => 55,
            'capacity' => 14,
            'requires_package' => true,
            'slots' => [
                ['weekday' => 'tuesday', 'time' => '08:00'],
                ['weekday' => 'thursday', 'time' => '08:00'],
            ],
        ]);

        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(DiscordPracticePlanner::class)
            ->call('applyCohortTemplate', 'db:'.$template->id)
            ->assertSet('cohort_label', 'B1-AM')
            ->assertSet('duration_minutes', 55)
            ->assertSet('capacity', 14)
            ->assertSet('templateSlots.0.weekday', 'tuesday')
            ->assertSet('selectedCohortTemplate', 'db:'.$template->id);
    }

    private function createLesson(): Lesson
    {
        $course = Course::create([
            'title' => 'Curso Demo',
            'slug' => Str::slug('curso-demo-'.Str::random(5)),
            'level' => 'b2',
            'published' => true,
        ]);

        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Unidad 1',
            'position' => 1,
        ]);

        return Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'video',
            'position' => 1,
            'config' => [
                'title' => 'Lección demo',
            ],
        ]);
    }
}


