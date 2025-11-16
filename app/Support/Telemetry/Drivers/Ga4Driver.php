<?php

namespace App\Support\Telemetry\Drivers;

use App\Models\VideoPlayerEvent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Ga4Driver implements TelemetryDriver
{
    public function name(): string
    {
        return 'ga4';
    }

    public function enabled(): bool
    {
        $config = config('telemetry.ga4');

        return (bool) data_get($config, 'enabled')
            && filled(data_get($config, 'measurement_id'))
            && filled(data_get($config, 'api_secret'));
    }

    public function send(Collection $events): void
    {
        if ($events->isEmpty() || ! $this->enabled()) {
            return;
        }

        $endpoint = config('telemetry.ga4.endpoint');
        $measurementId = config('telemetry.ga4.measurement_id');
        $apiSecret = config('telemetry.ga4.api_secret');

        $events
            ->chunk(25)
            ->each(function (Collection $chunk) use ($endpoint, $measurementId, $apiSecret): void {
                $payload = [
                    'client_id' => $this->clientId($chunk->first()),
                    'user_id' => $this->userId($chunk->first()),
                    'events' => $chunk
                        ->map(fn (VideoPlayerEvent $event) => $this->formatEvent($event))
                        ->values()
                        ->all(),
                ];

                $response = Http::asJson()
                    ->post("{$endpoint}?measurement_id={$measurementId}&api_secret={$apiSecret}", $payload);

                if (! $response->successful()) {
                    throw new \RuntimeException(
                        sprintf('GA4 responded with %s: %s', $response->status(), $response->body())
                    );
                }
            });
    }

    private function formatEvent(VideoPlayerEvent $event): array
    {
        return [
            'name' => $this->formatName($event->event),
            'params' => array_filter([
                'event_source' => 'player',
                'lesson_id' => $event->lesson_id,
                'course_id' => $event->course_id,
                'provider' => $event->provider,
                'playback_seconds' => $event->playback_seconds,
                'watched_seconds' => $event->watched_seconds,
                'video_duration' => $event->video_duration,
                'context_tag' => $event->context_tag,
                'metadata' => $event->metadata ?: null,
                'recorded_at' => optional($event->recorded_at)->toIso8601String(),
            ], fn ($value) => $value !== null),
        ];
    }

    private function formatName(?string $name): string
    {
        $formatted = Str::of($name ?: 'player_event')
            ->snake()
            ->trim('_')
            ->take(40)
            ->value();

        return $formatted ?: 'player_event';
    }

    private function clientId(?VideoPlayerEvent $event): string
    {
        if (! $event) {
            return sprintf('guest.%s', Str::uuid());
        }

        return sprintf(
            '%s.%s',
            $event->user_id ?? 'guest',
            $event->id
        );
    }

    private function userId(?VideoPlayerEvent $event): ?string
    {
        if (! $event || ! $event->user_id) {
            return null;
        }

        return (string) $event->user_id;
    }
}

