<div class="min-h-screen bg-slate-900 text-slate-100">
    <div class="mx-auto max-w-5xl px-6 py-16">
        <header class="mb-12 text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-blue-400">{{ __('First-time configuration') }}</p>
            <h1 class="mt-3 text-3xl font-semibold">{{ __('Bienvenido al asistente de puesta en marcha') }}</h1>
            <p class="mt-2 text-sm text-slate-400">{{ __('Configura tu cuenta administrativa, conecta tus integraciones críticas y deja lista la pasarela de pago preferida.') }}</p>
        </header>

        <div class="grid gap-8 md:grid-cols-[260px,1fr]">
            <aside class="space-y-2">
                @foreach($steps as $index => $label)
                    <div class="rounded-lg border {{ $step === $index ? 'border-blue-500 bg-blue-500/10' : 'border-slate-700' }} px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-slate-400">{{ __('Paso :number', ['number' => $index]) }}</p>
                        <p class="font-medium">{{ $label }}</p>
                    </div>
                @endforeach
            </aside>

            <section class="rounded-2xl border border-slate-800 bg-slate-950/60 p-6 shadow-2xl">
                @if($step === 1)
                    @include('setup.partials.step-admin')
                @elseif($step === 2)
                    @include('setup.partials.step-integrations')
                @else
                    @include('setup.partials.step-payments')
                @endif

                <div class="mt-8 flex items-center justify-between border-t border-slate-800 pt-6">
                    <div>
                        @if($step > 1)
                            <button type="button" wire:click="previous" class="inline-flex items-center gap-2 rounded-full border border-slate-700 px-4 py-2 text-sm font-medium text-slate-300 hover:border-slate-500">
                                <span>&larr;</span>{{ __('Atrás') }}
                            </button>
                        @endif
                    </div>

                    <div class="flex items-center gap-3">
                        @if($step < count($steps))
                            <button type="button" wire:click="next" class="inline-flex items-center gap-2 rounded-full bg-blue-500 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-blue-400">
                                {{ __('Guardar y continuar') }}<span>&rarr;</span>
                            </button>
                        @else
                            <button type="button" wire:click="finish" class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-400">
                                {{ __('Finalizar configuración') }}
                            </button>
                        @endif
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
