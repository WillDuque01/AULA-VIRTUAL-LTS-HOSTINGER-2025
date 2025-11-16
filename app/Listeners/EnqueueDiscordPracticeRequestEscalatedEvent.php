<?php

namespace App\Listeners;

use App\Events\DiscordPracticeRequestEscalated;
use App\Support\Integrations\IntegrationDispatcher;

class EnqueueDiscordPracticeRequestEscalatedEvent
{
    public function handle(DiscordPracticeRequestEscalated $event): void
    {
        $lesson = $event->lesson;
        $course = $lesson->chapter?->course;

        IntegrationDispatcher::dispatch('discord.practice.requests_escalated', [
            'lesson' => [
                'id' => $lesson->id,
                'title' => data_get($lesson->config, 'title'),
                'course' => $course?->slug,
            ],
            'pending' => $event->pendingCount,
        ]);
    }
}


