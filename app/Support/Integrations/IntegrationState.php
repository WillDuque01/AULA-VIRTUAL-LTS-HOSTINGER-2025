<?php

namespace App\Support\Integrations;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class IntegrationState
{
    public static function storageDriver(): string
    {
        if (config('integrations.force_free_storage')) {
            return 'public';
        }

        return self::hasS3Credentials() ? 's3' : 'public';
    }

    public static function hasS3Credentials(): bool
    {
        $config = config('services.s3');

        return filled(Arr::get($config, 'key'))
            && filled(Arr::get($config, 'secret'))
            && filled(Arr::get($config, 'bucket'));
    }

    public static function realtimeDriver(): string
    {
        if (config('integrations.force_free_realtime')) {
            return 'log';
        }

        return self::hasPusherCredentials() ? 'pusher' : 'log';
    }

    public static function hasPusherCredentials(): bool
    {
        $config = config('services.pusher');

        return filled(Arr::get($config, 'app_id'))
            && filled(Arr::get($config, 'key'))
            && filled(Arr::get($config, 'secret'));
    }

    public static function mailDriver(): string
    {
        $smtpConfig = config('mail.mailers.smtp');
        $hasCredentials = filled(Arr::get($smtpConfig, 'host'))
            && filled(Arr::get($smtpConfig, 'username'))
            && filled(Arr::get($smtpConfig, 'password'));

        if (! $hasCredentials) {
            return 'log';
        }

        $declaredMailer = config('mail.default', 'log');

        return Str::of($declaredMailer)->lower()->value() === 'log' ? 'log' : 'smtp';
    }

    public static function videoMode(): string
    {
        if (config('integrations.force_youtube_only')) {
            return 'youtube';
        }

        $cfConfig = config('services.cf', []);
        if (filled(Arr::get($cfConfig, 'token')) && filled(Arr::get($cfConfig, 'account_id'))) {
            return 'cloudflare';
        }

        if (filled(config('services.vimeo.token'))) {
            return 'vimeo';
        }

        return 'youtube';
    }

    public static function summaries(): array
    {
        $storageDriver = self::storageDriver();
        $hasS3 = self::hasS3Credentials();
        $realtimeDriver = self::realtimeDriver();
        $hasPusher = self::hasPusherCredentials();
        $mailDriver = self::mailDriver();
        $hasSmtp = $mailDriver === 'smtp';
        $videoMode = self::videoMode();

        return [
            'storage' => [
                'label' => 'Almacenamiento',
                'driver' => $storageDriver,
                'status' => $storageDriver === 's3' ? 'Activo (S3/R2)' : 'En espera (public)',
                'ok' => $storageDriver === 's3' ? $hasS3 : true,
                'forced' => config('integrations.force_free_storage'),
                'has_credentials' => $hasS3,
            ],
            'realtime' => [
                'label' => 'Realtime / Broadcasting',
                'driver' => $realtimeDriver,
                'status' => $realtimeDriver === 'pusher' ? 'Activo (Pusher)' : 'Modo local',
                'ok' => $realtimeDriver === 'pusher' ? $hasPusher : true,
                'forced' => config('integrations.force_free_realtime'),
                'has_credentials' => $hasPusher,
            ],
            'mail' => [
                'label' => 'Correo saliente',
                'driver' => $mailDriver,
                'status' => $mailDriver === 'smtp' ? 'Activo (SMTP)' : 'En espera (log)',
                'ok' => $mailDriver === 'smtp' ? $hasSmtp : true,
                'forced' => false,
                'has_credentials' => $hasSmtp,
            ],
            'video' => [
                'label' => 'Video Player',
                'driver' => $videoMode,
                'status' => match ($videoMode) {
                    'cloudflare' => 'Activo (Cloudflare Stream)',
                    'vimeo' => 'Activo (Vimeo)',
                    default => 'Modo gratuito (YouTube)',
                },
                'ok' => true,
                'forced' => config('integrations.force_youtube_only'),
                'has_credentials' => $videoMode !== 'youtube',
            ],
            'whatsapp' => self::whatsappSummary(),
            'discord' => self::discordSummary(),
        ];
    }

    protected static function hasWhatsAppCredentials(): bool
    {
        $config = config('services.whatsapp', []);

        return filled(Arr::get($config, 'token'))
            && filled(Arr::get($config, 'phone_number_id'))
            && filled(Arr::get($config, 'default_to'));
    }

    protected static function whatsappSummary(): array
    {
        $config = config('services.whatsapp', []);
        $hasApi = self::hasWhatsAppCredentials();
        $hasDeeplink = filled(Arr::get($config, 'deeplink'));

        return [
            'label' => 'Alertas WhatsApp',
            'driver' => $hasApi ? 'cloud-api' : 'deeplink',
            'status' => match (true) {
                $hasApi => 'Automático (Cloud API)',
                $hasDeeplink => 'Disponible vía deeplink',
                default => 'Sin configurar',
            },
            'ok' => $hasApi || $hasDeeplink,
            'forced' => false,
            'has_credentials' => $hasApi,
        ];
    }

    protected static function discordSummary(): array
    {
        $webhook = config('services.discord.webhook_url');

        return [
            'label' => 'Alertas Discord',
            'driver' => 'webhook',
            'status' => $webhook ? 'Webhook activo' : 'Sin configurar',
            'ok' => filled($webhook),
            'forced' => false,
            'has_credentials' => filled($webhook),
        ];
    }
}
