<div class="space-y-6">
    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Checkout') }}</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ __('Confirma tu compra') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Verifica los packs y el método de pago antes de finalizar.') }}</p>
            </div>
            <a href="{{ route('shop.cart', ['locale' => app()->getLocale()]) }}"
               class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:border-slate-400">
                ← {{ __('Regresar al carrito') }}
            </a>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-6">
        <section class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Resumen de packs') }}</p>
            <div class="space-y-2">
                @foreach($items as $item)
                    <article class="rounded-2xl border border-slate-100 px-4 py-3">
                        <p class="text-sm font-semibold text-slate-900">{{ $item->title }}</p>
                        <p class="text-xs text-slate-500">
                            {{ $item->sessions_count }} {{ __('sesiones') }} · ${{ number_format($item->price_amount, 2) }} {{ $item->price_currency }}
                        </p>
                    </article>
                @endforeach
            </div>
            <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50/70 px-4 py-3">
                <p class="text-sm font-semibold text-slate-700">{{ __('Total') }}</p>
                <p class="text-lg font-semibold text-slate-900">${{ number_format($total, 2) }} USD</p>
            </div>
        </section>

        <section class="space-y-4">
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Pago') }}</p>
            <div class="grid gap-3 md:grid-cols-2">
                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700">
                    <input type="radio" wire:model="paymentMethod" value="card" class="text-slate-900 focus:ring-slate-900">
                    <div>
                        <p>{{ __('Tarjeta / Checkout instantáneo') }}</p>
                        <p class="text-xs text-slate-500">{{ __('Procesado automáticamente, activación inmediata.') }}</p>
                    </div>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700">
                    <input type="radio" wire:model="paymentMethod" value="transfer" class="text-slate-900 focus:ring-slate-900">
                    <div>
                        <p>{{ __('Transferencia / depósito') }}</p>
                        <p class="text-xs text-slate-500">{{ __('Validaremos el comprobante antes de activar los packs.') }}</p>
                    </div>
                </label>
            </div>
            <label class="block text-sm font-semibold text-slate-700">
                {{ __('Notas adicionales (opcional)') }}
                <textarea wire:model.defer="notes"
                          rows="3"
                          class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900"
                          placeholder="{{ __('Si necesitas factura o tienes una solicitud especial, escríbela aquí.') }}"></textarea>
            </label>
        </section>

        <div class="flex flex-col gap-4 rounded-2xl border border-slate-100 bg-slate-50/70 p-4 md:flex-row md:items-center md:justify-between">
            <p class="text-sm text-slate-600">{{ __('Al confirmar autorizas el cargo indicado y recibirás tus packs de inmediato.') }}</p>
            <button type="button"
                    wire:click="process"
                    class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-500">
                {{ __('Confirmar y pagar') }}
            </button>
        </div>
    </div>
</div>

