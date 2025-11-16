<?php

namespace Tests\Feature\DataPorter;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Support\DataPorter\DataPorter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class DataPorterImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_import_student_snapshots_from_csv(): void
    {
        Gate::define('manage-settings', fn () => true);

        $admin = User::factory()->create();
        $studentA = User::factory()->create();
        $studentB = User::factory()->create();
        $lesson = $this->createLesson();

        $csv = implode(PHP_EOL, [
            'user_id,course_id,lesson_id,category,value,payload,captured_at',
            sprintf('%d,%d,%d,lesson_progress,85,"{""source"":""import""}",2025-11-16 10:00:00', $studentA->id, $lesson->chapter->course->id, $lesson->id),
            sprintf('%d,%d,,practice_booking,1,"{""channel"":""discord""}",2025-11-16 11:00:00', $studentB->id, $lesson->chapter->course->id),
        ]);

        $path = storage_path('framework/testing/dataporter_import.csv');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        file_put_contents($path, $csv);

        /** @var DataPorter $porter */
        $porter = app(DataPorter::class);

        $count = $porter->import('student_activity_snapshots', $path, $admin);

        $this->assertSame(2, $count);
        $this->assertDatabaseCount('student_activity_snapshots', 2);

        unlink($path);
    }

    private function createLesson(): Lesson
    {
        $course = Course::create([
            'slug' => 'dp-import-'.uniqid(),
            'level' => 'starter',
            'published' => true,
        ]);

        $chapter = Chapter::create([
            'course_id' => $course->id,
            'title' => 'Importador',
            'position' => 1,
        ]);

        return Lesson::create([
            'chapter_id' => $chapter->id,
            'type' => 'video',
            'position' => 1,
            'locked' => false,
            'config' => [
                'title' => 'Registro base',
                'source' => 'youtube',
                'video_id' => 'video',
                'length' => 200,
            ],
        ]);
    }
}

