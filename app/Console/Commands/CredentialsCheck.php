<?php

namespace App\Console\Commands;

use App\Support\Integrations\IntegrationState;
use Illuminate\Console\Command;

class CredentialsCheck extends Command
{
    protected $signature = 'credentials:check {--no-env : Omite el listado de variables faltantes y muestra solo el estado actual}';
    protected $description = 'Resumen de integraciones activas y variables .env faltantes';

    public function handle(): int
    {
        $this->info('=== Estado de integraciones ===');
        foreach (IntegrationState::summaries() as $summary) {
            $line = sprintf(
                '%s → %s (driver: %s)%s',
                $summary['label'],
                $summary['status'],
                $summary['driver'],
                $summary['forced'] ? ' [FORZADO]' : ''
            );

            $summary['ok'] ? $this->info($line) : $this->warn($line);
        }

        if ($this->option('no-env')) {
            return self::SUCCESS;
        }

        $this->line('');
        $this->info('=== Variables requeridas ===');

        $sets = [
            'Google OAuth' => ['GOOGLE_CLIENT_ID','GOOGLE_CLIENT_SECRET','GOOGLE_REDIRECT_URI'],
            'Pusher/Ably'  => ['PUSHER_APP_ID','PUSHER_APP_KEY','PUSHER_APP_SECRET'],
            'S3/R2'        => ['AWS_ACCESS_KEY_ID','AWS_SECRET_ACCESS_KEY','AWS_BUCKET'],
            'Vimeo/CF'     => ['VIMEO_TOKEN','CLOUDFLARE_STREAM_TOKEN'],
            'Gmail SMTP'   => ['MAIL_HOST','MAIL_USERNAME','MAIL_PASSWORD'],
            'Make HMAC'    => ['WEBHOOKS_MAKE_SECRET'],
        ];

        foreach ($sets as $name => $keys) {
            $missing = array_filter($keys, static fn ($key) => blank(env($key)));
            if ($missing) {
                $this->warn($name.' faltantes: '.implode(', ', $missing));
                continue;
            }

            $this->info($name.' OK');
        }

        return self::SUCCESS;
    }
}
