<?php

namespace App\Support\Integrations;

class IntegrationConfigurator
{
    public static function apply(): void
    {
        config([
            'filesystems.default' => IntegrationState::storageDriver(),
            'broadcasting.default' => IntegrationState::realtimeDriver(),
            'mail.default' => IntegrationState::mailDriver(),
            'integrations.status' => IntegrationState::summaries(),
            'integrations.video_mode' => IntegrationState::videoMode(),
        ]);
    }
}
