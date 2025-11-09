@php
    $title = data_get($lesson->config, 'title', $lesson->chapter?->title.' — Lección '.$lesson->position);
    $resumeSeconds = max(0, (int) $resumeAt);
    $durationSeconds = $duration ?? null;
    $resumeLabel = $resumeSeconds > 0 ? gmdate('H:i:s', $resumeSeconds) : 'Inicio';
    $bodyContent = data_get($lesson->config, 'body');
    $resourceUrl = $resourceUrl ?? data_get($lesson->config, 'resource_url');
@endphp

@if($isVideo)
    <div class="space-y-6" data-player-shell
         data-provider="{{ $provider }}"
         data-lesson="{{ $lesson->id }}"
         data-resume="{{ $resumeSeconds }}"
         data-duration="{{ $durationSeconds ?? '' }}"
         data-progress-url="{{ route('api.video.progress') }}">

        <div class="aspect-video rounded-xl overflow-hidden bg-black relative">
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

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-3">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold">{{ $title }}</h3>
                    <p class="text-sm text-gray-500">Proveedor: {{ ucfirst($provider) }}</p>
                </div>
                <div class="flex items-center gap-6 text-xs text-gray-500">
                    <div>
                        <span class="block text-[11px] uppercase font-semibold tracking-wide text-gray-400">Reanudar</span>
                        <span class="text-sm text-gray-700">{{ $resumeLabel }}</span>
                    </div>
                    @if($durationSeconds)
                        <div>
                            <span class="block text-[11px] uppercase font-semibold tracking-wide text-gray-400">Duración</span>
                            <span class="text-sm text-gray-700">{{ gmdate('H:i:s', $durationSeconds) }}</span>
                        </div>
                    @endif
                    @if($strictMode)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold">Modo estricto</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-semibold">Modo best-effort</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
@else
    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <div>
                <h3 class="text-lg font-semibold">{{ $title }}</h3>
                <p class="text-sm text-gray-500 capitalize">Tipo de contenido: {{ $lesson->type }}</p>
            </div>
            @if($lesson->type === 'text' && $bodyContent)
                <div class="prose prose-slate max-w-none">
                    {!! \Illuminate\Support\Str::markdown($bodyContent) !!}
                </div>
            @elseif($resourceUrl)
                <a href="{{ $resourceUrl }}" target="_blank" rel="noopener" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md shadow-sm hover:bg-blue-700">
                    Abrir recurso externo
                </a>
            @else
                <p class="text-sm text-gray-500">Configura el recurso de esta lección desde el builder.</p>
            @endif
        </div>
    </div>
@endif

@once
    @push('scripts')
        <script src="https://player.vimeo.com/api/player.js" defer></script>
        <script src="https://embed.videodelivery.net/embed/sdk.latest.js" defer></script>
        <script>
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
                        }).catch(() => null);
                    };

                    const toSeconds = (value) => {
                        const number = Number(value);
                        return Number.isFinite(number) ? Math.max(0, Math.floor(number)) : 0;
                    };

                    const attachYouTube = (container) => {
                        const iframeId = container.querySelector('iframe')?.id;
                        if (! iframeId) {
                            return;
                        }

                        const progressUrl = container.dataset.progressUrl;
                        const lessonId = container.dataset.lesson;
                        const resumeAt = toSeconds(container.dataset.resume);

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

                                    if (currentTime > lastValid + 3) {
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

                        let lastValid = resumeAt;
                        let lastSent = resumeAt;

                        element.addEventListener('loadedmetadata', () => {
                            if (resumeAt > 0) {
                                element.currentTime = resumeAt;
                            }
                        });

                        element.addEventListener('timeupdate', () => {
                            const current = Math.floor(element.currentTime || 0);
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
                            const current = Math.floor(element.currentTime || 0);
                            if (current > lastValid + 3) {
                                element.currentTime = lastValid;
                            }
                        });
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
                    };
                })();

                PlayerBridge.attachAll();

                Livewire.hook('message.processed', () => {
                    setTimeout(() => PlayerBridge.attachAll(), 100);
                });
            });
        </script>
    @endpush
@endonce


