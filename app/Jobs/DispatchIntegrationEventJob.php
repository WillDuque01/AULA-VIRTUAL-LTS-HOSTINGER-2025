<?php

namespace App\Jobs;

use App\Models\IntegrationEvent;
use App\Support\Integrations\DiscordMessageBuilder;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class DispatchIntegrationEventJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $eventId)
    {
    }

    public function handle(): void
    {
        $event = IntegrationEvent::find($this->eventId);

        if (! $event || $event->status === 'sent') {
            return;
        }

        $event->attempts++;
        $event->last_attempt_at = now();
        $event->save();

        try {
            $result = match ($event->target) {
                'make' => $this->sendToMake($event),
                'discord' => $this->sendToDiscord($event),
                'sheets' => $this->sendToSheets($event),
                'mailerlite' => $this->sendToMailerLite($event),
                'whatsapp' => $this->sendToWhatsApp($event),
                default => 'skipped',
            };

            if ($result === 'skipped') {
                $event->status = 'skipped';
            } else {
                $event->status = 'sent';
                $event->sent_at = now();
            }

            $event->last_error = null;
            $event->save();
        } catch (Throwable $exception) {
            $event->status = 'failed';
            $event->last_error = $exception->getMessage();
            $event->save();

            Log::channel('stack')->error('Integration dispatch failed', [
                'event' => $event->event,
                'target' => $event->target,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    private function sendToMake(IntegrationEvent $event): void
    {
        $config = config('services.make');
        $url = data_get($config, 'webhook_url');

        if (! $url) {
            throw new \RuntimeException('Make webhook URL no configurada');
        }

        $body = [
            'event' => $event->event,
            'payload' => $event->payload,
            'meta' => [
                'id' => $event->id,
                'attempt' => $event->attempts,
                'dispatched_at' => now()->toIso8601String(),
            ],
        ];

        $signature = null;
        $secret = data_get($config, 'secret');

        if ($secret) {
            $signature = base64_encode(hash_hmac(
                'sha256',
                json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                $secret,
                true
            ));
        }

        Http::timeout(12)
            ->withHeaders(array_filter([
                'X-LMS-Event' => $event->event,
                'X-LMS-Signature' => $signature,
            ]))
            ->post($url, $body)
            ->throw();
    }

    private function sendToDiscord(IntegrationEvent $event): void
    {
        $url = config('services.discord.webhook_url');

        if (! $url) {
            throw new \RuntimeException('Discord webhook no configurado');
        }

        $payload = (new DiscordMessageBuilder($event))->toPayload();

        Http::timeout(10)
            ->asJson()
            ->post($url, $payload)
            ->throw();
    }

    private function sendToSheets(IntegrationEvent $event): void
    {
        $googleConfig = config('services.google');

        if (! data_get($googleConfig, 'enabled')) {
            throw new \RuntimeException('Google Sheets deshabilitado');
        }

        $credentialsPath = data_get($googleConfig, 'service_account_json');
        $sheetId = data_get($googleConfig, 'sheet_id');
        $range = data_get($googleConfig, 'range', 'Integraciones!A1');

        if (! $credentialsPath || ! file_exists($credentialsPath)) {
            throw new \RuntimeException('Credenciales de Google no encontradas');
        }

        if (! $sheetId) {
            throw new \RuntimeException('Sheet ID no configurado');
        }

        $client = new \Google\Client();
        $client->setAuthConfig($credentialsPath);
        $client->setScopes([Sheets::SPREADSHEETS]);

        $service = new Sheets($client);

        $values = [[
            now()->toIso8601String(),
            $event->event,
            json_encode($event->payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            config('app.env'),
        ]];

        $body = new ValueRange(['values' => $values]);

        $service->spreadsheets_values->append(
            $sheetId,
            $range,
            $body,
            ['valueInputOption' => 'RAW']
        );
    }

    private function sendToMailerLite(IntegrationEvent $event): string
    {
        $apiKey = config('services.mailerlite.api_key');

        if (! $apiKey) {
            throw new \RuntimeException('MailerLite API key no configurada');
        }

        $email = data_get($event->payload, 'email');

        if (! $email) {
            return 'skipped';
        }

        $payload = [
            'email' => $email,
            'name' => data_get($event->payload, 'name'),
            'fields' => [
                'event' => $event->event,
                'metadata' => json_encode($event->payload, JSON_UNESCAPED_UNICODE),
            ],
        ];

        $groupId = config('services.mailerlite.group_id');
        if ($groupId) {
            $payload['groups'] = [$groupId];
        }

        Http::withToken($apiKey)
            ->timeout(10)
            ->post('https://connect.mailerlite.com/api/subscribers', $payload)
            ->throw();

        return 'sent';
    }

    private function sendToWhatsApp(IntegrationEvent $event): void
    {
        $config = config('services.whatsapp', []);

        if (! data_get($config, 'enabled')) {
            throw new \RuntimeException('WhatsApp deshabilitado');
        }

        $token = data_get($config, 'token');
        $phoneId = data_get($config, 'phone_number_id');
        $defaultTo = data_get($config, 'default_to');
        $rawRecipient = data_get($event->payload, 'student.phone') ?? $defaultTo;
        $recipient = $rawRecipient ? preg_replace('/\D+/', '', $rawRecipient) : null;

        if (! $token || ! $phoneId || ! $recipient) {
            throw new \RuntimeException('Config de WhatsApp incompleta');
        }

        $body = $this->buildWhatsAppMessage($event);

        Http::withToken($token)
            ->timeout(12)
            ->post("https://graph.facebook.com/v18.0/{$phoneId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $recipient,
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $body,
                ],
            ])
            ->throw();
    }

    private function buildWhatsAppMessage(IntegrationEvent $event): string
    {
        $title = data_get($event->payload, 'assignment.title', 'Tarea');
        $course = data_get($event->payload, 'assignment.course', 'curso');
        $student = data_get($event->payload, 'student.name', 'estudiante');
        $reason = data_get($event->payload, 'submission.reason')
            ?? data_get($event->payload, 'submission.feedback');

        $lines = [
            sprintf('*%s*', Str::headline($event->event)),
            "Curso: {$course}",
            "Tarea: {$title}",
            "Estudiante: {$student}",
        ];

        if ($reason) {
            $lines[] = 'Motivo: '.Str::limit($reason, 180);
        }

        if ($deeplink = config('services.whatsapp.deeplink')) {
            $lines[] = $deeplink;
        }

        return Str::of(implode("\n", $lines))
            ->limit(1000)
            ->toString();
    }
}
