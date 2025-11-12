<div class="mx-auto max-w-7xl space-y-10 px-4 py-12">
    <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-blue-400">Catalogo</p>
            <h1 class="text-3xl font-semibold text-slate-100">{{ __('Explora los cursos disponibles') }}</h1>
            <p class="text-sm text-slate-400">{{ __('Descubre contenido gratuito y premium. Compras simuladas actualizan tus tiers al instante.') }}</p>
        </div>
        @auth
            <div class="text-right text-xs text-slate-400">
                <p>{{ __('Tus tiers activos') }}:</p>
                <div class="flex flex-wrap gap-1 pt-1">
                    @forelse(auth()->user()->activeTiers()->select('name')->get() as $tier)
                        <span class="rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-300">{{ $tier->name }}</span>
                    @empty
                        <span class="rounded-full bg-slate-800/60 px-3 py-1 text-xs text-slate-300">{{ __('Ninguno') }}</span>
                    @endforelse
                </div>
            </div>
        @endauth
    </div>

    @if($flashStatus)
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ $flashStatus }}
        </div>
    @endif

    @if($flashError)
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
            {{ $flashError }}
        </div>
    @endif

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse($courses as $course)
            <article class="flex h-full flex-col rounded-2xl border border-slate-800 bg-slate-900/60 p-6 shadow-lg">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-100">{{ $course['title'] }}</h2>
                        @if($course['description'])
                            <p class="mt-2 text-sm text-slate-400">{{ Str::limit($course['description'], 140) }}</p>
                        @endif
                    </div>
                    <div class="text-right text-sm text-slate-400">
                        <span class="rounded-full bg-slate-800/70 px-3 py-1 text-xs uppercase tracking-wide">{{ $course['level'] ?? __('General') }}</span>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-2 text-xs text-slate-400">
                    @if(empty($course['tiers']))
                        <span class="rounded-full bg-emerald-500/15 px-3 py-1 text-emerald-300">{{ __('Acceso libre') }}</span>
                    @else
                        @foreach($course['tiers'] as $tier)
                            <span class="rounded-full border {{ $tier['available'] ? 'border-emerald-400 text-emerald-200' : 'border-slate-700 text-slate-300' }} px-3 py-1">
                                {{ $tier['name'] }}
                            </span>
                        @endforeach
                    @endif
                </div>

                <div class="mt-auto pt-6">
                    @if($course['is_accessible'])
                        <span class="rounded-full bg-emerald-500/15 px-4 py-2 text-sm font-semibold text-emerald-300">{{ __('Acceso habilitado') }}</span>
                    @else
                        @if($course['primary_tier'])
                            <div class="space-y-2 text-sm text-slate-300">
                                <p>{{ __('Requiere') }}: <strong>{{ $course['primary_tier']['name'] }}</strong></p>
                                <p class="text-xs text-slate-500">{{ __('Precio mensual') }}: {{ $course['primary_tier']['currency'] }} {{ number_format($course['primary_tier']['price_monthly'] ?? 0, 2) }}</p>
                            </div>
                            <button type="button" wire:click="purchaseTier({{ $course['primary_tier']['id'] }})" class="mt-3 inline-flex items-center justify-center rounded-full bg-blue-500 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-400">
                                {{ __('Comprar acceso simulado') }}
                            </button>
                        @else
                            <span class="text-xs text-slate-400">{{ __('Contacta soporte para habilitar este curso.') }}</span>
                        @endif
                    @endif
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-700 bg-slate-900/50 p-10 text-center text-slate-400">
                {{ __('No hay cursos configurados en el catalogo.') }}
            </div>
        @endforelse
    </div>
</div>

