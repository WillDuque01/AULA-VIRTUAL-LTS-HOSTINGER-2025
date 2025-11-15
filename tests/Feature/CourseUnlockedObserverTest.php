<?php

namespace Tests\Feature;

use App\Events\CourseUnlocked;
use App\Models\Course;
use App\Models\CourseI18n;
use App\Models\Tier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CourseUnlockedObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_publishing_course_dispatches_event(): void
    {
        Event::fake([CourseUnlocked::class]);

        $course = Course::create([
            'slug' => 'curso-pro',
            'level' => 'advanced',
            'published' => false,
        ]);

        CourseI18n::create([
            'course_id' => $course->id,
            'locale' => 'es',
            'title' => 'Curso Pro',
            'description' => 'Contenido avanzado',
        ]);

        $tier = Tier::factory()->create(['name' => 'VIP']);
        $course->tiers()->attach($tier->id);

        $user = User::factory()->create();
        $tier->users()->attach($user->id, ['status' => 'active']);

        $course->published = true;
        $course->save();

        Event::assertDispatched(CourseUnlocked::class, function (CourseUnlocked $event) use ($course, $user) {
            return $event->course->id === $course->id
                && $event->courseTitle === 'Curso Pro'
                && $event->recipients->pluck('id')->contains($user->id);
        });
    }
}

