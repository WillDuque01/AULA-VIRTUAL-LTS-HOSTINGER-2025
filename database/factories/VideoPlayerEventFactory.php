<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\VideoPlayerEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\VideoPlayerEvent>
 */
class VideoPlayerEventFactory extends Factory
{
    protected $model = VideoPlayerEvent::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'lesson_id' => null,
            'course_id' => null,
            'event' => 'progress_tick',
            'provider' => 'youtube',
            'playback_seconds' => $this->faker->numberBetween(0, 600),
            'watched_seconds' => $this->faker->numberBetween(0, 600),
            'video_duration' => 600,
            'playback_rate' => 1.0,
            'context_tag' => 'player',
            'metadata' => ['source' => 'factory'],
            'recorded_at' => now(),
        ];
    }
}

