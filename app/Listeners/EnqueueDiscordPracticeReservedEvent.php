<?php

namespace App\Listeners;

use App\Events\DiscordPracticeReserved;
use App\Support\Integrations\IntegrationDispatcher;

class EnqueueDiscordPracticeReservedEvent
{
    public function handle(DiscordPracticeReserved $event): void
    {
        $practice = $event->practice;
        $student = $event->reservation->user;

        IntegrationDispatcher::dispatch('discord.practice.reserved', [
            'practice' => [
                'id' => $practice->id,
                'title' => $practice->title,
                'start_at' => optional($practice->start_at)->toIso8601String(),
                'type' => $practice->type,
                'requires_package' => $practice->requires_package,
            ],
            'student' => [
                'id' => $student?->id,
                'name' => $student?->name,
                'email' => $student?->email,
            ],
            'reservation' => [
                'id' => $event->reservation->id,
                'status' => $event->reservation->status,
                'created_at' => optional($event->reservation->created_at)->toIso8601String(),
            ],
        ]);
    }
}


