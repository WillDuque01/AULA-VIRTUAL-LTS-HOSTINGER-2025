<div>
@if($packages->isNotEmpty())
    <section class="mt-10 w-full max-w-4xl mx-auto">
        <div class="rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-900 to-purple-900 text-white p-8 shadow-2xl">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-white/60">Prácticas en vivo</p>
                    <h2 class="text-3xl font-semibold mt-2">Pack premium · Discord listo</h2>
                    <p class="text-sm text-white/80 mt-2">Sesiones con nuestros teachers senior. Cupos limitados, asegura tu plaza.</p>
                </div>
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-100">
                    Empezar ahora ↗
                </a>
            </div>
            <div class="mt-6 grid gap-4 lg:grid-cols-3">
                @foreach($packages as $package)
                    <div class="rounded-2xl bg-white/5 p-4 border border-white/10 backdrop-blur text-left">
                        <p class="text-xs uppercase tracking-wide text-emerald-300">Incluye Discord</p>
                        <h3 class="text-xl font-semibold">{{ $package->title }}</h3>
                        <p class="text-sm text-white/70">{{ $package->subtitle }}</p>
                        <p class="mt-4 text-3xl font-bold">${{ number_format($package->price_amount, 0) }}</p>
                        <p class="text-xs text-white/70"> {{ $package->sessions_count }} sesiones · {{ $package->price_currency }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
</div>

