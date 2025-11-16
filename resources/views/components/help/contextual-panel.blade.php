@props([
    'guides' => [],
    'title' => __('Guía contextual'),
    'subtitle' => null,
])

@if(!empty($guides))
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs uppercase font-semibold tracking-[0.35em] text-slate-400">{{ __('Guía rápida') }}</p>
                <h3 class="text-lg font-semibold text-slate-900">{{ $title }}</h3>
                @if($subtitle)
                    <p class="text-sm text-slate-500">{{ $subtitle }}</p>
                @endif
            </div>
            <span class="text-xs font-semibold text-slate-500">{{ count($guides) }} {{ __('fichas') }}</span>
        </div>
        <div class="mt-4 space-y-3" x-data="{ openIndex: 0 }">
            @foreach($guides as $index => $guide)
                <article class="rounded-2xl border border-slate-200">
                    <button type="button"
                            class="flex w-full items-center justify-between px-4 py-3 text-left"
                            @click="openIndex === {{ $index }} ? openIndex = null : openIndex = {{ $index }}">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $guide['title'] ?? __('Guía') }}</p>
                            <p class="text-xs text-slate-500">{{ $guide['summary'] ?? '' }}</p>
                        </div>
                        <span class="text-xl text-slate-400" x-text="openIndex === {{ $index }} ? '−' : '+'"></span>
                    </button>
                    <div class="px-4 pb-4" x-show="openIndex === {{ $index }}" x-transition x-cloak>
                        @if(!empty($guide['description']))
                            <p class="text-sm text-slate-600">{{ $guide['description'] }}</p>
                        @endif
                        @if(!empty($guide['tokens']))
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach($guide['tokens'] as $token)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold text-slate-600">
                                        {{ $token['label'] ?? '' }}
                                        @if(!empty($token['hint']))
                                            <span class="text-slate-400 font-normal">{{ $token['hint'] }}</span>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        @endif
                        @if(!empty($guide['steps']))
                            <ol class="mt-4 list-decimal space-y-1 pl-5 text-sm text-slate-600">
                                @foreach($guide['steps'] as $step)
                                    <li>{{ $step }}</li>
                                @endforeach
                            </ol>
                        @endif
                        @if(!empty($guide['docs']))
                            <div class="mt-4">
                                <a href="{{ $guide['docs'] }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 hover:border-blue-300">
                                    {{ __('Ver documentación') }} ↗
                                </a>
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif

