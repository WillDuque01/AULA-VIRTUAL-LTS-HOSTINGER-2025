<?php

namespace App\Notifications;

use App\Models\DiscordPractice;
use App\Models\PracticePackage;
use App\Models\PracticePackageOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;

class DiscordPracticeSlotAvailableNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected bool $evaluatedPack = false;

    protected ?array $basePackRecommendation = null;

    public function __construct(public DiscordPractice $practice)
    {
        $this->practice = $practice->loadMissing('lesson.chapter.course', 'package');
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lessonTitle = data_get($this->practice->lesson->config, 'title', __('Lección solicitada'));
        $course = $this->practice->lesson->chapter?->course?->slug;

        return (new MailMessage)
            ->subject(__('¡Hay un nuevo cupo para tu práctica solicitada!'))
            ->greeting(__('Hola :name,', ['name' => $notifiable->name]))
            ->line(__('Abrimos un nuevo slot para ":lesson"', ['lesson' => $lessonTitle]))
            ->line(__('Inicio: :date', ['date' => optional($this->practice->start_at)->translatedFormat('d M H:i')]))
            ->line(__('Curso / cohorte: :course', ['course' => $course ?: __('General')]))
            ->action(__('Reservar ahora'), $this->practiceRoute())
            ->line(__('Te recomendamos confirmar cuanto antes: los cupos se asignan por orden de reserva.'));

        if ($pack = $this->packRecommendationFor($notifiable)) {
            $mail->line(__('Pack recomendado: :title (:sessions sesiones)', [
                'title' => $pack['title'],
                'sessions' => $pack['sessions'],
            ]));

            if (! empty($pack['price_amount'])) {
                $formatted = number_format($pack['price_amount'], 0);
                $mail->line(__('Inversión total: $:amount :currency', [
                    'amount' => $formatted,
                    'currency' => $pack['currency'],
                ]));
            }

            if (! empty($pack['price_per_session'])) {
                $mail->line(__('≈ $:amount por sesión', [
                    'amount' => number_format($pack['price_per_session'], 1),
                ]));
            }

            if ($pack['requires_package'] && ! $pack['has_order']) {
                $mail->line(__('Este slot requiere un pack activo. Puedes activarlo aquí:'));
            } elseif (! $pack['has_order']) {
                $mail->line(__('Activa tu pack para tener prioridad permanente en la agenda.'));
            } else {
                $mail->line(__('Ya tienes un pack activo, solo reserva el slot para usar tus sesiones.'));
            }

            if (! $pack['has_order']) {
                $mail->action(__('Ver packs y beneficios'), $this->packCtaUrl());
            }
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'practice_id' => $this->practice->id,
            'lesson_id' => $this->practice->lesson_id,
            'title' => $this->practice->title,
            'start_at' => optional($this->practice->start_at)->toIso8601String(),
            'practice_url' => $this->practiceRoute(),
            'packs_url' => $this->packCtaUrl(),
            'pack_recommendation' => $this->packRecommendationFor($notifiable),
        ];
    }

    private function practiceRoute(): string
    {
        if (Route::has('student.discord-practices')) {
            return route('student.discord-practices', ['locale' => app()->getLocale()]);
        }

        return route('dashboard', ['locale' => app()->getLocale()]);
    }

    private function packCtaUrl(): string
    {
        return route('dashboard', ['locale' => app()->getLocale()]).'#practice-packs';
    }

    private function packRecommendationFor(object $notifiable): ?array
    {
        $base = $this->basePackRecommendation();

        if (! $base) {
            return null;
        }

        $hasOrder = false;
        if ($notifiable instanceof User) {
            $hasOrder = PracticePackageOrder::where('practice_package_id', $base['id'])
                ->where('user_id', $notifiable->getKey())
                ->whereIn('status', ['paid', 'completed'])
                ->exists();
        }

        return $base + ['has_order' => $hasOrder];
    }

    private function basePackRecommendation(): ?array
    {
        if ($this->evaluatedPack) {
            return $this->basePackRecommendation;
        }

        $this->evaluatedPack = true;

        $package = $this->resolveRecommendedPackage();
        if (! $package) {
            $this->basePackRecommendation = null;

            return null;
        }

        $perSession = $package->sessions_count > 0
            ? round((float) $package->price_amount / $package->sessions_count, 2)
            : null;

        $this->basePackRecommendation = [
            'id' => $package->id,
            'title' => $package->title,
            'sessions' => (int) $package->sessions_count,
            'price_amount' => (float) $package->price_amount,
            'currency' => $package->price_currency,
            'price_per_session' => $perSession,
            'requires_package' => (bool) $this->practice->requires_package,
        ];

        return $this->basePackRecommendation;
    }

    private function resolveRecommendedPackage(): ?PracticePackage
    {
        if (! $this->shouldRecommendPack()) {
            return null;
        }

        $package = $this->practice->package;
        if ($package && $package->status === 'published') {
            return $package;
        }

        if ($this->practice->lesson_id) {
            $lessonPack = PracticePackage::where('status', 'published')
                ->where('lesson_id', $this->practice->lesson_id)
                ->orderByDesc('updated_at')
                ->first();

            if ($lessonPack) {
                return $lessonPack;
            }
        }

        return PracticePackage::where('status', 'published')
            ->where('is_global', true)
            ->orderBy('price_amount')
            ->first();
    }

    private function shouldRecommendPack(): bool
    {
        return (bool) ($this->practice->requires_package || $this->practice->practice_package_id);
    }
}

