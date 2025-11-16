<div class="space-y-6">
    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Carrito') }}</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ __('Tus packs seleccionados') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Cada pack desbloquea sesiones privadas o grupales dentro del planner Discord.') }}</p>
            </div>
            <a href="{{ route('shop.packs', ['locale' => app()->getLocale()]) }}"
               class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:border-slate-400">
                ← {{ __('Volver al catálogo') }}
            </a>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
        @if($items->isEmpty())
            <p class="text-sm text-slate-500">{{ __('Tu carrito está vacío. Agrega packs desde el catálogo para continuar.') }}</p>
        @else
            <div class="space-y-3">
                @foreach($items as $item)
                    <article class="flex flex-col gap-3 rounded-2xl border border-slate-100 p-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $item->title }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $item->sessions_count }} {{ __('sesiones') }} · ${{ number_format($item->price_amount, 2) }} {{ $item->price_currency }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button"
                                    wire:click="remove({{ $item->id }})"
                                    class="rounded-full border border-rose-200 px-4 py-2 text-xs font-semibold text-rose-700 hover:border-rose-300">
                                {{ __('Eliminar') }}
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="flex flex-col gap-4 rounded-2xl border border-slate-100 bg-slate-50/70 p-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Resumen') }}</p>
                    <p class="text-lg font-semibold text-slate-900">{{ __('Subtotal') }}: ${{ number_format($subtotal, 2) }} USD</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button"
                            wire:click="clearCart"
                            class="rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-500 hover:border-slate-400">
                        {{ __('Vaciar carrito') }}
                    </button>
                    <button type="button"
                            wire:click="proceedToCheckout"
                            class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800">
                        {{ __('Ir al checkout') }}
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

