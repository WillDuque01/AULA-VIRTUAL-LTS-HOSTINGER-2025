<?php

namespace App\Listeners;

use App\Events\DiscordPracticeScheduled;
use App\Support\Integrations\IntegrationDispatcher;

class EnqueueDiscordPracticeScheduledEvent
{
    public function handle(DiscordPracticeScheduled $event): void
    {
        $practice = $event->practice;

        IntegrationDispatcher::dispatch('discord.practice.scheduled', [
            'practice' => [
                'id' => $practice->id,
                'title' => $practice->title,
                'lesson_id' => $practice->lesson_id,
                'start_at' => optional($practice->start_at)->toIso8601String(),
                'type' => $practice->type,
                'capacity' => $practice->capacity,
                'requires_package' => $practice->requires_package,
            ],
            'creator' => [
                'id' => $practice->created_by,
                'name' => $practice->creator?->name,
                'email' => $practice->creator?->email,
            ],
        ]);
    }
}


