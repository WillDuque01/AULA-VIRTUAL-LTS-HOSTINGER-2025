<?php

namespace Tests\Feature\Gamification;

use App\Http\Controllers\Api\VideoProgressController;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LessonCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_awards_points_and_records_event(): void
    {
        $user = User::factory()->create();
        $lesson = $this->createLesson(length: 200);

        Auth::login($user);

        $controller = app(VideoProgressController::class);
        $request = Request::create('/api/video/progress', 'POST', [
            'lesson_id' => $lesson->id,
            'source' => 'youtube',
            'last_second' => 200,
            'watched_seconds' => 200,
        ]);
        $request->setUserResolver(fn () => $user);

        $response = $controller->store($request);
        $payload = $response->getData(true);

        $this->assertTrue($payload['celebration']);
        $this->assertEquals(50, $payload['rewards']['points']);
        $this->assertDatabaseHas('gamification_events', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'type' => 'lesson_completed',
        ]);

        $this->assertNotNull($user->fresh()->last_completion_at);
        $this->assertSame(50, $user->fresh()->experience_points);
        $this->assertSame(1, $user->fresh()->current_streak);
    }

    public function test_streak_increments_within_window(): void
    {
        $user = User::factory()->create([
            'experience_points' => 100,
            'current_streak' => 3,
            'last_completion_at' => now()->subHours(12),
        ]);
        $lesson = $this->createLesson(length: 150);

        Auth::login($user);

        $controller = app(VideoProgressController::class);
        $request = Request::create('/api/video/progress', 'POST', [
            'lesson_id' => $lesson->id,
            'source' => 'youtube',
            'last_second' => 150,
            'watched_seconds' => 150,
        ]);
        $request->setUserResolver(fn () => $user);

        $controller->store($request);

        $user->refresh();
        $this->assertSame(4, $user->current_streak);
        $this->assertSame(150, $user->experience_points);
    }

    private function createLesson(int $length): Lesson
    {
        $course = Course::create([
            'slug' => 'demo-course',
            'level' => 'beginner',
            'published' => true,
        ]);

        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Introducción',
            'position' => 1,
        ]);

        return Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'video',
            'position' => 1,
            'config' => [
                'title' => 'Demo',
                'length' => $length,
                'source' => 'youtube',
                'video_id' => 'abcd1234',
            ],
        ]);
    }
}


