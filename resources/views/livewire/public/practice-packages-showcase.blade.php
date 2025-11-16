<div>
@if($packages->isNotEmpty())
    <section class="mt-10 w-full max-w-5xl mx-auto">
        <div class="rounded-[2.5rem] bg-gradient-to-br from-slate-900 via-indigo-950 to-blue-900 text-white p-10 shadow-2xl ring-1 ring-white/10">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-3">
                    <p class="text-xs uppercase tracking-[0.3em] text-white/60">Live practice experience</p>
                    <h2 class="text-4xl font-semibold leading-tight">Domina las conversaciones en 4 semanas</h2>
                    <p class="text-sm text-white/80">Nuestros teachers senior diseñaron estos packs para romper el bloqueo, ganar seguridad y mantener tu racha activa. Telegram + Discord + feedback personalizado.</p>
                    <div class="flex flex-wrap gap-2 text-[11px] font-semibold text-white/80">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1">⚡ 48h para tu primer slot</span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1">🎯 Metas semanales visibles</span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1">💬 Discord + materiales premium</span>
                    </div>
                </div>
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 text-base font-semibold text-slate-900 hover:bg-slate-100">
                    Reserva tu plaza ↗
                </a>
            </div>
            <div class="mt-8 grid gap-4 lg:grid-cols-3">
                @foreach($packages as $package)
                    @php($pricePerSession = $package->sessions_count > 0 ? $package->price_amount / $package->sessions_count : $package->price_amount)
                    <div class="rounded-3xl bg-white/5 p-5 border border-white/10 backdrop-blur text-left flex flex-col gap-3">
                        <p class="text-xs uppercase tracking-wide text-emerald-300">{{ $package->is_global ? 'Teacher Admin' : 'Coach asignado' }}</p>
                        <h3 class="text-xl font-semibold">{{ $package->title }}</h3>
                        <p class="text-sm text-white/70">{{ $package->subtitle ?? __('Entrenamiento intensivo de conversación.') }}</p>
                        <div>
                            <p class="mt-4 text-3xl font-bold">${{ number_format($package->price_amount, 0) }}</p>
                            <p class="text-xs text-white/70">{{ $package->sessions_count }} sesiones · {{ $package->price_currency }} · ≈ ${{ number_format($pricePerSession, 1) }}/sesión</p>
                        </div>
                        <ul class="text-xs text-white/70 space-y-1">
                            <li>• Plataforma: {{ ucfirst($package->delivery_platform ?? 'discord') }}</li>
                            <li>• Agenda privilegiada + recordatorios</li>
                            <li>• Retroalimentación escrita y mini retos</li>
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
</div>

