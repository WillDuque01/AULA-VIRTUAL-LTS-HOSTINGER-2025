<?php

namespace Tests\Feature;

use App\Livewire\Admin\Dashboard;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Models\VideoHeatmapSegment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDashboardHeatmapTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_exposes_abandonment_insights(): void
    {
        $admin = User::factory()->create();

        $course = Course::create([
            'slug' => 'heatmap-demo',
            'level' => 'intermediate',
            'published' => true,
        ]);

        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Modulo 1',
            'position' => 1,
        ]);

        $lesson = Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'video',
            'position' => 1,
            'config' => [
                'title' => 'Video introductorio',
                'source' => 'youtube',
                'video_id' => 'demo123',
                'length' => 180,
            ],
        ]);

        VideoHeatmapSegment::create([
            'lesson_id' => $lesson->id,
            'bucket' => 4,
            'reach_count' => 5,
        ]);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertSet('abandonmentInsights.0.lesson', 'Video introductorio')
            ->assertSet('abandonmentInsights.0.timestamp', '01:00')
            ->assertSet('abandonmentInsights.0.reach', 5);
    }
}

