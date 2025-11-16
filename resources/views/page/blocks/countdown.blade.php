@php($title = $props['title'] ?? '')
@php($target = $props['target_date'] ?? now()->addDay()->toDateTimeString())
@php($ctaLabel = $props['cta_label'] ?? __('Apartar cupo'))
@php($ctaUrl = $props['cta_url'] ?? '#')

<section class="py-16 bg-slate-900 text-white">
    <div class="mx-auto max-w-4xl text-center space-y-6 px-6"
         x-data="{
            target: new Date('{{ $target }}').getTime(),
            remaining: { days: 0, hours: 0, minutes: 0, seconds: 0 },
            tick() {
                const diff = Math.max(0, this.target - new Date().getTime());
                this.remaining.days = Math.floor(diff / (1000 * 60 * 60 * 24));
                this.remaining.hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
                this.remaining.minutes = Math.floor((diff / (1000 * 60)) % 60);
                this.remaining.seconds = Math.floor((diff / 1000) % 60);
            }
        }"
         x-init="tick(); setInterval(() => tick(), 1000)">
        <p class="text-xs uppercase tracking-[0.35em] font-semibold text-emerald-300">{{ __('Cuenta regresiva') }}</p>
        <h2 class="text-3xl font-semibold">{{ $title }}</h2>
        <div class="grid grid-cols-4 gap-3">
            <div class="rounded-2xl border border-white/20 p-4">
                <p class="text-3xl font-bold" x-text="remaining.days">0</p>
                <p class="text-xs uppercase tracking-wide text-white/70">{{ __('Días') }}</p>
            </div>
            <div class="rounded-2xl border border-white/20 p-4">
                <p class="text-3xl font-bold" x-text="remaining.hours">0</p>
                <p class="text-xs uppercase tracking-wide text-white/70">{{ __('Horas') }}</p>
            </div>
            <div class="rounded-2xl border border-white/20 p-4">
                <p class="text-3xl font-bold" x-text="remaining.minutes">0</p>
                <p class="text-xs uppercase tracking-wide text-white/70">{{ __('Min') }}</p>
            </div>
            <div class="rounded-2xl border border-white/20 p-4">
                <p class="text-3xl font-bold" x-text="remaining.seconds">0</p>
                <p class="text-xs uppercase tracking-wide text-white/70">{{ __('Seg') }}</p>
            </div>
        </div>
        <a href="{{ $ctaUrl }}"
           class="inline-flex items-center justify-center rounded-full bg-white px-6 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-100">
            {{ $ctaLabel }}
        </a>
    </div>
</section>

