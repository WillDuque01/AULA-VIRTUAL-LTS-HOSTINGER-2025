<div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <div>
            <p class="text-xs uppercase font-semibold text-slate-500 tracking-wide">Prácticas premium</p>
            <h4 class="text-lg font-semibold text-slate-900">Refuerza con sesiones 1:1</h4>
        </div>
        <button type="button" wire:click="$refresh" class="text-xs font-semibold text-slate-600 hover:text-blue-600">
            Actualizar
        </button>
    </div>
    <div class="px-6 py-5">
        <div class="grid gap-4 lg:grid-cols-3 sm:grid-cols-2">
            @forelse($packages as $package)
                <div class="rounded-2xl border border-slate-100 p-4 bg-gradient-to-b from-white to-slate-50 flex flex-col gap-3 shadow-sm">
                    <div>
                        <p class="text-xs uppercase text-emerald-600 font-semibold">{{ $package->is_global ? 'Global' : 'Tu profesor' }}</p>
                        <h5 class="text-lg font-semibold text-slate-900">{{ $package->title }}</h5>
                        @if($package->subtitle)
                            <p class="text-sm text-slate-500">{{ $package->subtitle }}</p>
                        @endif
                    </div>
                    <p class="text-3xl font-bold text-slate-900">
                        ${{ number_format($package->price_amount, 0) }}
                        <span class="text-base font-semibold text-slate-500">{{ $package->price_currency }}</span>
                    </p>
                    <ul class="text-xs text-slate-500 space-y-1">
                        <li>• {{ $package->sessions_count }} sesiones privadas</li>
                        <li>• Plataforma: {{ ucfirst($package->delivery_platform) }}</li>
                        <li>• Acceso a material de apoyo</li>
                    </ul>
                    <button type="button"
                            wire:click="startCheckout({{ $package->id }})"
                            class="mt-auto inline-flex items-center justify-center rounded-full bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                        Reservar pack
                    </button>
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
        @php($package = $packages->firstWhere('id', $checkoutPackageId))
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

