<?php

namespace Tests\Feature;

use App\Events\ModuleUnlocked;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Tier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ModuleUnlockedObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_unlocking_lesson_dispatches_module_unlocked_event(): void
    {
        Event::fake([ModuleUnlocked::class]);

        $course = Course::create(['slug' => 'test-course', 'level' => 'advanced', 'published' => true]);
        $chapter = Chapter::create(['course_id' => $course->id, 'title' => 'Módulo 1', 'position' => 1]);
        $lesson = Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'video',
            'locked' => true,
            'position' => 1,
            'config' => ['title' => 'Clase desbloqueable'],
        ]);
        $this->assertTrue($lesson->locked);
        $this->assertTrue((bool) $lesson->getOriginal('locked'));

        $tier = Tier::factory()->create(['name' => 'VIP']);
        $course->tiers()->attach($tier->id);
        $user = User::factory()->create();
        $tier->users()->attach($user->id, ['status' => 'active']);

        $this->assertSame(1, $course->tiers()->count());
        $this->assertSame(1, $tier->users()->count());

        $lesson->locked = false;
        $lesson->save();

        Event::assertDispatched(ModuleUnlocked::class, function (ModuleUnlocked $event) use ($lesson, $user) {
            return $event->moduleTitle === 'Clase desbloqueable'
                && $event->recipients->pluck('id')->contains($user->id)
                && $event->moduleUrl === route('lessons.player', ['locale' => app()->getLocale(), 'lesson' => $lesson]);
        });
    }
}

