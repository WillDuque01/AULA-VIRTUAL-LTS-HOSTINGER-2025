<?php

namespace App\Support\Integrations;

use App\Jobs\DispatchIntegrationEventJob;
use App\Models\IntegrationEvent;

class IntegrationDispatcher
{
    public static function dispatch(string $event, array $payload): void
    {
        foreach (self::targets() as $target) {
            $record = IntegrationEvent::create([
                'event' => $event,
                'target' => $target,
                'payload' => $payload,
            ]);

            DispatchIntegrationEventJob::dispatch($record->id);
        }
    }

    protected static function targets(): array
    {
        $targets = [];

        if (filled(config('services.make.webhook_url'))) {
            $targets[] = 'make';
        }

        if (filled(config('services.discord.webhook_url'))) {
            $targets[] = 'discord';
        }

        if (config('services.google.enabled')) {
            $targets[] = 'sheets';
        }

        if (filled(config('services.mailerlite.api_key'))) {
            $targets[] = 'mailerlite';
        }

        if (config('services.whatsapp.enabled')) {
            $targets[] = 'whatsapp';
        }

        return $targets;
    }
}

