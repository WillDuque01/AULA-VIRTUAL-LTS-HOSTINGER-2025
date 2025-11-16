@php
    $title = data_get($lesson->config, 'title', $lesson->chapter?->title.' — Lección '.$lesson->position);
    $resumeSeconds = max(0, (int) $resumeAt);
    $durationSeconds = $duration ?? null;
    $resumeLabel = $resumeSeconds > 0 ? gmdate('H:i:s', $resumeSeconds) : 'Inicio';
    $bodyContent = data_get($lesson->config, 'body');
    $resourceUrl = $resourceUrl ?? data_get($lesson->config, 'resource_url');
    $estimation = $estimatedMinutes ? $estimatedMinutes.' min' : null;
    $assignmentStatusMeta = [
        'pending' => ['label' => __('player.timeline.assignment.pending'), 'class' => 'bg-slate-200 text-slate-700'],
        'submitted' => ['label' => __('player.timeline.assignment.submitted'), 'class' => 'bg-sky-100 text-sky-700'],
        'graded' => ['label' => __('player.timeline.assignment.graded'), 'class' => 'bg-violet-100 text-violet-700'],
        'approved' => ['label' => __('player.timeline.assignment.approved'), 'class' => 'bg-emerald-100 text-emerald-700'],
        'rejected' => ['label' => __('player.timeline.assignment.rejected'), 'class' => 'bg-rose-100 text-rose-700'],
    ];
    $typeGlyphs = [
        'video' => '▶️',
        'audio' => '🎧',
        'pdf' => '📄',
        'text' => '📝',
        'assignment' => '🧾',
        'quiz' => '❓',
        'iframe' => '🌐',
        'default' => '📘',
    ];
    $practiceRoute = \Illuminate\Support\Facades\Route::has('student.discord-practices')
        ? route('student.discord-practices', ['locale' => app()->getLocale()])
        : null;
@endphp

<div class="grid gap-6 lg:grid-cols-[320px,1fr]">
    <aside
        class="lg:sticky lg:top-28 space-y-4"
        x-data="playerInsights({{ json_encode([
            'progress' => $progressPercent,
            'duration' => $durationSeconds ?? 0,
            'resume' => $resumeSeconds,
        ]) }})">
        <div class="rounded-3xl border border-slate-100 bg-white/85 p-4 shadow-xl shadow-slate-200/60">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] uppercase font-semibold tracking-wide text-slate-400">{{ __('player.timeline.title') }}</p>
                    <p class="text-sm font-semibold text-slate-900">{{ $courseTitle ?? __('player.timeline.untitled_course') }}</p>
                </div>
                <span class="text-xl" aria-hidden="true">🧭</span>
            </div>
            <div class="mt-4 space-y-4 max-h-[70vh] overflow-y-auto pr-1" data-player-timeline>
                @forelse($timeline as $block)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $block['title'] ?? __('player.timeline.chapter_fallback') }}</p>
                        <div class="mt-2 space-y-4 relative pl-6">
                            <span class="pointer-events-none absolute left-2 top-0 bottom-0 w-px bg-slate-100"></span>
                            @foreach($block['lessons'] as $timelineLesson)
                                @php
                                    $isCurrent = $timelineLesson['current'] ?? false;
                                    $statusKey = $timelineLesson['status'] ?? null;
                                    $statusMeta = $assignmentStatusMeta[$statusKey] ?? null;
                                    $glyph = $typeGlyphs[$timelineLesson['type']] ?? $typeGlyphs['default'];
                                    $dotClasses = match (true) {
                                        $isCurrent => 'bg-emerald-400 shadow-[0_0_0_6px_rgba(16,185,129,0.18)] motion-safe:animate-pulse',
                                        $statusKey === 'approved' => 'bg-emerald-300 shadow-[0_0_0_4px_rgba(16,185,129,.08)]',
                                        $statusKey === 'rejected' => 'bg-rose-300 shadow-[0_0_0_4px_rgba(244,114,182,.12)]',
                                        $statusKey === 'submitted' || $statusKey === 'graded' => 'bg-sky-300',
                                        default => 'bg-slate-300',
                                    };
                                    $itemClasses = $isCurrent
                                        ? 'bg-slate-900 text-white shadow-lg shadow-slate-500/40 scale-[1.01]'
                                        : 'bg-white text-slate-700 border border-slate-100 hover:border-slate-300 hover:shadow-md';
                                @endphp
                                <div class="relative">
                                    <span class="pointer-events-none absolute -left-5 top-1/2 -translate-y-1/2 flex h-4 w-4 items-center justify-center">
                                        <span class="h-2.5 w-2.5 rounded-full {{ $dotClasses }}"></span>
                                    </span>
                                    <a href="{{ route('lessons.player', ['locale' => app()->getLocale(), 'lesson' => $timelineLesson['id']]) }}"
                                       class="group flex items-center gap-3 rounded-2xl px-3 py-2 text-sm transition transform {{ $itemClasses }}"
                                       data-timeline-link
                                       @if($isCurrent) aria-current="true" @endif>
                                        <span class="text-base" aria-hidden="true">{{ $glyph }}</span>
                                        <span class="flex-1">
                                            <span class="block font-semibold">{{ $timelineLesson['title'] }}</span>
                                            <span class="block text-[11px] text-slate-500 group-hover:text-slate-600">
                                                {{ ucfirst($timelineLesson['type']) }}
                                                @if($timelineLesson['requiresApproval'] ?? false)
                                                    · {{ __('player.timeline.requires_approval_badge') }}
                                                @endif
                                            </span>
                                        </span>
                                        @if($statusMeta)
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $statusMeta['class'] }}">
                                                {{ $statusMeta['label'] }}
                                            </span>
                                        @endif
                                    </a>
                                </div>
                                @if($isCurrent && $practiceCta)
                                    <div class="ml-8 mt-2 rounded-2xl border border-indigo-100 bg-indigo-50/70 px-3 py-2 text-xs text-indigo-900">
                                        <p class="font-semibold">🎉 {{ __('Práctica disponible para esta lección') }}</p>
                                        <p class="text-[11px] text-indigo-700">
                                            {{ optional($practiceCta['start_at'])->translatedFormat('d M H:i') ?? __('Próximamente') }}
                                            · {{ $practiceCta['available'] }} / {{ $practiceCta['capacity'] }} {{ __('cupos') }}
                                        </p>
                                        @if($practiceRoute)
                                            <a href="{{ $practiceRoute }}"
                                               class="mt-1 inline-flex items-center gap-1 rounded-full bg-indigo-600 px-3 py-1 text-[11px] font-semibold text-white shadow hover:bg-indigo-700">
                                                {{ __('Abrir agenda') }} ↗
                                            </a>
                                        @endif
                                    </div>
                                @elseif($isCurrent && $practicePackCta)
                                    <div class="ml-8 mt-2 rounded-2xl border border-emerald-100 bg-emerald-50/60 px-3 py-2 text-xs text-emerald-900">
                                        <p class="font-semibold">✨ {{ __('Pack de prácticas recomendado') }}</p>
                                        <p class="text-[11px] text-emerald-700">
                                            {{ $practicePackCta['sessions'] }} {{ __('sesiones') }} · {{ number_format($practicePackCta['price'], 0) }} {{ $practicePackCta['currency'] }}
                                        </p>
                                        @if($practiceRoute)
                                            <a href="{{ $practiceRoute }}"
                                               class="mt-1 inline-flex items-center gap-1 rounded-full bg-emerald-600 px-3 py-1 text-[11px] font-semibold text-white shadow hover:bg-emerald-700">
                                                {{ $practicePackCta['has_order'] ? __('Gestionar sesiones') : __('Ver packs') }} ↗
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">{{ __('player.timeline.empty') }}</p>
                @endforelse
            </div>
        </div>
    </aside>

    <div>
@if($isLocked)
    <div class="space-y-6">
        <div class="rounded-2xl border border-amber-200 bg-amber-50/80 p-6 shadow-inner">
            <div class="flex items-center gap-3 text-amber-900">
                <span class="text-2xl">🔒</span>
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide">Lección bloqueada</p>
                    <p class="text-base font-semibold">{{ $lockReason }}</p>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-4 text-sm text-amber-800/90">
                @if($releaseAtHuman)
                    <div class="flex items-center gap-2">
                        <span class="text-xl">⏳</span>
                        Disponible en {{ $releaseAtHuman }}
                    </div>
                @endif
                @if($prerequisiteLesson)
                    <div class="flex items-center gap-2">
                        <span class="text-xl">✅</span>
                        Completa "{{ data_get($prerequisiteLesson->config, 'title', $prerequisiteLesson->chapter?->title) }}"
                    </div>
                @endif
            </div>
            @if($prerequisiteLesson)
                <div class="mt-4">
                    <a href="{{ route('lessons.player', ['locale' => app()->getLocale(), 'lesson' => $prerequisiteLesson]) }}"
                       class="inline-flex items-center gap-2 rounded-full bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-amber-700">
                        Ir a la lección previa
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            @endif
        </div>
    </div>
@elseif($isVideo)
    <div class="space-y-6" data-player-shell
         data-provider="{{ $provider }}"
         data-lesson="{{ $lesson->id }}"
         data-resume="{{ $resumeSeconds }}"
         data-duration="{{ $durationSeconds ?? '' }}"
         data-strict="{{ $strictSeeking ? '1' : '0' }}"
         data-progress-url="{{ route('api.video.progress') }}">

        @if($practiceCta)
            <div class="rounded-3xl border border-indigo-200 bg-indigo-50/80 p-5 shadow-sm">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs uppercase font-semibold tracking-wide text-indigo-500">{{ __('Práctica en vivo vinculada') }}</p>
                        <p class="text-lg font-semibold text-slate-900">{{ $practiceCta['title'] }}</p>
                        <p class="text-sm text-slate-600">
                            {{ __('Inicio') }}: {{ optional($practiceCta['start_at'])->translatedFormat('d M H:i') ?? __('Próximamente') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-slate-800">{{ $practiceCta['available'] }} / {{ $practiceCta['capacity'] }}</p>
                        <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Cupos disponibles') }}</p>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-3">
                    @if($practiceCta['has_reservation'])
                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-4 py-2 text-xs font-semibold text-emerald-700">
                            ✅ {{ __('Reserva confirmada') }}
                        </span>
                    @elseif($practiceCta['available'] <= 0)
                        <span class="inline-flex items-center gap-2 rounded-full bg-rose-100 px-4 py-2 text-xs font-semibold text-rose-700">
                            ⚠️ {{ __('Cupos agotados') }}
                        </span>
                    @else
                        @if($practiceRoute)
                            <a href="{{ $practiceRoute }}"
                               class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow hover:bg-indigo-700">
                                {{ __('Reservar en Discord') }} ↗
                            </a>
                        @endif
                    @endif
                    @if($practiceRoute)
                        <a href="{{ $practiceRoute }}"
                           class="inline-flex items-center gap-2 rounded-full border border-indigo-200 px-4 py-2 text-xs font-semibold text-indigo-700 hover:border-indigo-300">
                            {{ __('Ver agenda completa') }}
                        </a>
                    @endif
                </div>
                @if($practiceCta['requires_package'])
                    <p class="mt-3 text-xs text-slate-600">
                        {{ __('Requiere pack activo') }}
                        @if($practiceCta['package_title'])
                            — {{ $practiceCta['package_title'] }}
                        @endif
                    </p>
                    @if(($practicePackCta['has_order'] ?? false) === false && $practicePackCta && $practiceRoute)
                        <a href="{{ $practiceRoute }}"
                           class="mt-2 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white px-4 py-2 text-xs font-semibold text-emerald-700 hover:border-emerald-300">
                            {{ __('Ver packs disponibles') }} ↗
                        </a>
                    @endif
                @endif
            </div>
        @elseif($practicePackCta)
            <div class="rounded-3xl border border-emerald-200 bg-emerald-50/70 p-5 shadow-sm">
                <div class="flex flex-col gap-2">
                    <p class="text-xs uppercase font-semibold tracking-wide text-emerald-600">{{ __('Prácticas recomendadas') }}</p>
                    <p class="text-lg font-semibold text-slate-900">{{ $practicePackCta['title'] }}</p>
                    <p class="text-sm text-slate-600">
                        {{ $practicePackCta['sessions'] }} {{ __('sesiones') }} · {{ number_format($practicePackCta['price'], 2) }} {{ $practicePackCta['currency'] }}
                    </p>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-3">
                    @if($practiceRoute)
                        <a href="{{ $practiceRoute }}"
                           class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow hover:bg-emerald-700">
                            {{ $practicePackCta['has_order'] ? __('Gestionar mis sesiones') : __('Comprar pack') }} ↗
                        </a>
                    @endif
                    <span class="text-xs text-emerald-700">{{ __('Ideal para practicar lo visto en esta lección.') }}</span>
                </div>
            </div>
        @endif

        <div class="aspect-video rounded-3xl overflow-hidden bg-black relative ring-1 ring-slate-900/10 shadow-xl shadow-black/30" data-player-video>
            @switch($provider)
                @case('vimeo')
                    @if($videoId)
                        <iframe
                            id="player-vimeo-{{ $lesson->id }}"
                            src="https://player.vimeo.com/video/{{ $videoId }}?app_id=122963"
                            frameborder="0"
                            allow="autoplay; fullscreen; picture-in-picture"
                            allowfullscreen
                            class="w-full h-full"></iframe>
                    @else
                        <div class="flex items-center justify-center w-full h-full text-white text-sm">Configura el ID de video de Vimeo.</div>
                    @endif
                    @break

                @case('cloudflare')
                    @if($videoId)
                        <stream id="player-cf-{{ $lesson->id }}" src="{{ $videoId }}" controls preload="metadata" class="w-full h-full"></stream>
                    @else
                        <div class="flex items-center justify-center w-full h-full text-white text-sm">Configura el token de Cloudflare Stream.</div>
                    @endif
                    @break

                @default
                    @if($videoId)
                        <iframe
                            id="player-youtube-{{ $lesson->id }}"
                            src="https://www.youtube-nocookie.com/embed/{{ $videoId }}?enablejsapi=1&origin={{ urlencode(env('YOUTUBE_ORIGIN', config('app.url'))) }}&rel=0"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                            referrerpolicy="strict-origin-when-cross-origin"
                            class="w-full h-full"></iframe>
                    @else
                        <div class="flex items-center justify-center w-full h-full text-white text-sm">Configura el ID del video de YouTube.</div>
                    @endif
            @endswitch
        </div>

        @if(!empty($heatmap))
            <div class="rounded-3xl border border-slate-100 bg-white/80 p-4 shadow-sm">
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <p class="font-semibold uppercase tracking-wide text-slate-400">{{ __('Mapa de abandono') }}</p>
                    <span>{{ count($heatmap) }} {{ __('segmentos') }}</span>
                </div>
                <div class="mt-3 flex h-16 items-end gap-0.5" role="presentation">
                    @foreach($heatmap as $segment)
                        <span class="flex-1 rounded-t-full bg-gradient-to-t from-slate-200 to-indigo-400"
                              style="height: {{ max(8, $segment['intensity'] * 100) }}%; opacity: {{ max(0.35, $segment['intensity']) }};"
                              title="{{ __('Bucket :bucket — :reach reproducciones', ['bucket' => $segment['bucket'], 'reach' => $segment['reach']]) }}">
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200 border border-slate-100/80 p-6 space-y-4" data-player-metrics>
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <h3 class="text-xl font-semibold">{{ $title }}</h3>
                        @if($badge)
                            <span class="inline-flex items-center rounded-full border border-blue-100 bg-blue-50 px-3 py-0.5 text-xs font-semibold text-blue-600">{{ $badge }}</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500">Proveedor: {{ ucfirst($provider) }}</p>
                </div>
                    <div class="flex flex-wrap items-center gap-4 text-xs text-gray-600">
                    <div class="w-full" aria-live="polite">
                        <span class="block text-[11px] uppercase font-semibold tracking-wide text-gray-400">{{ __('Avance') }}</span>
                        <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden motion-safe:transition-all motion-safe:duration-500">
                            <span class="block h-full rounded-full bg-gradient-to-r from-indigo-500 via-sky-500 to-emerald-500 transition-all duration-500"
                                  style="width: {{ $progressPercent }}%;"
                                  x-bind:style="`width: ${progressPercent}%`"
                                  x-bind:class="{ 'shadow-[0_0_12px_rgba(16,185,129,.45)]': celebrating }"></span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500"
                           x-text="`${progressPercent}% {{ __('completado') }}`">
                            {{ number_format($progressPercent, 1) }}% {{ __('completado') }}
                        </p>
                    </div>
                    <div>
                        <span class="block text-[11px] uppercase font-semibold tracking-wide text-gray-400">{{ __('Reanudar') }}</span>
                        <span class="text-sm text-gray-900"
                              x-text="resumeLabel">
                            {{ $resumeLabel }}
                        </span>
                    </div>
                    @if($durationSeconds)
                        <div>
                            <span class="block text-[11px] uppercase font-semibold tracking-wide text-gray-400">{{ __('Duración') }}</span>
                            <span class="text-sm text-gray-900">{{ gmdate('H:i:s', $durationSeconds) }}</span>
                        </div>
                        <div x-show="duration > 0">
                            <span class="block text-[11px] uppercase font-semibold tracking-wide text-gray-400">{{ __('Tiempo restante') }}</span>
                            <span class="text-sm text-gray-900"
                                  x-text="timeRemainingLabel">
                                --
                            </span>
                        </div>
                    @endif
                    @if($estimation)
                        <div>
                            <span class="block text-[11px] uppercase font-semibold tracking-wide text-gray-400">Estimado</span>
                            <span class="text-sm text-gray-900">{{ $estimation }}</span>
                        </div>
                    @endif
                    @if($provider !== 'youtube')
                        <button type="button"
                                wire:click="toggleStrict"
                                class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold transition {{ $strictSeeking ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 bg-slate-50 text-slate-600' }}">
                            <span class="text-base" aria-hidden="true">{{ $strictSeeking ? '🛡️' : '🎯' }}</span>
                            {{ $strictSeeking ? 'Modo estricto' : 'Modo libre' }}
                        </button>
                    @else
                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">
                            <span aria-hidden="true">⚙️</span> YouTube (best effort)
                        </span>
                    @endif
                </div>
            </div>

            @if($ctaLabel && $ctaUrl)
                <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50/60 px-4 py-3">
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-emerald-800">{{ $ctaLabel }}</p>
                        <p class="text-xs text-emerald-600/90">Enlace recomendado al finalizar esta lección.</p>
                    </div>
                    <a href="{{ $ctaUrl }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow hover:bg-emerald-700">
                        Abrir recurso
                        <span aria-hidden="true">↗</span>
                    </a>
                </div>
            @endif
        </div>
    </div>
@elseif($lesson->type === 'quiz')
    <livewire:lessons.quiz-runner :lesson="$lesson" />
@elseif($lesson->type === 'assignment')
    <livewire:lessons.assignment-panel :lesson="$lesson" />
@else
    <div class="space-y-6">
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200 border border-slate-100 p-6 space-y-4">
            <div class="flex items-center gap-3">
                <h3 class="text-lg font-semibold">{{ $title }}</h3>
                @if($badge)
                    <span class="inline-flex items-center rounded-full border border-blue-100 bg-blue-50 px-3 py-0.5 text-xs font-semibold text-blue-600">{{ $badge }}</span>
                @endif
            </div>
            <p class="text-sm text-gray-500 capitalize">Tipo de contenido: {{ $lesson->type }}</p>
            @if($lesson->type === 'text' && $bodyContent)
                <div class="prose prose-slate max-w-none">
                    {!! \Illuminate\Support\Str::markdown($bodyContent) !!}
                </div>
            @elseif($resourceUrl)
                <a href="{{ $resourceUrl }}" target="_blank" rel="noopener"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-full shadow-sm hover:bg-blue-700">
                    Abrir recurso externo
                </a>
            @else
                <p class="text-sm text-gray-500">Configura el recurso de esta lección desde el builder.</p>
            @endif
            @if($ctaLabel && $ctaUrl)
                <a href="{{ $ctaUrl }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700">
                    {{ $ctaLabel }} ↗
                </a>
            @endif
        </div>
    </div>
@endif
    </div>
</div>

@once
    @push('scripts')
        <script src="https://player.vimeo.com/api/player.js" defer></script>
        <script src="https://embed.videodelivery.net/embed/sdk.latest.js" defer></script>
        <script>
            document.addEventListener('alpine:init', () => {
                const normalizePercent = (value) => {
                    const num = Number(value ?? 0);
                    return Math.min(100, Math.max(0, Math.round(num * 10) / 10));
                };

                Alpine.data('playerInsights', (initial = {}) => ({
                    progressPercent: normalizePercent(initial.progress ?? 0),
                    duration: Number(initial.duration ?? 0),
                    current: Number(initial.resume ?? 0),
                    celebrating: false,
                    resumeLabel: '',
                    timeRemainingLabel: '',
                    init() {
                        this.updateLabels();

                        this.onProgress = (event) => {
                            const detail = event.detail || {};
                            if (typeof detail.duration === 'number' && detail.duration > 0) {
                                this.duration = detail.duration;
                            }
                            if (typeof detail.current === 'number') {
                                this.current = detail.current;
                            }
                            if (typeof detail.percent === 'number') {
                                this.progressPercent = normalizePercent(detail.percent);
                            }
                            this.updateLabels();
                        };

                        this.onCelebrate = () => {
                            this.celebrating = true;
                            setTimeout(() => {
                                this.celebrating = false;
                            }, 1200);
                        };

                        window.addEventListener('player:progress-tick', this.onProgress);
                        window.addEventListener('player:celebrate', this.onCelebrate);
                    },
                    updateLabels() {
                        this.resumeLabel = this.current <= 0
                            ? '{{ __('Inicio') }}'
                            : this.formatTime(this.current);

                        this.timeRemainingLabel = this.duration > 0
                            ? this.formatTime(Math.max(0, this.duration - this.current))
                            : '{{ __('--') }}';
                    },
                    formatTime(totalSeconds) {
                        const total = Math.max(0, Math.round(totalSeconds ?? 0));

                        if (total >= 3600) {
                            const hours = Math.floor(total / 3600);
                            const minutes = Math.floor((total % 3600) / 60);

                            return `${hours}h ${minutes}m`;
                        }

                        if (total >= 60) {
                            const minutes = Math.floor(total / 60);
                            const seconds = total % 60;

                            return `${minutes}m ${seconds}s`;
                        }

                        return `${total}s`;
                    },
                    destroy() {
                        window.removeEventListener('player:progress-tick', this.onProgress);
                        window.removeEventListener('player:celebrate', this.onCelebrate);
                    },
                }));
            });

            const dispatchInitialProgressEvent = () => {
                window.dispatchEvent(new CustomEvent('player:progress-tick', {
                    detail: {
                        percent: {{ number_format($progressPercent, 1, '.', '') }},
                        current: {{ $resumeSeconds }},
                        duration: {{ $durationSeconds ?? 0 }},
                    },
                }));
            };

            document.addEventListener('DOMContentLoaded', dispatchInitialProgressEvent);

            document.addEventListener('livewire:load', () => {
                const PlayerBridge = (() => {
                    const loadedScripts = new Set();

                    const ensureScript = (src) => {
                        if (loadedScripts.has(src)) {
                            return Promise.resolve();
                        }

                        return new Promise((resolve, reject) => {
                            const tag = document.createElement('script');
                            tag.src = src;
                            tag.async = true;
                            tag.onload = () => {
                                loadedScripts.add(src);
                                resolve();
                            };
                            tag.onerror = reject;
                            document.head.appendChild(tag);
                        });
                    };

                      const postProgress = (url, payload) => {
                        const token = document.querySelector('meta[name="csrf-token"]')?.content;
                        const body = new URLSearchParams(payload);
                        if (token) {
                            body.append('_token', token);
                        }

                        return fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                              body,
                          })
                              .then(async (response) => {
                                  if (! response.ok) {
                                      return null;
                                  }

                                  const data = await response.json();
                                  if (data?.celebration && data?.rewards) {
                                      window.dispatchEvent(new CustomEvent('gamification:celebrate', { detail: data.rewards }));
                                  }

                                  return data;
                              })
                              .catch(() => null);
                    };

                    const toSeconds = (value) => {
                        const number = Number(value);
                        return Number.isFinite(number) ? Math.max(0, Math.floor(number)) : 0;
                    };

                    const dispatchProgressEvent = (current, duration) => {
                        const percent = duration > 0
                            ? Math.min(100, Math.round((current / duration) * 1000) / 10)
                            : 0;

                        window.dispatchEvent(new CustomEvent('player:progress-tick', {
                            detail: { percent, current, duration },
                        }));

                        return percent;
                    };

                    const createProgressEmitter = (duration) => {
                        let celebrated = false;

                        return (current) => {
                            const percent = dispatchProgressEvent(current, duration);

                            if (! celebrated && percent >= 90) {
                                celebrated = true;
                                window.dispatchEvent(new CustomEvent('player:celebrate'));
                            }
                        };
                    };

                    const attachYouTube = (container) => {
                        const iframeId = container.querySelector('iframe')?.id;
                        if (! iframeId) {
                            return;
                        }

                        const progressUrl = container.dataset.progressUrl;
                        const lessonId = container.dataset.lesson;
                        const resumeAt = toSeconds(container.dataset.resume);
                        const duration = toSeconds(container.dataset.duration);
                        const isStrict = container.dataset.strict === '1';
                        const emitProgress = createProgressEmitter(duration);
                        emitProgress(resumeAt);

                        const initPlayer = () => {
                            const player = new YT.Player(iframeId, {
                                events: {
                                    onReady: () => {
                                        if (resumeAt > 0) {
                                            player.seekTo(resumeAt, true);
                                        }
                                    },
                                    onStateChange: () => handleTick(),
                                },
                            });

                            let intervalRef = null;
                            let lastValid = resumeAt;
                            let lastSent = resumeAt;

                            const handleTick = () => {
                                if (intervalRef) {
                                    clearInterval(intervalRef);
                                }

                                intervalRef = setInterval(() => {
                                    const currentTime = Math.floor(player.getCurrentTime());
                                    emitProgress(currentTime);
                                    if (currentTime > lastValid) {
                                        lastValid = currentTime;
                                    }

                                    if (currentTime - lastSent >= 5) {
                                        lastSent = currentTime;
                                        postProgress(progressUrl, {
                                            lesson_id: lessonId,
                                            source: 'youtube',
                                            last_second: currentTime,
                                            watched_seconds: lastValid,
                                        });
                                    }

                                    if (isStrict && currentTime > lastValid + 3) {
                                        player.seekTo(lastValid, true);
                                    }
                                }, 2000);
                            };
                        };

                        if (window.YT && window.YT.Player) {
                            initPlayer();
                        } else {
                            ensureScript('https://www.youtube.com/iframe_api').then(() => {
                                window.onYouTubeIframeAPIReady = () => initPlayer();
                            });
                        }
                    };

                    const attachVimeo = (container) => {
                        const iframe = container.querySelector('iframe');
                        if (! iframe) {
                            return;
                        }
                        const progressUrl = container.dataset.progressUrl;
                        const lessonId = container.dataset.lesson;
                        const resumeAt = toSeconds(container.dataset.resume);
                        const duration = toSeconds(container.dataset.duration);
                        const isStrict = container.dataset.strict === '1';
                        const emitProgress = createProgressEmitter(duration);
                        emitProgress(resumeAt);

                        const player = new Vimeo.Player(iframe);
                        let lastValid = resumeAt;
                        let lastSent = resumeAt;

                        player.ready().then(() => {
                            if (resumeAt > 0) {
                                player.setCurrentTime(resumeAt).catch(() => null);
                            }
                        });

                        player.on('timeupdate', (data) => {
                            const currentTime = Math.floor(data.seconds ?? 0);
                            emitProgress(currentTime);
                            if (currentTime > lastValid) {
                                lastValid = currentTime;
                            }

                            if (currentTime - lastSent >= 5) {
                                lastSent = currentTime;
                                postProgress(progressUrl, {
                                    lesson_id: lessonId,
                                    source: 'vimeo',
                                    last_second: currentTime,
                                    watched_seconds: lastValid,
                                });
                            }
                        });

                        player.on('seeked', (event) => {
                            if (! isStrict) {
                                return;
                            }

                            const seconds = Math.floor(event.seconds ?? 0);
                            if (seconds > lastValid + 3) {
                                player.setCurrentTime(lastValid).catch(() => null);
                            }
                        });
                    };

                    const attachCloudflare = (container) => {
                        const element = container.querySelector('stream');
                        if (! element) {
                            return;
                        }

                        const progressUrl = container.dataset.progressUrl;
                        const lessonId = container.dataset.lesson;
                        const resumeAt = toSeconds(container.dataset.resume);
                        const duration = toSeconds(container.dataset.duration);
                        const isStrict = container.dataset.strict === '1';
                        const emitProgress = createProgressEmitter(duration);
                        emitProgress(resumeAt);

                        let lastValid = resumeAt;
                        let lastSent = resumeAt;

                        element.addEventListener('loadedmetadata', () => {
                            if (resumeAt > 0) {
                                element.currentTime = resumeAt;
                            }
                        });

                        element.addEventListener('timeupdate', () => {
                            const current = Math.floor(element.currentTime || 0);
                            emitProgress(current);
                            if (current > lastValid) {
                                lastValid = current;
                            }

                            if (current - lastSent >= 5) {
                                lastSent = current;
                                postProgress(progressUrl, {
                                    lesson_id: lessonId,
                                    source: 'cloudflare',
                                    last_second: current,
                                    watched_seconds: lastValid,
                                });
                            }
                        });

                        element.addEventListener('seeking', () => {
                            if (! isStrict) {
                                return;
                            }

                            const current = Math.floor(element.currentTime || 0);
                            if (current > lastValid + 3) {
                                element.currentTime = lastValid;
                            }
                        });
                    };

                    const snapshotFromShell = () => {
                        const shell = document.querySelector('[data-player-shell]');
                        if (! shell) {
                            return;
                        }

                        const duration = toSeconds(shell.dataset.duration);
                        const resumeAt = toSeconds(shell.dataset.resume);
                        dispatchProgressEvent(resumeAt, duration);
                    };

                    return {
                        attachAll() {
                            document.querySelectorAll('[data-player-shell]').forEach((container) => {
                                const provider = container.dataset.provider;
                                if (provider === 'vimeo') {
                                    attachVimeo(container);
                                } else if (provider === 'cloudflare') {
                                    attachCloudflare(container);
                                } else if (provider === 'static') {
                                    // no-op
                                } else {
                                    attachYouTube(container);
                                }
                            });
                        },
                        snapshot: snapshotFromShell,
                    };
                })();

                const focusTimeline = () => {
                    const container = document.querySelector('[data-player-timeline]');
                    if (! container) {
                        return;
                    }

                    const current = container.querySelector('[aria-current="true"]');
                    if (! current) {
                        return;
                    }

                    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    current.scrollIntoView({
                        block: 'center',
                        inline: 'nearest',
                        behavior: prefersReduced ? 'auto' : 'smooth',
                    });
                };

                PlayerBridge.attachAll();
                focusTimeline();

                Livewire.hook('message.processed', (message, component) => {
                    setTimeout(() => PlayerBridge.attachAll(), 100);

                    if (component.fingerprint?.name === 'player') {
                        requestAnimationFrame(() => focusTimeline());
                        PlayerBridge.snapshot();
                    }
                });
            });
        </script>
    @endpush
@endonce


