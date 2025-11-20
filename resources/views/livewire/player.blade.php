        @if($returnHint)
            <div x-data
                 x-init="window.playerSignals?.emitOnce('return_hint_view_{{ $lesson->id }}', 'banner_view', { metadata: { banner: 'return_hint' } })"
                 class="player-slide-up flex flex-wrap items-center gap-3 rounded-2xl border border-amber-100 bg-amber-50/70 px-4 py-3 text-sm text-amber-900">
                <div class="flex items-center gap-2">
                    <span class="text-base" aria-hidden="true">⏪</span>
                    <p class="font-semibold">{{ __('Retoma desde :time', ['time' => $returnHint['label']]) }}</p>
                </div>
                <button type="button"
                        class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-white px-3 py-1 text-xs font-semibold text-amber-700 hover:border-amber-300"
                        x-on:click.prevent="
                            window.dispatchEvent(new CustomEvent('player:seek-to', { detail: { time: {{ $returnHint['seconds'] ?? 0 }}, source: 'return_hint' } }));
                            window.playerSignals?.emit('banner_click', { metadata: { banner: 'return_hint' } });
                        ">
                    {{ __('Volver ahora') }}
                </button>
            </div>
        @endif

@php
    $title = data_get($lesson->config, 'title', $lesson->chapter?->title.' — Lección '.$lesson->position);
    $resumeSeconds = max(0, (int) $resumeAt);
    $durationSeconds = $duration ?? null;
    $resumeLabel = $resumeSeconds > 0 ? gmdate('H:i:s', $resumeSeconds) : 'Inicio';
    $bodyContent = data_get($lesson->config, 'body');
    $resourceUrl = $resourceUrl ?? data_get($lesson->config, 'resource_url');
    $estimation = $estimatedMinutes ? $estimatedMinutes.' min' : null;
    $remainingSeconds = $durationSeconds !== null ? max(0, $durationSeconds - $resumeSeconds) : null;
    $remainingLabel = $remainingSeconds !== null ? gmdate('H:i:s', $remainingSeconds) : null;

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

@once
    @push('styles')
        <style>
            @keyframes playerFadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }

            @keyframes playerSlideUp {
                from { opacity: 0; transform: translateY(16px); }
                to { opacity: 1; transform: translateY(0); }
            }

            @keyframes playerGlowPulse {
                0% { box-shadow: 0 0 0 rgba(16, 185, 129, 0.0); }
                50% { box-shadow: 0 0 15px rgba(16, 185, 129, 0.5); }
                100% { box-shadow: 0 0 0 rgba(16, 185, 129, 0.0); }
            }

            @media (prefers-reduced-motion: no-preference) {
                .player-fade-in { animation: playerFadeIn 0.5s cubic-bezier(0.2, 0, 0, 1) both; }
                .player-slide-up { animation: playerSlideUp 0.55s cubic-bezier(0.2, 0, 0, 1) both; }
                .player-glow-pulse { animation: playerGlowPulse 1.6s ease-in-out infinite; }
            }

            @media (prefers-reduced-motion: reduce) {
                .player-fade-in,
                .player-slide-up,
                .player-glow-pulse {
                    animation: none !important;
                }
            }
        </style>
    @endpush
@endonce

<div class="grid gap-6 lg:grid-cols-[320px,1fr]">
    <aside
        class="lg:sticky lg:top-28 space-y-4"
        x-data="playerInsights(
            {{ json_encode([
                'progress' => $progressPercent,
                'duration' => $durationSeconds ?? 0,
                'resume' => $resumeSeconds,
            ]) }},
            {{ json_encode([
                'milestones' => $progressMarkers,
            ]) }}
        )">
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
        @php
            $playerMode = 'default';

            if ($isLocked) {
                $playerMode = 'locked';
            } elseif ($isVideo) {
                $playerMode = 'video';
            } elseif ($lesson->type === 'quiz') {
                $playerMode = 'quiz';
            } elseif ($lesson->type === 'assignment') {
                $playerMode = 'assignment';
            }
        @endphp

        @include("livewire.player.modes.$playerMode")
    </div>
</div>

@once
    @push('scripts')
        <script src="https://player.vimeo.com/api/player.js" defer></script>
        <script src="https://embed.videodelivery.net/embed/sdk.latest.js" defer></script>
        <script>
            (function () {
                const signals = window.playerSignals || {};
                signals.queue = signals.queue || [];
                signals.onceKeys = signals.onceKeys || new Set();
                signals.emit = signals.emit || function (event, payload = {}) {
                    if (typeof signals.emitImpl === 'function') {
                        signals.emitImpl(event, payload);
                    } else {
                        signals.queue.push([event, payload]);
                    }
                };
                signals.emitOnce = signals.emitOnce || function (key, event, payload = {}) {
                    if (! key) {
                        return signals.emit(event, payload);
                    }

                    if (signals.onceKeys.has(key)) {
                        return;
                    }

                    signals.onceKeys.add(key);
                    signals.emit(event, payload);
                };
                signals.flushQueue = signals.flushQueue || function () {
                    if (typeof signals.emitImpl !== 'function') {
                        return;
                    }

                    while (signals.queue.length) {
                        const [event, payload] = signals.queue.shift();
                        signals.emitImpl(event, payload);
                    }
                };

                window.playerSignals = signals;
            })();

            document.addEventListener('alpine:init', () => {
                const normalizePercent = (value) => {
                    const num = Number(value ?? 0);
                    return Math.min(100, Math.max(0, Math.round(num * 10) / 10));
                };

                Alpine.data('playerInsights', (initial = {}, meta = {}) => ({
                    progressPercent: normalizePercent(initial.progress ?? 0),
                    duration: Number(initial.duration ?? 0),
                    current: Number(initial.resume ?? 0),
                    milestones: Array.isArray(meta.milestones) ? meta.milestones : [],
                    milestoneHits: {},
                    milestoneMessages: [],
                    celebrating: false,
                    resumeLabel: '',
                    timeRemainingLabel: '',
                    init() {
                        this.updateLabels();
                        this.checkMilestones(this.progressPercent);

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
                            this.checkMilestones(this.progressPercent);
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
                    checkMilestones(percent) {
                        this.milestones.forEach((milestone, index) => {
                            const threshold = Number(milestone.percent ?? 0);
                            if (percent >= threshold && ! this.milestoneHits[index]) {
                                this.milestoneHits[index] = true;
                                const label = milestone.label ?? '{{ __('Bloque completado') }}';
                                this.queueMilestone(`🎯 ${label}`);
                            }
                        });
                    },
                    queueMilestone(message) {
                        this.milestoneMessages.push(message);
                        setTimeout(() => {
                            this.milestoneMessages.shift();
                        }, 2600 + (this.milestoneMessages.length * 200));
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

                    const postPlayerEvent = (url, payload) => {
                        if (! url) {
                            return;
                        }

                        const token = document.querySelector('meta[name="csrf-token"]')?.content;
                        const enriched = Object.assign({}, payload);
                        if (token) {
                            enriched._token = token;
                        }

                        const body = JSON.stringify(enriched);

                        if (navigator.sendBeacon) {
                            const blob = new Blob([body], { type: 'application/json' });
                            navigator.sendBeacon(url, blob);

                            return Promise.resolve();
                        }

                        return fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                ...(token ? { 'X-CSRF-TOKEN': token } : {}),
                            },
                            body,
                        }).catch(() => null);
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

                    const createEventEmitter = (eventsUrl, lessonId, provider, duration) => {
                        if (! eventsUrl || ! lessonId) {
                            return () => {};
                        }

                        return (event, payload = {}) => {
                            if (! event) {
                                return;
                            }

                            const body = {
                                lesson_id: lessonId,
                                event,
                                provider,
                                video_duration: duration,
                            };

                            if (payload.playback_seconds !== undefined) {
                                body.playback_seconds = payload.playback_seconds;
                            }

                            if (payload.watched_seconds !== undefined) {
                                body.watched_seconds = payload.watched_seconds;
                            }

                            if (payload.metadata !== undefined) {
                                body.metadata = payload.metadata;
                            }

                            if (payload.context_tag !== undefined) {
                                body.context_tag = payload.context_tag;
                            }

                            postPlayerEvent(eventsUrl, body);
                        };
                    };

                    const registerGlobalEmitter = (emitter) => {
                        window.playerSignals = window.playerSignals || {};
                        window.playerSignals.emitImpl = emitter;
                        window.playerSignals.flushQueue?.();
                    };

                    const attachYouTube = (container) => {
                        const iframeId = container.querySelector('iframe')?.id;
                        if (! iframeId) {
                            return;
                        }

                        const progressUrl = container.dataset.progressUrl;
                        const lessonId = container.dataset.lesson;
                        const eventsUrl = container.dataset.eventsUrl;
                        const resumeAt = toSeconds(container.dataset.resume);
                        const duration = toSeconds(container.dataset.duration);
                        const isStrict = container.dataset.strict === '1';
                        const emitProgress = createProgressEmitter(duration);
                        const emitEvent = createEventEmitter(eventsUrl, lessonId, 'youtube', duration);
                        registerGlobalEmitter(emitEvent);
                        emitProgress(resumeAt);

                        const initPlayer = () => {
                            const player = new YT.Player(iframeId, {
                                events: {
                                    onReady: () => {
                                        if (resumeAt > 0) {
                                            player.seekTo(resumeAt, true);
                                        }
                                    },
                                    onStateChange: (state) => handleStateChange(state),
                                },
                            });

                            let intervalRef = null;
                            let lastValid = resumeAt;
                            let lastSent = resumeAt;
                            let lastReportedTime = resumeAt;
                            let playbackState = null;

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
                                            duration,
                                        });
                                    }

                                    if (isStrict && currentTime > lastValid + 3) {
                                        player.seekTo(lastValid, true);
                                    } else if (currentTime !== lastReportedTime) {
                                        dispatchProgressEvent(currentTime, duration);
                                        lastReportedTime = currentTime;
                                    }
                                }, 2000);
                            };

                            const handleStateChange = (stateEvent) => {
                                handleTick();

                                if (! stateEvent) {
                                    return;
                                }

                                if (stateEvent.data === YT.PlayerState.PLAYING && playbackState !== 'playing') {
                                    playbackState = 'playing';
                                    emitEvent('play', {
                                        playback_seconds: Math.floor(player.getCurrentTime()),
                                    });
                                } else if (stateEvent.data === YT.PlayerState.PAUSED && playbackState !== 'paused') {
                                    playbackState = 'paused';
                                    emitEvent('pause', {
                                        playback_seconds: Math.floor(player.getCurrentTime()),
                                    });
                                }
                            };

                            window.addEventListener('player:seek-to', (event) => {
                                const seekTime = toSeconds(event.detail?.time);
                                if (Number.isFinite(seekTime)) {
                                    player.seekTo(seekTime, true);
                                    lastValid = seekTime;
                                    lastSent = seekTime;
                                    dispatchProgressEvent(seekTime, duration);
                                    emitEvent('seek', {
                                        playback_seconds: seekTime,
                                        metadata: { source: event.detail?.source || 'ui' },
                                    });
                                }
                            });
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
                        const eventsUrl = container.dataset.eventsUrl;
                        const resumeAt = toSeconds(container.dataset.resume);
                        const duration = toSeconds(container.dataset.duration);
                        const isStrict = container.dataset.strict === '1';
                        const emitProgress = createProgressEmitter(duration);
                        const emitEvent = createEventEmitter(eventsUrl, lessonId, 'vimeo', duration);
                        registerGlobalEmitter(emitEvent);
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
                                    duration,
                                });
                            }
                        });

                        player.on('play', (data) => {
                            emitEvent('play', {
                                playback_seconds: Math.floor(data.seconds ?? 0),
                            });
                        });

                        player.on('pause', (data) => {
                            emitEvent('pause', {
                                playback_seconds: Math.floor(data.seconds ?? 0),
                            });
                        });

                        player.on('seeked', (event) => {
                            if (! isStrict) {
                                emitEvent('seek', {
                                    playback_seconds: Math.floor(event.seconds ?? 0),
                                    metadata: { source: 'scrub' },
                                });
                                return;
                            }

                            const seconds = Math.floor(event.seconds ?? 0);
                            if (seconds > lastValid + 3) {
                                player.setCurrentTime(lastValid).catch(() => null);
                                return;
                            }

                            emitEvent('seek', {
                                playback_seconds: seconds,
                                metadata: { source: 'scrub' },
                            });
                        });

                        window.addEventListener('player:seek-to', (event) => {
                            const seekTime = toSeconds(event.detail?.time);
                            if (Number.isFinite(seekTime)) {
                                player.setCurrentTime(seekTime).catch(() => null);
                                lastValid = seekTime;
                                lastSent = seekTime;
                                emitProgress(seekTime);
                                emitEvent('seek', {
                                    playback_seconds: seekTime,
                                    metadata: { source: event.detail?.source || 'ui' },
                                });
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
                        const eventsUrl = container.dataset.eventsUrl;
                        const resumeAt = toSeconds(container.dataset.resume);
                        const duration = toSeconds(container.dataset.duration);
                        const isStrict = container.dataset.strict === '1';
                        const emitProgress = createProgressEmitter(duration);
                        const emitEvent = createEventEmitter(eventsUrl, lessonId, 'cloudflare', duration);
                        registerGlobalEmitter(emitEvent);
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
                                    duration,
                                });
                            }
                        });

                        element.addEventListener('play', () => {
                            emitEvent('play', {
                                playback_seconds: Math.floor(element.currentTime || 0),
                            });
                        });

                        element.addEventListener('pause', () => {
                            emitEvent('pause', {
                                playback_seconds: Math.floor(element.currentTime || 0),
                            });
                        });

                        element.addEventListener('seeking', () => {
                            if (! isStrict) {
                                emitEvent('seek', {
                                    playback_seconds: Math.floor(element.currentTime || 0),
                                    metadata: { source: 'scrub' },
                                });
                                return;
                            }

                            const current = Math.floor(element.currentTime || 0);
                            if (current > lastValid + 3) {
                                element.currentTime = lastValid;
                                return;
                            }

                            emitEvent('seek', {
                                playback_seconds: current,
                                metadata: { source: 'scrub' },
                            });
                        });

                        window.addEventListener('player:seek-to', (event) => {
                            const seekTime = toSeconds(event.detail?.time);
                            if (Number.isFinite(seekTime)) {
                                element.currentTime = seekTime;
                                lastValid = seekTime;
                                lastSent = seekTime;
                                emitProgress(seekTime);
                                emitEvent('seek', {
                                    playback_seconds: seekTime,
                                    metadata: { source: event.detail?.source || 'ui' },
                                });
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


