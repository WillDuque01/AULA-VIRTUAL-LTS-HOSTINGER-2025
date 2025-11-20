<div class="space-y-6" data-player-shell
     data-provider="{{ $provider }}"
     data-lesson="{{ $lesson->id }}"
     data-resume="{{ $resumeSeconds }}"
     data-duration="{{ $durationSeconds ?? '' }}"
     data-strict="{{ $strictSeeking ? '1' : '0' }}"
     data-progress-url="{{ route('api.video.progress') }}"
     data-events-url="{{ route('api.player.events') }}">

    @if($practiceCta)
        <div x-data
             x-init="window.playerSignals?.emitOnce('cta_practice_view_{{ $lesson->id }}_{{ $practiceCta['id'] }}', 'cta_view', { metadata: { type: 'practice', practice_id: {{ $practiceCta['id'] }}, requires_package: {{ $practiceCta['requires_package'] ? 'true' : 'false' }} } })"
             class="player-slide-up rounded-3xl border border-indigo-200 bg-indigo-50/80 p-5 shadow-sm">
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
                @else
                    @if($practiceCta['available'] <= 0)
                        <span class="inline-flex items-center gap-2 rounded-full bg-rose-100 px-4 py-2 text-xs font-semibold text-rose-700">
                            ⚠️ {{ __('Cupos agotados') }}
                        </span>
                    @else
                        @if($practiceRoute)
                            <a href="{{ $practiceRoute }}"
                               x-on:click="window.playerSignals?.emit('cta_click', { metadata: { type: 'practice', action: 'reserve', practice_id: {{ $practiceCta['id'] }} } })"
                               class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow hover:bg-indigo-700">
                                {{ __('Reservar en Discord') }} ↗
                            </a>
                        @endif
                    @endif
                @endif
                @if($practiceRoute)
                    <a href="{{ $practiceRoute }}"
                       x-on:click="window.playerSignals?.emit('cta_click', { metadata: { type: 'practice', action: 'agenda', practice_id: {{ $practiceCta['id'] }} } })"
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
                       x-on:click="window.playerSignals?.emit('cta_click', { metadata: { type: 'practice', action: 'view_packs', practice_id: {{ $practiceCta['id'] }} } })"
                       class="mt-2 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white px-4 py-2 text-xs font-semibold text-emerald-700 hover:border-emerald-300">
                        {{ __('Ver packs disponibles') }} ↗
                    </a>
                @endif
            @endif
        </div>
    @else
        @if($practicePackCta)
        <div x-data
             x-init="window.playerSignals?.emitOnce('cta_pack_view_{{ $lesson->id }}_{{ $practicePackCta['id'] }}', 'cta_view', { metadata: { type: 'pack', pack_id: {{ $practicePackCta['id'] }}, owned: {{ $practicePackCta['has_order'] ? 'true' : 'false' }} } })"
             class="player-slide-up rounded-3xl border border-emerald-200 bg-emerald-50/70 p-5 shadow-sm">
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
                       x-on:click="window.playerSignals?.emit('cta_click', { metadata: { type: 'pack', pack_id: {{ $practicePackCta['id'] }}, owned: {{ $practicePackCta['has_order'] ? 'true' : 'false' }} } })"
                       class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow hover:bg-emerald-700">
                        {{ $practicePackCta['has_order'] ? __('Gestionar mis sesiones') : __('Comprar pack') }} ↗
                    </a>
                @endif
                <span class="text-xs text-emerald-700">{{ __('Ideal para practicar lo visto en esta lección.') }}</span>
            </div>
        </div>
        @endif
    @endif

    @if($celebration)
        <div class="player-fade-in relative overflow-hidden rounded-3xl border border-emerald-100 bg-gradient-to-br from-emerald-50 via-white to-emerald-50 p-5 shadow-sm">
            <div class="player-confetti" aria-hidden="true"></div>
            <div class="space-y-1">
                <p class="text-xs uppercase font-semibold tracking-[0.35em] text-emerald-500">{{ __('Momentum') }}</p>
                <h3 class="text-2xl font-semibold text-slate-900">{{ $celebration['title'] }}</h3>
                <p class="text-sm text-slate-600">{{ $celebration['message'] }}</p>
            </div>
            <div class="mt-4 flex flex-wrap gap-3 text-xs text-slate-600">
                @if(!empty($celebration['streak']))
                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white/80 px-3 py-1 font-semibold text-emerald-700">
                        🔥 {{ __('Racha: :count días', ['count' => $celebration['streak']]) }}
                    </span>
                @endif
                @if(!empty($celebration['xp']))
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/80 px-3 py-1 font-semibold text-slate-700">
                        ✨ {{ __('XP total: :xp', ['xp' => number_format((int) $celebration['xp'])]) }}
                    </span>
                @endif
            </div>
        </div>
    @endif

    @if(!empty($progressMarkers))
        <div class="player-fade-in rounded-3xl border border-slate-100 bg-white/85 p-4 shadow-sm">
            <div class="flex items-center justify-between text-xs text-slate-500">
                <p class="font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Progreso del curso') }}</p>
                <span class="font-semibold text-slate-700">{{ number_format($progressPercent, 1) }}%</span>
            </div>
            <div class="relative mt-3">
                <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600"
                         style="width: {{ min(100, max(0, $progressPercent)) }}%;"></div>
                </div>
                @foreach($progressMarkers as $marker)
                    @php
                        $markerPercent = max(0, min(100, $marker['percent'] ?? 0));
                    @endphp
                    <button type="button"
                            class="player-marker absolute -top-1 h-5 w-5 rounded-full border-2 border-white bg-slate-300 shadow transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                            style="left: calc({{ $markerPercent }}% - 10px);"
                            x-on:click="window.dispatchEvent(new CustomEvent('player-scroll-timeline', { detail: { percent: {{ $markerPercent }} } }))"
                            title="{{ $marker['label'] ?? __('Capítulo') }}">
                        <span class="sr-only">{{ __('Ir a :chapter', ['chapter' => $marker['label'] ?? __('capítulo')]) }}</span>
                    </button>
                @endforeach
                <p class="mt-2 text-[11px] text-slate-500">{{ __('Haz clic en los hitos para centrar el timeline en ese capítulo.') }}</p>
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
        <div class="player-fade-in rounded-3xl border border-slate-100 bg-white/80 p-4 shadow-sm">
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
            @if(!empty($heatmapHighlights))
                <div class="mt-4 rounded-2xl border border-slate-100 bg-white/90 px-4 py-3">
                    <p class="text-[11px] uppercase font-semibold tracking-wide text-slate-400">{{ __('Momentos más vistos') }}</p>
                    <ul class="mt-2 space-y-1 text-sm text-slate-600">
                        @foreach($heatmapHighlights as $highlight)
                            <li class="flex items-center justify-between rounded-xl border border-slate-100 px-3 py-1.5">
                                <span>{{ $highlight['label'] }}</span>
                                <span class="text-[11px] font-semibold text-slate-500">{{ $highlight['percent'] }}%</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <div class="player-slide-up bg-white rounded-3xl shadow-xl shadow-slate-200 border border-slate-100/80 p-6 space-y-4" data-player-metrics>
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
                @php
                    $streak = (int) data_get($playerStats, 'streak', 0);
                    $xp = (int) data_get($playerStats, 'xp', 0);
                    $lastCompletionLabel = data_get($playerStats, 'last_completion_label');
                @endphp
                @if($streak || $xp || $lastCompletionLabel)
                    <div class="w-full">
                        <div class="grid gap-3 sm:grid-cols-3" aria-live="polite">
                            <div class="rounded-2xl border border-amber-100 bg-amber-50/60 px-3 py-2">
                                <p class="text-[11px] uppercase font-semibold tracking-wide text-amber-600">{{ __('Racha actual') }}</p>
                                <p class="mt-1 text-lg font-semibold text-amber-900 flex items-baseline gap-1">
                                    <span>{{ $streak }}</span>
                                    <span class="text-xs font-semibold text-amber-700">{{ __('días') }}</span>
                                </p>
                            </div>
                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 px-3 py-2">
                                <p class="text-[11px] uppercase font-semibold tracking-wide text-emerald-600">{{ __('XP acumulado') }}</p>
                                <p class="mt-1 text-lg font-semibold text-emerald-900">
                                    {{ number_format($xp, 0, ',', '.') }}
                                    <span class="text-xs font-semibold text-emerald-700">XP</span>
                                </p>
                            </div>
                            <div class="rounded-2xl border border-slate-100 bg-slate-50 px-3 py-2">
                                <p class="text-[11px] uppercase font-semibold tracking-wide text-slate-500">{{ __('Último logro') }}</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">
                                    {{ $lastCompletionLabel ?? __('Todavía no registrado') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="w-full" aria-live="polite">
                    <span class="block text-[11px] uppercase font-semibold tracking-wide text-gray-400">{{ __('Avance') }}</span>
                    <div class="mt-2 relative h-3 rounded-full bg-slate-100 overflow-hidden motion-safe:transition-all motion-safe:duration-500 motion-reduce:transition-none">
                        <span class="absolute inset-0 h-full rounded-full bg-gradient-to-r from-indigo-500 via-sky-500 to-emerald-500 transition-all duration-500 motion-reduce:transition-none"
                              style="width: {{ $progressPercent }}%;"
                              x-bind:style="`width: ${progressPercent}%`"
                              x-bind:class="{ 'shadow-[0_0_12px_rgba(16,185,129,.45)]': celebrating, 'player-glow-pulse': celebrating }"></span>
                        @foreach($progressMarkers as $marker)
                            <span class="absolute -top-4 flex flex-col items-center"
                                  style="left: {{ $marker['percent'] }}%;">
                                <span class="h-3 w-px bg-slate-300"></span>
                                <span class="mt-1 hidden whitespace-nowrap rounded-full border border-slate-200 bg-white px-2 py-0.5 text-[10px] font-semibold text-slate-500 md:inline-flex">
                                    {{ $marker['label'] }}
                                </span>
                            </span>
                        @endforeach
                    </div>
                    <p class="mt-1 text-xs text-slate-500"
                       x-text="`${progressPercent}% {{ __('completado') }}`">
                        {{ number_format($progressPercent, 1) }}% {{ __('completado') }}
                    </p>
                    <div class="mt-2 space-y-2" x-show="milestoneMessages.length" x-cloak>
                        <template x-for="(message, index) in milestoneMessages" :key="index">
                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/80 px-3 py-2 text-[11px] font-semibold text-emerald-800 shadow-sm"
                                 x-text="message"
                                 x-transition></div>
                        </template>
                    </div>
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
                    @if($remainingLabel)
                        <div>
                            <span class="block text-[11px] uppercase font-semibold tracking-wide text-gray-400">{{ __('Tiempo restante') }}</span>
                            <span class="text-sm text-gray-900">{{ $remainingLabel }}</span>
                        </div>
                    @endif
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

        @if($ctaHighlight)
            <div x-data
                 x-init="window.playerSignals?.emitOnce('cta_highlight_view_{{ $lesson->id }}_{{ $ctaHighlight['type'] }}', 'cta_view', { metadata: @js($ctaHighlight) })"
                 @class([
                        'player-fade-in flex flex-wrap items-center gap-3 rounded-2xl border px-4 py-3',
                    'border-indigo-100 bg-indigo-50/60' => $ctaHighlight['type'] === 'practice',
                    'border-emerald-100 bg-emerald-50/60' => $ctaHighlight['type'] === 'pack',
                    'border-amber-100 bg-amber-50/60' => $ctaHighlight['type'] === 'resource',
                ])>
                <div class="flex-1 space-y-0.5">
                    <p class="text-sm font-semibold text-slate-900">{{ $ctaHighlight['title'] }}</p>
                    <p class="text-xs text-slate-600">{{ $ctaHighlight['description'] }}</p>
                </div>
                @if($ctaHighlight['type'] === 'practice' && $practiceRoute)
                    <a href="{{ $practiceRoute }}"
                       x-on:click="window.playerSignals?.emit('cta_click', { metadata: Object.assign({ type: 'practice', origin: 'highlight' }, @js($ctaHighlight)) })"
                       class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow hover:bg-indigo-700">
                        {{ ($practiceCta['has_reservation'] ?? false) ? __('Ver detalles') : __('Reservar ahora') }} ↗
                    </a>
                @else
                    @if($ctaHighlight['type'] === 'pack' && $practiceRoute)
                        <a href="{{ $practiceRoute }}"
                           x-on:click="window.playerSignals?.emit('cta_click', { metadata: Object.assign({ type: 'pack', origin: 'highlight' }, @js($ctaHighlight)) })"
                           class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow hover:bg-emerald-700">
                            {{ ($practicePackCta['has_order'] ?? false) ? __('Gestionar') : __('Ver packs') }} ↗
                        </a>
                    @else
                        @if($ctaHighlight['type'] === 'resource' && $ctaUrl)
                            <a href="{{ $ctaUrl }}" target="_blank" rel="noopener"
                               x-on:click="window.playerSignals?.emit('cta_click', { metadata: Object.assign({ type: 'resource', origin: 'highlight' }, @js($ctaHighlight)) })"
                               class="inline-flex items-center gap-2 rounded-full bg-amber-500 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow hover:bg-amber-600">
                                {{ __('Abrir recurso') }} ↗
                            </a>
                        @endif
                    @endif
                @endif
            </div>
        @endif

        @if($ctaLabel && $ctaUrl && (! $ctaHighlight || $ctaHighlight['type'] !== 'resource'))
            <div x-data
                 x-init="window.playerSignals?.emitOnce('cta_resource_view_{{ $lesson->id }}', 'cta_view', { metadata: { type: 'resource', origin: 'secondary', label: @js($ctaLabel) } })"
                 class="player-fade-in flex flex-wrap items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50/60 px-4 py-3">
                <div class="flex-1">
                    <p class="text-sm font-semibold text-emerald-800">{{ $ctaLabel }}</p>
                    <p class="text-xs text-emerald-600/90">Enlace recomendado al finalizar esta lección.</p>
                </div>
                <a href="{{ $ctaUrl }}" target="_blank" rel="noopener"
                   x-on:click="window.playerSignals?.emit('cta_click', { metadata: { type: 'resource', origin: 'secondary', label: @js($ctaLabel) } })"
                   class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow hover:bg-emerald-700">
                    Abrir recurso
                    <span aria-hidden="true">↗</span>
                </a>
            </div>
        @endif
    </div>
</div>

