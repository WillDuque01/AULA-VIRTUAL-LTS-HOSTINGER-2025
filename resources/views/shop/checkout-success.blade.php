<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-2xl space-y-6 rounded-3xl border border-emerald-100 bg-white p-8 text-center shadow-sm">
            <div class="text-5xl">🎉</div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('¡Pago confirmado!') }}</h1>
            @php($summary = session('checkout_success'))
            <p class="text-sm text-slate-500">
                {{ __('Tus packs ya están activos. Revisa el Planner o tus reservas para agendar sesiones.') }}
            </p>
            @if($summary)
                <p class="text-sm text-slate-600">
                    {{ trans_choice(':count pack confirmado|:count packs confirmados', $summary['count'] ?? 1, ['count' => $summary['count'] ?? 1]) }} ·
                    ${{ number_format($summary['total'] ?? 0, 2) }} USD ·
                    {{ __('Método: :method', ['method' => $summary['method'] === 'transfer' ? __('Transferencia') : __('Tarjeta')]) }}
                </p>
            @endif
            <div class="flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('professor.discord-practices', ['locale' => app()->getLocale()]) }}"
                   class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:border-slate-400">
                    {{ __('Ver planner') }}
                </a>
                <a href="{{ route('shop.catalog', ['locale' => app()->getLocale()]) }}"
                   class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    {{ __('Volver al catálogo') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

