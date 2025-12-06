<?php

namespace App\Livewire\Student;

use App\Events\DiscordPracticeRequestEscalated;
use App\Events\DiscordPracticeReserved;
use App\Models\DiscordPractice;
use App\Models\DiscordPracticeRequest;
use App\Models\DiscordPracticeReservation;
use App\Models\Lesson;
use App\Models\PracticePackageOrder;
use App\Notifications\DiscordPracticeSlotAvailableNotification;
use App\Services\PracticePackageOrderService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Illuminate\Support\Collection as SupportCollection;

class DiscordPracticeBrowser extends Component
{
    public SupportCollection $availableLessons;
    public SupportCollection $practices;

    public array $lessonGroups = []; // [AGENTE: GPT-5.1 CODEX] - Fuente para el selector agrupado

    public ?int $selectedLesson = null;

    public array $statusMessages = [];

    public ?array $packReminder = null;

    public ?string $packNotificationId = null;

    public string $packsUrl = ''; // [AGENTE: GPT-5.1 CODEX] - URL base hacia el catálogo de packs

    public function mount(): void
    {
        $locale = request()->route('locale') ?? app()->getLocale();
        $this->packsUrl = $this->buildPackUrl();

        $this->availableLessons = DiscordPractice::with('lesson.chapter.course')
            ->where('start_at', '>=', now())
            ->where('status', 'scheduled')
            ->get()
            ->map(fn ($practice) => $practice->lesson)
            ->filter()
            ->unique('id')
            ->values();

        $this->lessonGroups = $this->formatLessonGroups(); // [AGENTE: GPT-5.1 CODEX] - Alimenta el componente agrupado

        $this->loadPractices();
        $this->loadPackReminder();
    }

    public function updatedSelectedLesson(): void
    {
        $this->loadPractices();
    }

    public function reserve(int $practiceId, PracticePackageOrderService $orderService): void
    {
        $user = auth()->user();
        $practice = DiscordPractice::withCount([
                'reservations as confirmed_reservations_count' => fn ($query) => $query->where('status', '!=', 'cancelled'),
            ])
            ->findOrFail($practiceId);

        if ($practice->start_at->isPast() || $practice->status !== 'scheduled') {
            $message = __('Esta práctica ya no está disponible.');
            $this->addError('reservation', $message);
            $this->notify($message, 'error'); // [AGENTE: GPT-5.1 CODEX] - Feedback inmediato

            return;
        }

        if ($practice->confirmed_reservations_count >= $practice->capacity) {
            $message = __('No hay cupos disponibles.');
            $this->addError('reservation', $message);
            $this->notify($message, 'error'); // [AGENTE: GPT-5.1 CODEX] - Comunica el bloqueo

            return;
        }

        $order = null;
        if ($practice->requires_package) {
            $order = $this->resolveEligibleOrder($user->id, $practice);

            if (! $order) {
                $message = __('Necesitas un pack activo para reservar.');
                $this->addError('reservation', $message);
                $this->notify($message, 'error'); // [AGENTE: GPT-5.1 CODEX] - Explica el requisito

                return;
            }
        }

        $reservation = DB::transaction(function () use ($practice, $user, $orderService, $order) {
            if ($order) {
                $orderService->consumeSession($order);
            }

            return DiscordPracticeReservation::updateOrCreate(
                [
                    'discord_practice_id' => $practice->id,
                    'user_id' => $user->id,
                ],
                [
                    'status' => 'confirmed',
                    'practice_package_order_id' => $order?->id,
                    'cancelled_at' => null,
                ]
            );
        });

        event(new DiscordPracticeReserved(
            $practice->fresh(['creator', 'lesson.chapter.course']),
            $reservation->fresh('user')
        ));

        $this->statusMessages[$practice->id] = __('Reserva confirmada');
        $this->loadPractices();
        $this->dismissPackReminder();
        $this->dispatch('practice-reserved');
        $this->notify(__('Cupo reservado correctamente'), 'success'); // [AGENTE: GPT-5.1 CODEX] - Confirma la acción
    }

    public function cancelReservation(int $practiceId, PracticePackageOrderService $orderService): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $practice = DiscordPractice::with(['reservations' => fn ($query) => $query->where('user_id', $user->id)])->findOrFail($practiceId);
        $reservation = $practice->reservations->first();

        if (! $reservation || $reservation->status === 'cancelled') {
            $message = __('No tienes una reserva activa para este slot.');
            $this->addError('reservation', $message);
            $this->notify($message, 'error'); // [AGENTE: GPT-5.1 CODEX] - Aviso cuando no existe reserva

            return;
        }

        if ($practice->start_at->isPast()) {
            $message = __('No puedes cancelar una sesión que ya inició.');
            $this->addError('reservation', $message);
            $this->notify($message, 'error'); // [AGENTE: GPT-5.1 CODEX] - Bloqueo por horario

            return;
        }

        $reservation->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        if ($reservation->packageOrder) {
            $orderService->restoreSession($reservation->packageOrder);
        }

        $this->statusMessages[$practice->id] = __('Reserva cancelada');
        $this->loadPractices();
        $this->dispatch('practice-cancelled');
        $this->notify(__('Reserva cancelada correctamente'), 'info'); // [AGENTE: GPT-5.1 CODEX] - Confirma la cancelación
    }

    private function resolveEligibleOrder(int $userId, DiscordPractice $practice): ?PracticePackageOrder
    {
        $query = PracticePackageOrder::with('package')
            ->where('user_id', $userId)
            ->where('status', 'paid')
            ->where('sessions_remaining', '>', 0);

        if ($practice->practice_package_id) {
            $query->where('practice_package_id', $practice->practice_package_id);
        } else {
            $query->whereHas('package', function ($q) use ($practice) {
                $q->where('creator_id', $practice->created_by);
            });
        }

        return $query->first();
    }

    public function requestSlot(?int $lessonId = null): void
    {
        $lesson = $lessonId ?? $this->selectedLesson;
        if (! $lesson) {
            $message = __('Selecciona una lección para solicitar sesión.');
            $this->addError('request', $message);
            $this->notify($message, 'error'); // [AGENTE: GPT-5.1 CODEX] - Indica que falta elegir lección

            return;
        }

        DiscordPracticeRequest::firstOrCreate([
            'lesson_id' => $lesson,
            'user_id' => auth()->id(),
            'status' => 'pending',
        ]);

        $this->dispatch('practice-requested');
        $this->maybeEscalateRequests($lesson);
        $this->notify(__('Solicitud enviada. Te avisaremos cuando se abra un cupo.'), 'success'); // [AGENTE: GPT-5.1 CODEX] - Confirma la solicitud
    }

    public function resetFilters(): void
    {
        $this->selectedLesson = null; // [AGENTE: GPT-5.1 CODEX] - Limpia la selección activa
        $this->loadPractices(); // [AGENTE: GPT-5.1 CODEX] - Refresca la lista completa
        $this->notify(__('Filtros restaurados'), 'info'); // [AGENTE: GPT-5.1 CODEX] - Comunica la acción al estudiante
    }

    public function dismissPackReminder(): void
    {
        if ($this->packNotificationId && auth()->check()) {
            $notification = auth()->user()
                ->notifications()
                ->whereKey($this->packNotificationId)
                ->first();

            $notification?->markAsRead();
        }

        $this->packReminder = null;
        $this->packNotificationId = null;
    }

    private function loadPractices(): void
    {
        $user = auth()->user();
        $activeOrders = collect();

        if ($user) {
            $activeOrders = PracticePackageOrder::with('package')
                ->where('user_id', $user->id)
                ->whereIn('status', ['paid', 'completed'])
                ->where('sessions_remaining', '>', 0)
                ->get();
        }

        $query = DiscordPractice::with(['lesson.chapter.course'])
            ->with(['reservations' => function ($builder) use ($user) {
                if ($user) {
                    $builder->where('user_id', $user->id);
                } else {
                    $builder->whereRaw('1 = 0');
                }
            }])
            ->withCount(['reservations as confirmed_reservations_count' => fn ($builder) => $builder->where('status', '!=', 'cancelled')])
            ->where('start_at', '>=', now())
            ->where('status', 'scheduled');

        if ($this->selectedLesson) {
            $query->where('lesson_id', $this->selectedLesson);
        }

        $this->practices = $query
            ->orderBy('start_at')
            ->limit(10)
            ->get()
            ->map(function (DiscordPractice $practice) use ($user, $activeOrders) {
                $reserved = (int) $practice->confirmed_reservations_count;
                $userReservation = $practice->reservations->first();
                $hasReservation = $userReservation && $userReservation->status !== 'cancelled';
                $canCancel = $hasReservation && $practice->start_at && $practice->start_at->isFuture();

                $hasRequiredPack = false;
                if ($practice->requires_package && $user) {
                    $hasRequiredPack = $activeOrders->contains(function (PracticePackageOrder $order) use ($practice) {
                        if ($practice->practice_package_id) {
                            return $order->practice_package_id === $practice->practice_package_id;
                        }

                        return $order->package && (int) $order->package->creator_id === (int) $practice->created_by;
                    });
                }

                return [
                    'id' => $practice->id,
                    'title' => $practice->title,
                    'lesson' => data_get($practice->lesson->config, 'title', __('Lesson')),
                    'course' => $practice->lesson->chapter?->course?->slug,
                    'start_at' => $practice->start_at,
                    'capacity' => $practice->capacity,
                    'reserved' => $reserved,
                    'available' => max(0, $practice->capacity - $reserved),
                    'has_reservation' => $hasReservation,
                    'can_cancel' => $canCancel,
                    'reservation_status' => $userReservation?->status,
                    'discord_channel_url' => $practice->discord_channel_url,
                    'lesson_id' => $practice->lesson_id,
                    'requires_package' => $practice->requires_package,
                    'has_required_pack' => $hasRequiredPack,
                    'practice_package_id' => $practice->practice_package_id,
                    'pack_url' => $this->buildPackUrl($practice->practice_package_id),
                ];
            });
    }

    private function loadPackReminder(): void
    {
        $user = auth()->user();
        if (! $user || ! Schema::hasTable('notifications')) {
            $this->packReminder = null;
            $this->packNotificationId = null;

            return;
        }

        /** @var DatabaseNotification|null $notification */
        $notification = $user->unreadNotifications()
            ->where('type', DiscordPracticeSlotAvailableNotification::class)
            ->latest()
            ->first();

        if (! $notification) {
            $this->packReminder = null;
            $this->packNotificationId = null;

            return;
        }

        $pack = data_get($notification->data, 'pack_recommendation');
        if (! $pack || ($pack['has_order'] ?? false)) {
            $this->packReminder = null;
            $this->packNotificationId = null;

            return;
        }

        $startAt = data_get($notification->data, 'start_at');

        $this->packNotificationId = $notification->id;
        $this->packReminder = [
            'practice_title' => data_get($notification->data, 'title'),
            'start_at' => $startAt ? Carbon::parse($startAt) : null,
            'practice_url' => data_get($notification->data, 'practice_url'),
            'packs_url' => data_get($notification->data, 'packs_url') ?? $this->buildPackUrl((int) data_get($pack, 'id')),
            'pack' => [
                'title' => data_get($pack, 'title'),
                'sessions' => data_get($pack, 'sessions'),
                'price_amount' => data_get($pack, 'price_amount'),
                'currency' => data_get($pack, 'currency'),
                'price_per_session' => data_get($pack, 'price_per_session'),
                'requires_package' => (bool) data_get($pack, 'requires_package'),
                'id' => data_get($pack, 'id'),
            ],
        ];
    }

    private function maybeEscalateRequests(int $lessonId): void
    {
        $threshold = (int) config('services.discord_practices.request_threshold', 3);
        if ($threshold <= 0) {
            return;
        }

        $pending = DiscordPracticeRequest::where('lesson_id', $lessonId)
            ->where('status', 'pending')
            ->count();

        $cacheKey = "discord-practice:requests:lesson:{$lessonId}";

        if ($pending < $threshold) {
            Cache::forget($cacheKey);

            return;
        }

        $cooldown = (int) config('services.discord_practices.request_cooldown_minutes', 240);

        if (! Cache::add($cacheKey, true, now()->addMinutes($cooldown))) {
            return;
        }

        $lesson = Lesson::with('chapter.course')->find($lessonId);

        if ($lesson) {
            event(new DiscordPracticeRequestEscalated($lesson, $pending));
        }
    }

    private function buildPackUrl(?int $packId = null): string
    {
        $locale = request()->route('locale') ?? app()->getLocale();
        $base = route('dashboard', ['locale' => $locale]);
        $query = $packId ? '?pack='.$packId : '';

        return $base.$query.'#practice-packs';
    }

    private function formatLessonGroups(): array
    {
        return $this->availableLessons
            ->filter()
            ->groupBy(function (Lesson $lesson) {
                return $lesson->chapter?->course?->slug ?? __('Curso sin asignar');
            })
            ->map(function (SupportCollection $lessons) {
                return $lessons
                    ->sortBy('position')
                    ->mapWithKeys(function (Lesson $lesson) {
                        $course = $lesson->chapter?->course?->slug ?? __('Curso');
                        $title = data_get($lesson->config, 'title', __('Lesson'));

                        return [$lesson->id => $course.' · '.$title];
                    })
                    ->toArray();
            })
            ->toArray(); // [AGENTE: GPT-5.1 CODEX] - Estructura id => label agrupada por curso
    }

    private function notify(string $message, string $style = 'success'): void
    {
        $this->dispatch('notify', message: $message, style: $style); // [AGENTE: GPT-5.1 CODEX] - Helper para los toasts globales
    }

    public function render()
    {
        return view('livewire.student.discord-practice-browser', [
            'packsUrl' => $this->packsUrl,
        ]);
    }
}




