<div id="practice-packs" class="bg-white border border-slate-200 rounded-2xl shadow-sm font-[family:'Onest',var(--brand-body-font,'Inter'),sans-serif]">
    <div class="px-6 py-4 border-b border-slate-100 flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs uppercase font-semibold text-emerald-500 tracking-[0.2em]">Prácticas premium</p>
            <h4 class="text-2xl font-semibold text-slate-900 leading-tight">Haz que cada clase cuente</h4>
            <p class="text-sm text-slate-500">Sesiones cortas, enfocadas y con feedback accionable. Reserva en 30 segundos.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-600">
            <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-emerald-700">
                ✅ Cupos garantizados en Discord
            </span>
            <button type="button" wire:click="$refresh" class="rounded-full border border-slate-200 px-3 py-1 hover:border-blue-300 hover:text-blue-600">
                Actualizar lista
            </button>
            <a href="{{ route('shop.cart', ['locale' => app()->getLocale()]) }}"
               class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1 hover:border-emerald-300 hover:text-emerald-700">
                🛒 {{ __('Ver carrito') }}
                @if(\App\Support\Practice\PracticeCart::count())
                    <span class="rounded-full bg-emerald-600 px-2 py-0.5 text-white text-[10px]">
                        {{ \App\Support\Practice\PracticeCart::count() }}
                    </span>
                @endif
            </a>
        </div>
    </div>
    <div class="px-6 py-5">
        @if($flashMessage)
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ $flashMessage }}
            </div>
        @endif
        <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($packages as $package)
                @php
                    $pricePerSession = $package->sessions_count > 0 ? $package->price_amount / $package->sessions_count : $package->price_amount;
                    $badge = $package->is_global ? __('Teacher Admin') : __('Tu profesor');
                    $platformLabel = match ($package->delivery_platform) {
                        'zoom' => 'Zoom con cámara compartida',
                        'meet' => 'Google Meet con grabación',
                        default => 'Discord con pizarras en vivo',
                    };
                    $emotionalHook = [
                        __('Activa tu español en 48h'),
                        __('Feedback accionable y seguimiento'),
                        __('Recordatorios automáticos + bonus PDF'),
                    ];
                    $isHighlighted = $highlightPackageId === $package->id;
                @endphp
                <div @class([
                        'relative rounded-2xl border p-4 bg-gradient-to-b from-white to-slate-50 flex flex-col gap-4 shadow-sm transition',
                        'border-emerald-200 ring-2 ring-emerald-500 shadow-emerald-100' => $isHighlighted,
                        'border-slate-100 ring-1 ring-transparent hover:ring-emerald-200' => ! $isHighlighted,
                    ])>
                    @if($isHighlighted)
                        <span class="absolute -top-3 right-4 inline-flex items-center gap-1 rounded-full bg-emerald-600 px-3 py-0.5 text-[11px] font-semibold text-white shadow-lg">
                            ⭐ {{ __('Best Value') }}
                        </span>
                    @endif
                    <div class="space-y-1">
                        <p class="text-xs uppercase text-emerald-600 font-semibold tracking-wide">{{ $badge }}</p>
                        <h5 class="text-xl font-semibold text-slate-900 leading-snug">{{ $package->title }}</h5>
                        @if($package->subtitle)
                            <p class="text-sm text-slate-500">{{ $package->subtitle }}</p>
                        @endif
                        @if($isHighlighted)
                            <span class="mt-1 inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                ✨ {{ __('Recomendado') }}
                            </span>
                        @endif
                    </div>
                    <div class="flex items-baseline gap-2">
                        <p class="text-3xl font-bold text-slate-900">
                            ${{ number_format($package->price_amount, 0) }}
                            <span class="text-base font-semibold text-slate-500">{{ $package->price_currency }}</span>
                        </p>
                        <span class="text-xs text-slate-500">≈ ${{ number_format($pricePerSession, 1) }}/sesión</span>
                    </div>
                    <ul class="text-xs text-slate-500 space-y-1">
                        <li>• {{ $package->sessions_count }} sesiones guiadas</li>
                        <li>• {{ $platformLabel }}</li>
                        <li>• {{ __('Prioridad en agenda Discord + recordatorios') }}</li>
                    </ul>
                    <div class="space-y-1">
                        @foreach($emotionalHook as $line)
                            <div class="flex items-center gap-2 text-[11px] text-slate-500">
                                <span class="text-emerald-500">●</span> {{ $line }}
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-auto grid gap-2">
                        <button type="button"
                                wire:click="startCheckout({{ $package->id }})"
                                wire:loading.attr="disabled"
                                wire:target="startCheckout({{ $package->id }})"
                                class="inline-flex items-center justify-center rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:opacity-70">
                            {{ $package->is_global ? __('Comprar ahora') : __('Reservar con mi profe') }}
                        </button>
                        <button type="button"
                                wire:click="addToCart({{ $package->id }})"
                                wire:loading.attr="disabled"
                                wire:target="addToCart({{ $package->id }})"
                                class="inline-flex items-center justify-center rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-emerald-300 hover:text-emerald-700 disabled:opacity-70">
                            🛒 {{ __('Agregar al carrito') }}
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center text-sm text-slate-500">
                    No hay packs disponibles para tu cohorte todavía.
                </div>
            @endforelse
        </div>
    </div>

    @if($orders->isNotEmpty())
        <div class="border-t border-slate-100 px-6 py-4">
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide mb-2">Tus packs activos</p>
            <div class="space-y-2 text-sm text-slate-600">
                @foreach($orders as $order)
                    <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $order->package?->title }}</p>
                            <p class="text-xs text-slate-500">{{ __('Restan :count sesiones', ['count' => $order->sessions_remaining]) }}</p>
                        </div>
                        <span class="text-xs font-semibold text-emerald-600">✔ {{ ucfirst($order->status) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($showCheckout)
        @php
            $package = $packages->firstWhere('id', $checkoutPackageId);
        @endphp
        @if($package)
            <div class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/70 p-4">
                <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase text-slate-500">Confirmar compra</p>
                            <h4 class="text-lg font-semibold text-slate-900">{{ $package->title }}</h4>
                        </div>
                        <button type="button" wire:click="$set('showCheckout', false)" class="text-slate-400 hover:text-slate-600">✕</button>
                    </div>
                    <p class="text-sm text-slate-600">{{ $package->description }}</p>
                    <p class="text-3xl font-bold text-slate-900">${{ number_format($package->price_amount, 2) }} {{ $package->price_currency }}</p>
                    <button type="button"
                            wire:click="confirmCheckout"
                            class="w-full rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                        Pagar y empezar
                    </button>
                </div>
            </div>
        @endif
    @endif
</div>

