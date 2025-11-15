<div class="mx-auto max-w-5xl space-y-8 px-4 py-12">
    <div class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-blue-400">Teacher Admin</p>
        <h1 class="text-3xl font-semibold text-slate-100">{{ __('Simulador de Pagos') }}</h1>
        <p class="text-sm text-slate-400">{{ __('Ejecuta compras simuladas para pruebas o soporte y revisa los ultimos eventos registrados.') }}</p>
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

    <div class="grid gap-6 lg:grid-cols-[1.1fr,1fr]">
        <form wire:submit.prevent="simulate" class="space-y-4 rounded-2xl border border-slate-800 bg-slate-900/60 p-6 shadow-xl">
            <h2 class="text-lg font-semibold text-slate-100">{{ __('Nueva simulacion') }}</h2>

            <div class="space-y-1">
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="sim-email">{{ __('Correo del estudiante') }}</label>
                <input id="sim-email" type="email" wire:model.defer="form.email" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                @error('form.email')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-1">
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="sim-tier">{{ __('Tier de destino') }}</label>
                <select id="sim-tier" wire:model.defer="form.tier_id" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    <option value="">{{ __('Selecciona un tier') }}</option>
                    @foreach($tiers as $tier)
                        <option value="{{ $tier['id'] }}">{{ $tier['name'] }} ({{ $tier['access_type'] }})</option>
                    @endforeach
                </select>
                @error('form.tier_id')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-1">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="sim-provider">{{ __('Proveedor') }}</label>
                    <input id="sim-provider" type="text" wire:model.defer="form.provider" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    @error('form.provider')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="sim-status">{{ __('Estado') }}</label>
                    <input id="sim-status" type="text" wire:model.defer="form.status" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    @error('form.status')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-1">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="sim-amount">{{ __('Importe (opcional)') }}</label>
                    <input id="sim-amount" type="number" step="0.01" wire:model.defer="form.amount" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    @error('form.amount')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400" for="sim-currency">{{ __('Moneda') }}</label>
                    <input id="sim-currency" type="text" maxlength="3" wire:model.defer="form.currency" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm uppercase text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    @error('form.currency')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="inline-flex items-center justify-center rounded-full bg-blue-500 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-400">{{ __('Simular pago') }}</button>
            </div>
        </form>

        <section class="space-y-3 rounded-2xl border border-slate-800 bg-slate-900/60 p-6 shadow-xl">
            <h2 class="text-lg font-semibold text-slate-100">{{ __('Eventos recientes') }}</h2>
            <div class="max-h-[420px] space-y-3 overflow-y-auto pr-2">
                @forelse($events as $event)
                    <article class="rounded-xl border border-slate-800 bg-slate-900/80 p-4 text-sm text-slate-200">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold">{{ $event['user'] }}</span>
                            <span class="text-xs text-slate-500">{{ $event['created_at'] }}</span>
                        </div>
                        <p class="text-xs text-slate-400">{{ __('Tier') }}: <strong>{{ $event['tier'] ?? __('Desconocido') }}</strong></p>
                        <p class="text-xs text-slate-400">{{ __('Proveedor') }}: {{ $event['provider'] }} · {{ __('Estado') }}: {{ $event['status'] }}</p>
                        @if($event['amount'] !== null)
                            <p class="text-xs text-slate-400">{{ __('Importe') }}: {{ number_format($event['amount'], 2) }} {{ $event['currency'] }}</p>
                        @endif
                    </article>
                @empty
                    <p class="text-sm text-slate-400">{{ __('Aun no se registran pagos simulados.') }}</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
