<?php

namespace Tests\Feature;

use App\Events\DiscordPracticeScheduled;
use App\Livewire\Professor\DiscordPracticePlanner;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\DiscordPractice;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DiscordPracticeWeekDuplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('Profesor');
    }

    public function test_duplicate_week_series_clones_all_practices(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Profesor');
        $lesson = $this->createLesson();
        $weekStart = Carbon::now()->startOfWeek()->addWeek();

        DiscordPractice::create([
            'lesson_id' => $lesson->id,
            'title' => 'Sesión lunes',
            'type' => 'cohort',
            'start_at' => $weekStart->copy()->setTime(10, 0),
            'end_at' => $weekStart->copy()->setTime(11, 0),
            'duration_minutes' => 60,
            'capacity' => 10,
            'created_by' => $user->id,
        ]);

        DiscordPractice::create([
            'lesson_id' => $lesson->id,
            'title' => 'Sesión miércoles',
            'type' => 'cohort',
            'start_at' => $weekStart->copy()->addDays(2)->setTime(18, 30),
            'end_at' => $weekStart->copy()->addDays(2)->setTime(19, 30),
            'duration_minutes' => 60,
            'capacity' => 10,
            'created_by' => $user->id,
        ]);

        Event::fake(DiscordPracticeScheduled::class);

        Livewire::actingAs($user)
            ->test(DiscordPracticePlanner::class)
            ->set('calendarRangeStart', $weekStart->toDateString())
            ->set('calendarRangeEnd', $weekStart->copy()->addDays(6)->toDateString())
            ->set('weekDuplicationForm.offset', 1)
            ->set('weekDuplicationForm.repeat', 2)
            ->call('duplicateWeekSeries')
            ->assertDispatched('practice-week-duplicated');

        $this->assertEquals(6, DiscordPractice::count()); // 2 originales + (2 * repeat)
        Event::assertDispatched(DiscordPracticeScheduled::class, 4);
    }

    private function createLesson(): Lesson
    {
        $course = Course::create([
            'title' => 'Curso Planner',
            'slug' => Str::slug('curso-planner-'.Str::random(5)),
            'level' => 'b1',
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
                'title' => 'Introducción planner',
            ],
        ]);
    }
}


