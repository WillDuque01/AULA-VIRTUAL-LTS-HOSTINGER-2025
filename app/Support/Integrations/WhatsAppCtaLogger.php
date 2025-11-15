<?php

namespace App\Support\Integrations;

use App\Models\IntegrationEvent;
use Illuminate\Support\Facades\Auth;

class WhatsAppCtaLogger
{
    public static function record(string $context, array $meta = []): void
    {
        IntegrationEvent::create([
            'event' => 'whatsapp.cta_clicked',
            'target' => 'whatsapp_cta',
            'payload' => [
                'context' => $context,
                'user_id' => Auth::id(),
                'meta' => $meta,
            ],
            'status' => 'sent',
            'sent_at' => now(),
            'attempts' => 0,
        ]);
    }
}


