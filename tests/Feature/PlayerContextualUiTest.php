<?php

namespace Tests\Feature;

use App\Http\Livewire\Player;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\DiscordPractice;
use App\Models\Lesson;
use App\Models\User;
use App\Models\VideoHeatmapSegment;
use App\Models\VideoProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class PlayerContextualUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_progress_markers_follow_chapter_boundaries(): void
    {
        [$lessonCurrent] = $this->createCourseStructure();
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Player::class, ['lesson' => $lessonCurrent])
            ->assertSet('progressMarkers.0.label', 'Unidad 1')
            ->assertSet('progressMarkers.1.label', 'Unidad 2');
    }

    public function test_return_hint_exposed_when_resume_point_exists(): void
    {
        [$lessonCurrent, $user] = $this->createCourseStructure(withUser: true);

        VideoProgress::create([
            'lesson_id' => $lessonCurrent->id,
            'user_id' => $user->id,
            'last_second' => 120,
            'watched_seconds' => 120,
            'source' => 'youtube',
        ]);

        Livewire::actingAs($user)
            ->test(Player::class, ['lesson' => $lessonCurrent])
            ->assertSet('returnHint.seconds', 120);
    }

    public function test_cta_highlight_prioritizes_practice_slot(): void
    {
        [$lessonCurrent, $user] = $this->createCourseStructure(withUser: true);

        DiscordPractice::create([
            'lesson_id' => $lessonCurrent->id,
            'title' => 'Live practice',
            'description' => null,
            'type' => 'cohort',
            'cohort_label' => 'B2',
            'practice_package_id' => null,
            'start_at' => Carbon::now()->addDay(),
            'end_at' => Carbon::now()->addDay()->addHour(),
            'duration_minutes' => 60,
            'capacity' => 8,
            'discord_channel_url' => null,
            'created_by' => $user->id,
            'requires_package' => false,
            'status' => 'scheduled',
        ]);

        Livewire::actingAs($user)
            ->test(Player::class, ['lesson' => $lessonCurrent])
            ->assertSet('ctaHighlight.type', 'practice');
    }

    public function test_heatmap_highlights_surface_top_segments(): void
    {
        [$lessonCurrent, $user] = $this->createCourseStructure(withUser: true);

        VideoHeatmapSegment::create([
            'lesson_id' => $lessonCurrent->id,
            'bucket' => 0,
            'reach_count' => 10,
        ]);
        VideoHeatmapSegment::create([
            'lesson_id' => $lessonCurrent->id,
            'bucket' => 60,
            'reach_count' => 35,
        ]);
        VideoHeatmapSegment::create([
            'lesson_id' => $lessonCurrent->id,
            'bucket' => 120,
            'reach_count' => 25,
        ]);

        Livewire::actingAs($user)
            ->test(Player::class, ['lesson' => $lessonCurrent])
            ->assertSet('heatmapHighlights.0.bucket', 60);
    }

    /**
     * @return array{Lesson, User|null}
     */
    private function createCourseStructure(bool $withUser = false): array
    {
        $course = Course::create([
            'slug' => Str::slug('curso-player-'.Str::random(5)),
            'level' => 'b2',
            'published' => true,
        ]);

        $chapterOne = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Unidad 1',
            'position' => 1,
        ]);

        $chapterTwo = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Unidad 2',
            'position' => 2,
        ]);

        Lesson::create([
            'chapter_id' => $chapterOne->id,
            'type' => 'video',
            'position' => 1,
            'config' => [
                'title' => 'Introducción',
                'length' => 300,
                'video_id' => 'abc123',
                'source' => 'youtube',
            ],
        ]);

        $lessonCurrent = Lesson::create([
            'chapter_id' => $chapterTwo->id,
            'type' => 'video',
            'position' => 1,
            'config' => [
                'title' => 'Práctica guiada',
                'length' => 600,
                'video_id' => 'def456',
                'source' => 'youtube',
            ],
        ]);

        $user = $withUser ? User::factory()->create() : null;

        return [$lessonCurrent, $user];
    }
}


