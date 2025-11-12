<div class="space-y-8">
    <div>
        <h2 class="text-xl font-semibold">{{ __('Pasarelas de pago') }}</h2>
        <p class="text-sm text-slate-400">{{ __('Configura PayPal como pasarela principal. Más adelante podrás activar Stripe u opciones personalizadas desde el panel de administración.') }}</p>
    </div>

    <section class="rounded-xl border border-slate-800 bg-slate-900/40 p-6">
        <h3 class="text-lg font-semibold text-slate-100">{{ __('PayPal') }}</h3>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <label class="text-xs uppercase text-slate-400">Client ID
                <input type="text" wire:model.defer="payments.paypal.client_id" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="{{ __('Obligatorio para cobros en vivo') }}">
            </label>
            <label class="text-xs uppercase text-slate-400">Client secret
                <input type="text" wire:model.defer="payments.paypal.client_secret" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
            </label>
        </div>
        <label class="mt-4 block text-xs uppercase text-slate-400">{{ __('Modo') }}
            <select wire:model.defer="payments.paypal.mode" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="sandbox">Sandbox</option>
                <option value="live">Live</option>
            </select>
        </label>
    </section>

    <section class="rounded-xl border border-slate-800 bg-slate-900/40 p-6">
        <h3 class="text-lg font-semibold text-slate-100">{{ __('Stripe (opcional)') }}</h3>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <label class="text-xs uppercase text-slate-400">Publishable key
                <input type="text" wire:model.defer="payments.stripe.publishable_key" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
            </label>
            <label class="text-xs uppercase text-slate-400">Secret key
                <input type="text" wire:model.defer="payments.stripe.secret_key" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
            </label>
            <label class="md:col-span-2 text-xs uppercase text-slate-400">Webhook secret
                <input type="text" wire:model.defer="payments.stripe.webhook_secret" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
            </label>
        </div>
    </section>

    <section class="rounded-xl border border-slate-800 bg-slate-900/40 p-6">
        <h3 class="text-lg font-semibold text-slate-100">{{ __('Otra pasarela (opcional)') }}</h3>
        <p class="text-xs text-slate-400">{{ __('Puedes registrar un enlace externo o instructivo de pago alterno (transferencia, criptomonedas, etc.).') }}</p>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <label class="text-xs uppercase text-slate-400">Etiqueta visible
                <input type="text" wire:model.defer="payments.custom.CUSTOM_PAYMENT_LABEL" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Cripto, transferencia...">
            </label>
            <label class="text-xs uppercase text-slate-400">URL / Instrucciones
                <input type="text" wire:model.defer="payments.custom.CUSTOM_PAYMENT_URL" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="https:// o instructivo">
            </label>
        </div>
    </section>
</div>
