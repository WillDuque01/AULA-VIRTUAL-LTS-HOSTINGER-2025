<?php

namespace Tests\Feature;

use App\Http\Livewire\Builder\CourseBuilder;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\DiscordPractice;
use App\Models\Lesson;
use App\Models\PracticePackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class CourseBuilderPracticeMetaTest extends TestCase
{
    use RefreshDatabase;

    public function test_builder_shows_practice_and_pack_metadata(): void
    {
        $creator = User::factory()->create();

        $course = Course::create([
            'slug' => Str::uuid(),
            'level' => 'b2',
            'published' => true,
        ]);

        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Capítulo Discord',
            'position' => 1,
        ]);

        $lesson = Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'video',
            'position' => 1,
            'config' => [
                'title' => 'Pronunciación avanzada',
            ],
        ]);

        DiscordPractice::create([
            'lesson_id' => $lesson->id,
            'type' => '1:1',
            'title' => 'Sesión demo',
            'practice_package_id' => null,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addMinutes(30),
            'duration_minutes' => 30,
            'capacity' => 3,
            'status' => 'scheduled',
            'created_by' => $creator->id,
            'requires_package' => true,
        ]);

        PracticePackage::create([
            'creator_id' => $creator->id,
            'lesson_id' => $lesson->id,
            'title' => 'Pack builder',
            'subtitle' => '4 sesiones intensivas',
            'description' => 'Sesiones enfocadas en speaking.',
            'sessions_count' => 4,
            'price_amount' => 120,
            'price_currency' => 'USD',
            'is_global' => false,
            'visibility' => 'private',
            'status' => 'published',
        ]);

        Livewire::actingAs($creator)
            ->test(CourseBuilder::class, ['course' => $course->fresh('chapters.lessons')])
            ->assertSet('state.chapters.0.lessons.0.practice_meta.total', 1)
            ->assertSet('state.chapters.0.lessons.0.practice_meta.requires_pack', true)
            ->assertSet('state.chapters.0.lessons.0.pack_meta.title', 'Pack builder')
            ->assertSee('Prácticas Discord');
    }
}


