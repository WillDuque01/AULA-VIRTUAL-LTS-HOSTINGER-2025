<?php

namespace App\Support\Integrations;

use App\Models\IntegrationEvent;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DiscordMessageBuilder
{
    private const SEVERITY_SUCCESS = 'success';
    private const SEVERITY_INFO = 'info';
    private const SEVERITY_WARNING = 'warning';
    private const SEVERITY_CRITICAL = 'critical';

    private const SEVERITIES = [
        'assignment.approved' => self::SEVERITY_SUCCESS,
        'assignment.rejected' => self::SEVERITY_WARNING,
        'certificate.issued' => self::SEVERITY_SUCCESS,
        'course.unlocked' => self::SEVERITY_SUCCESS,
        'module.unlocked' => self::SEVERITY_SUCCESS,
        'offer.launched' => self::SEVERITY_INFO,
        'tier.updated' => self::SEVERITY_INFO,
        'subscriptions.expiring' => self::SEVERITY_WARNING,
        'subscriptions.expired' => self::SEVERITY_CRITICAL,
        'payments.simulated' => self::SEVERITY_INFO,
        'practice.package.published' => self::SEVERITY_INFO,
        'practice.package.purchased' => self::SEVERITY_SUCCESS,
        'discord.practice.scheduled' => self::SEVERITY_INFO,
        'discord.practice.reserved' => self::SEVERITY_SUCCESS,
        'discord.practice.requests_escalated' => self::SEVERITY_WARNING,
    ];

    private const SEVERITY_COLORS = [
        self::SEVERITY_SUCCESS => 0x22c55e,
        self::SEVERITY_INFO => 0x0ea5e9,
        self::SEVERITY_WARNING => 0xf59e0b,
        self::SEVERITY_CRITICAL => 0xef4444,
    ];

    private const SEVERITY_EMOJIS = [
        self::SEVERITY_SUCCESS => '✅',
        self::SEVERITY_INFO => '📣',
        self::SEVERITY_WARNING => '⚠️',
        self::SEVERITY_CRITICAL => '🚨',
    ];

    public function __construct(private readonly IntegrationEvent $event)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        $content = $this->buildContent();
        $payload = [
            'username' => config('services.discord.username', config('app.name').' · LMS'),
            'avatar_url' => config('services.discord.avatar'),
            'allowed_mentions' => ['parse' => []],
            'embeds' => [$this->buildEmbed()],
        ];

        if ($content) {
            $payload['content'] = $content;
        }

        if ($threadId = config('services.discord.thread_id')) {
            $payload['thread_id'] = $threadId;
        }

        return array_filter($payload, fn ($value) => ! is_null($value));
    }

    private function buildContent(): string
    {
        $severity = $this->resolveSeverity();
        $emoji = self::SEVERITY_EMOJIS[$severity] ?? self::SEVERITY_EMOJIS[self::SEVERITY_INFO];

        return sprintf(
            '%s `%s` · %s',
            $emoji,
            $this->eventTitle(),
            Str::upper(config('app.env'))
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildEmbed(): array
    {
        $fields = array_values(array_filter([
            $this->highlightField(),
            $this->metaField(),
            $this->payloadField(),
        ]));

        return [
            'title' => $this->eventTitle(),
            'description' => $this->buildDescription(),
            'color' => $this->resolveColor(),
            'fields' => $fields,
            'footer' => [
                'text' => sprintf('Outbox #%d · %s', $this->event->id, config('app.name')),
            ],
            'timestamp' => now()->toIso8601String(),
        ];
    }

    private function buildDescription(): string
    {
        $lines = [
            sprintf('Entorno: %s', Str::upper(config('app.env'))),
            sprintf('Destino: %s', Str::headline($this->event->target)),
            sprintf('Intentos: %d', $this->event->attempts),
        ];

        if ($link = $this->outboxUrl()) {
            $lines[] = sprintf('[Abrir outbox](%s)', $link);
        }

        return implode("\n", $lines);
    }

    /**
     * @return array{name: string, value: string, inline: bool}|null
     */
    private function highlightField(): ?array
    {
        return match ($this->event->event) {
            'assignment.approved', 'assignment.rejected' => $this->assignmentField(),
            'certificate.issued' => $this->certificateField(),
            'offer.launched' => $this->offerField(),
            'tier.updated' => $this->tierField(),
            'course.unlocked', 'module.unlocked' => $this->courseField(),
            'subscriptions.expiring', 'subscriptions.expired' => $this->subscriptionField(),
            'payments.simulated' => $this->paymentField(),
            'practice.package.published' => $this->packageField(),
            'practice.package.purchased' => $this->packageOrderField(),
            'discord.practice.scheduled' => $this->practiceScheduledField(),
            'discord.practice.reserved' => $this->practiceReservedField(),
            'discord.practice.requests_escalated' => $this->practiceEscalationField(),
            default => null,
        };
    }

    private function assignmentField(): ?array
    {
        $title = $this->payloadValue('assignment.title');
        $course = $this->payloadValue('assignment.course');
        $student = $this->payloadValue('student.name');
        $score = $this->payloadValue('submission.score');
        $status = Str::headline(Str::after($this->event->event, 'assignment.'));

        $lines = array_filter([
            $title ? "Tarea: {$title}" : null,
            $course ? "Curso: {$course}" : null,
            $student ? "Alumno: {$student}" : null,
            $score ? "Puntaje: {$score}" : null,
            "Estado: {$status}",
        ]);

        if (empty($lines)) {
            return null;
        }

        return [
            'name' => 'Tarea',
            'value' => $this->limitField(implode("\n", $lines)),
            'inline' => false,
        ];
    }

    private function certificateField(): ?array
    {
        $code = $this->payloadValue('certificate.code');
        $student = $this->payloadValue('student.name');
        $course = $this->payloadValue('course.slug');
        $url = $this->payloadValue('certificate.url');

        $lines = array_filter([
            $student ? "Alumno: {$student}" : null,
            $course ? "Curso: {$course}" : null,
            $code ? "Código: `{$code}`" : null,
            $url ? "[Ver certificado]({$url})" : null,
        ]);

        if (empty($lines)) {
            return null;
        }

        return [
            'name' => 'Certificado',
            'value' => $this->limitField(implode("\n", $lines)),
            'inline' => false,
        ];
    }

    private function offerField(): ?array
    {
        $title = $this->payloadValue('offer_title');
        $tier = $this->payloadValue('tier_label');
        $price = $this->payloadValue('price');
        $discount = $this->payloadValue('discount');
        $url = $this->payloadValue('offer_url');

        $lines = array_filter([
            $title ? "Oferta: {$title}" : null,
            $tier ? "Tier: {$tier}" : null,
            $price ? "Precio: {$price}" : null,
            $discount ? "Descuento: {$discount}" : null,
            $url ? "[Abrir oferta]({$url})" : null,
        ]);

        if (empty($lines)) {
            return null;
        }

        return [
            'name' => 'Oferta',
            'value' => $this->limitField(implode("\n", $lines)),
            'inline' => false,
        ];
    }

    private function tierField(): ?array
    {
        $name = $this->payloadValue('tier_name');
        $slug = $this->payloadValue('tier_slug');
        $isActive = $this->payloadValue('is_active');

        $lines = array_filter([
            $name ? "Nombre: {$name}" : null,
            $slug ? "Slug: {$slug}" : null,
            $isActive !== null ? ('Activo: '.($isActive ? 'sí' : 'no')) : null,
        ]);

        if (empty($lines)) {
            return null;
        }

        return [
            'name' => 'Tier',
            'value' => $this->limitField(implode("\n", $lines)),
            'inline' => false,
        ];
    }

    private function courseField(): ?array
    {
        $title = $this->payloadValue('course_title');
        $slug = $this->payloadValue('course_slug');
        $audience = $this->payloadValue('audience');

        $lines = array_filter([
            $title ? "Curso: {$title}" : null,
            $slug ? "Slug: {$slug}" : null,
            $audience ? "Audiencia: {$audience}" : null,
        ]);

        if (empty($lines)) {
            return null;
        }

        return [
            'name' => 'Curso',
            'value' => $this->limitField(implode("\n", $lines)),
            'inline' => false,
        ];
    }

    private function subscriptionField(): ?array
    {
        $tier = $this->payloadValue('tier');
        $name = $this->payloadValue('name');
        $renew = $this->payloadValue('renews_at') ?? $this->payloadValue('expired_at');

        $lines = array_filter([
            $name ? "Alumno: {$name}" : null,
            $tier ? "Tier: {$tier}" : null,
            $renew ? "Fecha: {$renew}" : null,
        ]);

        if (empty($lines)) {
            return null;
        }

        return [
            'name' => 'Suscripción',
            'value' => $this->limitField(implode("\n", $lines)),
            'inline' => false,
        ];
    }

    private function paymentField(): ?array
    {
        $tier = $this->payloadValue('tier');
        $amount = $this->payloadValue('amount');
        $currency = $this->payloadValue('currency');
        $name = $this->payloadValue('name');

        $lines = array_filter([
            $name ? "Alumno: {$name}" : null,
            $tier ? "Tier: {$tier}" : null,
            $amount ? "Monto: {$amount} {$currency}" : null,
        ]);

        if (empty($lines)) {
            return null;
        }

        return [
            'name' => 'Pago simulado',
            'value' => $this->limitField(implode("\n", $lines)),
            'inline' => false,
        ];
    }

    private function packageField(): ?array
    {
        $title = $this->payloadValue('package.title');
        $sessions = $this->payloadValue('package.sessions');
        $price = $this->payloadValue('package.price');
        $currency = $this->payloadValue('package.currency');

        $lines = array_filter([
            $title ? "Pack: {$title}" : null,
            $sessions ? "Sesiones: {$sessions}" : null,
            $price ? "Precio: {$price} {$currency}" : null,
        ]);

        if (empty($lines)) {
            return null;
        }

        return [
            'name' => 'Pack publicado',
            'value' => $this->limitField(implode("\n", $lines)),
            'inline' => false,
        ];
    }

    private function packageOrderField(): ?array
    {
        $title = $this->payloadValue('package.title');
        $sessions = $this->payloadValue('order.sessions_remaining');
        $student = $this->payloadValue('student.name');

        $lines = array_filter([
            $student ? "Alumno: {$student}" : null,
            $title ? "Pack: {$title}" : null,
            $sessions !== null ? "Sesiones restantes: {$sessions}" : null,
        ]);

        if (empty($lines)) {
            return null;
        }

        return [
            'name' => 'Compra realizada',
            'value' => $this->limitField(implode("\n", $lines)),
            'inline' => false,
        ];
    }

    private function practiceScheduledField(): ?array
    {
        $title = $this->payloadValue('practice.title');
        $start = $this->payloadValue('practice.start_at');
        $capacity = $this->payloadValue('practice.capacity');

        $lines = array_filter([
            $title ? "Sesión: {$title}" : null,
            $start ? "Inicio: {$start}" : null,
            $capacity ? "Capacidad: {$capacity}" : null,
        ]);

        if (empty($lines)) {
            return null;
        }

        return [
            'name' => 'Nueva práctica publicada',
            'value' => $this->limitField(implode("\n", $lines)),
            'inline' => false,
        ];
    }

    private function practiceReservedField(): ?array
    {
        $title = $this->payloadValue('practice.title');
        $student = $this->payloadValue('student.name');
        $start = $this->payloadValue('practice.start_at');

        $lines = array_filter([
            $student ? "Estudiante: {$student}" : null,
            $title ? "Sesión: {$title}" : null,
            $start ? "Inicio: {$start}" : null,
        ]);

        if (empty($lines)) {
            return null;
        }

        return [
            'name' => 'Reserva confirmada',
            'value' => $this->limitField(implode("\n", $lines)),
            'inline' => false,
        ];
    }

    private function practiceEscalationField(): ?array
    {
        $lesson = $this->payloadValue('lesson.title');
        $course = $this->payloadValue('lesson.course');
        $pending = $this->payloadValue('pending');

        $lines = array_filter([
            $lesson ? "Lección: {$lesson}" : null,
            $course ? "Curso: {$course}" : null,
            $pending ? "Solicitudes pendientes: {$pending}" : null,
        ]);

        if (empty($lines)) {
            return null;
        }

        return [
            'name' => 'Solicitudes acumuladas',
            'value' => $this->limitField(implode("\n", $lines)),
            'inline' => false,
        ];
    }

    /**
     * @return array{name: string, value: string, inline: bool}
     */
    private function metaField(): array
    {
        $lines = [
            sprintf('Evento: `%s`', $this->event->event),
            sprintf('Target: %s', Str::headline($this->event->target)),
            sprintf('Outbox ID: %d', $this->event->id),
        ];

        if ($link = $this->outboxUrl()) {
            $lines[] = sprintf('[Ver detalle](%s)', $link);
        }

        return [
            'name' => 'Meta',
            'value' => $this->limitField(implode("\n", $lines)),
            'inline' => false,
        ];
    }

    /**
     * @return array{name: string, value: string, inline: bool}
     */
    private function payloadField(): array
    {
        $json = json_encode(
            $this->event->payload ?? [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) ?: '{}';

        $snippet = Str::limit($json, 900, ' …');

        return [
            'name' => 'Payload',
            'value' => $this->limitField("```json\n{$snippet}\n```"),
            'inline' => false,
        ];
    }

    private function resolveSeverity(): string
    {
        return self::SEVERITIES[$this->event->event] ?? self::SEVERITY_INFO;
    }

    private function resolveColor(): int
    {
        $severity = $this->resolveSeverity();

        return self::SEVERITY_COLORS[$severity] ?? self::SEVERITY_COLORS[self::SEVERITY_INFO];
    }

    private function eventTitle(): string
    {
        return Str::of($this->event->event)
            ->replace(['.', '_'], ' ')
            ->headline()
            ->toString();
    }

    private function payloadValue(string $key, ?string $default = null): mixed
    {
        return Arr::get($this->event->payload ?? [], $key, $default);
    }

    private function limitField(string $value): string
    {
        return Str::limit($value, 1000, ' …');
    }

    private function outboxUrl(): ?string
    {
        try {
            return route('admin.integrations.outbox', ['event_id' => $this->event->id]);
        } catch (\Throwable) {
            $base = rtrim(config('app.url', ''), '/');

            return $base ? "{$base}/admin/integrations/outbox?event={$this->event->id}" : null;
        }
    }
}


