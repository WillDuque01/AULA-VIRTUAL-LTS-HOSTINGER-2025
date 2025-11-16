<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-2xl space-y-6 rounded-3xl border border-rose-100 bg-white p-8 text-center shadow-sm">
            <div class="text-5xl">⚠️</div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('El pago no se completó') }}</h1>
            <p class="text-sm text-slate-500">
                {{ session('checkout_error', __('Hubo un problema al procesar tu pago. Intenta nuevamente o contáctanos por soporte.')) }}
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('shop.checkout', ['locale' => app()->getLocale()]) }}"
                   class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    {{ __('Volver al checkout') }}
                </a>
                <a href="{{ route('shop.cart', ['locale' => app()->getLocale()]) }}"
                   class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:border-slate-400">
                    {{ __('Revisar carrito') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

