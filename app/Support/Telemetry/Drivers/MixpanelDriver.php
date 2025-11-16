<?php

namespace App\Support\Telemetry\Drivers;

use App\Models\VideoPlayerEvent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MixpanelDriver implements TelemetryDriver
{
    public function name(): string
    {
        return 'mixpanel';
    }

    public function enabled(): bool
    {
        $config = config('telemetry.mixpanel');

        return (bool) data_get($config, 'enabled')
            && filled(data_get($config, 'project_token'));
    }

    public function send(Collection $events): void
    {
        if ($events->isEmpty() || ! $this->enabled()) {
            return;
        }

        $endpoint = config('telemetry.mixpanel.endpoint');
        $token = config('telemetry.mixpanel.project_token');

        $events
            ->chunk(50)
            ->each(function (Collection $chunk) use ($endpoint, $token): void {
                $payload = $chunk->map(function (VideoPlayerEvent $event) use ($token) {
                    return [
                        'event' => $this->formatName($event->event),
                        'properties' => array_merge([
                            'token' => $token,
                            'distinct_id' => $this->distinctId($event),
                            'time' => optional($event->recorded_at)->timestamp ?? now()->timestamp,
                            'lesson_id' => $event->lesson_id,
                            'course_id' => $event->course_id,
                            'provider' => $event->provider,
                            'playback_seconds' => $event->playback_seconds,
                            'watched_seconds' => $event->watched_seconds,
                            'video_duration' => $event->video_duration,
                            'context_tag' => $event->context_tag,
                        ], $event->metadata ?? []),
                    ];
                })->all();

                $response = Http::asForm()->post($endpoint, [
                    'data' => base64_encode(json_encode($payload)),
                    'verbose' => 1,
                ]);

                if (! $response->successful()) {
                    throw new \RuntimeException(
                        sprintf('Mixpanel responded with %s: %s', $response->status(), $response->body())
                    );
                }
            });
    }

    private function formatName(?string $event): string
    {
        $formatted = Str::of($event ?: 'player_event')
            ->replace(' ', '_')
            ->take(50)
            ->value();

        return $formatted ?: 'player_event';
    }

    private function distinctId(VideoPlayerEvent $event): string
    {
        if ($event->user_id) {
            return (string) $event->user_id;
        }

        return sprintf('guest-%s', $event->id);
    }
}

