@props(['cards' => []])

@if(!empty($cards))
    <div x-data="{ open: false }" class="fixed bottom-6 right-6 z-50 text-slate-900">
        <button type="button"
                class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-900 text-white shadow-lg shadow-slate-900/40 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500"
                aria-label="{{ __('Guía contextual') }}"
                @click="open = !open">
            ?
        </button>
        <div x-show="open" x-transition x-cloak
             class="mt-3 w-80 rounded-3xl border border-slate-200 bg-white p-4 shadow-2xl shadow-slate-900/30">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase font-semibold tracking-[0.3em] text-slate-400">{{ __('Guía contextual') }}</p>
                    <p class="text-sm font-semibold text-slate-900">{{ __('¿Cómo funciona esta pantalla?') }}</p>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-600" @click="open = false">×</button>
            </div>
            <div class="mt-3 max-h-[60vh] space-y-3 overflow-y-auto pr-1 text-sm text-slate-600">
                @foreach($cards as $card)
                    <article class="rounded-2xl border border-slate-100 bg-slate-50/70 p-3">
                        <p class="text-sm font-semibold text-slate-900">{{ $card['title'] ?? __('Guía') }}</p>
                        <p class="text-xs text-slate-500">{{ $card['summary'] ?? '' }}</p>
                        @if(!empty($card['steps']))
                            <ul class="mt-2 list-disc space-y-1 pl-4 text-xs text-slate-600">
                                @foreach($card['steps'] as $step)
                                    <li>{{ $step }}</li>
                                @endforeach
                            </ul>
                        @endif
                        @if(!empty($card['docs']))
                            <a href="{{ $card['docs'] }}" target="_blank" rel="noopener"
                               class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:underline">
                                {{ __('Ver documentación') }} ↗
                            </a>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </div>
@endif

