<?php

namespace Tests\Feature;

use App\Http\Livewire\Builder\CourseBuilder;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class CourseBuilderQuickActionsTest extends TestCase
{
    use RefreshDatabase;

    private function createCourseWithChapters(): array
    {
        $course = Course::create([
            'title' => 'Curso UIX',
            'slug' => Str::slug('Curso UIX '.Str::random(5)),
            'level' => 'b2',
            'published' => true,
        ]);

        $chapterA = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Capítulo A',
            'position' => 1,
        ]);

        $chapterB = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Capítulo B',
            'position' => 2,
        ]);

        $lesson = Lesson::create([
            'chapter_id' => $chapterA->id,
            'type' => 'video',
            'position' => 1,
            'config' => [
                'title' => 'Video original',
                'source' => 'youtube',
                'video_id' => 'abc123',
                'length' => 120,
            ],
        ]);

        return [$course, $chapterA, $chapterB, $lesson];
    }

    public function test_duplicate_lesson_creates_copy_and_focuses(): void
    {
        [$course, $chapterA,, $lesson] = $this->createCourseWithChapters();
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CourseBuilder::class, ['course' => $course])
            ->call('duplicateLesson', $lesson->id);

        $this->assertEquals(
            2,
            Lesson::where('chapter_id', $chapterA->id)->count()
        );
    }

    public function test_quick_move_lesson_updates_chapter(): void
    {
        [$course,, $chapterB, $lesson] = $this->createCourseWithChapters();
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CourseBuilder::class, ['course' => $course])
            ->call('quickMoveLesson', $lesson->id, $chapterB->id);

        $this->assertDatabaseHas('lessons', [
            'id' => $lesson->id,
            'chapter_id' => $chapterB->id,
        ]);
    }

    public function test_quick_convert_lesson_to_assignment(): void
    {
        [$course,,,$lesson] = $this->createCourseWithChapters();
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CourseBuilder::class, ['course' => $course])
            ->call('quickConvertLesson', $lesson->id, 'assignment');

        $this->assertDatabaseHas('lessons', [
            'id' => $lesson->id,
            'type' => 'assignment',
        ]);

        $this->assertDatabaseHas('assignments', [
            'lesson_id' => $lesson->id,
        ]);
    }
}


