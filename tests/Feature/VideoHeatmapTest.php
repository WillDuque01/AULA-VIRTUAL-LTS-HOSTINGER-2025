<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoHeatmapTest extends TestCase
{
    use RefreshDatabase;

    public function test_progress_updates_populate_heatmap_segments(): void
    {
        config()->set('player.heatmap_bucket_seconds', 10);
        config()->set('auth.guards.sanctum', [
            'driver' => 'session',
            'provider' => 'users',
        ]);

        $lesson = $this->createLesson();
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum');

        $this->postJson('/api/video/progress', [
            'lesson_id' => $lesson->id,
            'source' => 'vimeo',
            'last_second' => 35,
            'watched_seconds' => 35,
        ])->assertOk();

        $this->assertDatabaseHas('video_heatmap_segments', [
            'lesson_id' => $lesson->id,
            'bucket' => 3,
            'reach_count' => 1,
        ]);

        // No new buckets should be added if the user reports the same time.
        $this->postJson('/api/video/progress', [
            'lesson_id' => $lesson->id,
            'source' => 'vimeo',
            'last_second' => 35,
            'watched_seconds' => 35,
        ])->assertOk();

        $this->assertDatabaseHas('video_heatmap_segments', [
            'lesson_id' => $lesson->id,
            'bucket' => 3,
            'reach_count' => 1,
        ]);

        $secondUser = User::factory()->create();
        $this->actingAs($secondUser, 'sanctum');

        $this->postJson('/api/video/progress', [
            'lesson_id' => $lesson->id,
            'source' => 'vimeo',
            'last_second' => 22,
            'watched_seconds' => 22,
        ])->assertOk();

        $this->assertDatabaseHas('video_heatmap_segments', [
            'lesson_id' => $lesson->id,
            'bucket' => 2,
            'reach_count' => 2,
        ]);
    }

    private function createLesson(int $length = 120): Lesson
    {
        $course = Course::create([
            'slug' => 'demo-'.uniqid(),
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
            'locked' => false,
            'config' => [
                'title' => 'Demo Lesson',
                'source' => 'vimeo',
                'video_id' => 'abc123',
                'length' => $length,
            ],
        ]);
    }
}

