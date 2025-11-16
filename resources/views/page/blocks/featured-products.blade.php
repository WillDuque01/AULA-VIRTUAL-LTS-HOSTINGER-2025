@php($title = $props['title'] ?? '')
@php($max = (int) ($props['max_items'] ?? 3))
@php($showBadges = $props['show_badges'] ?? true)
@php($products = \App\Models\Product::featured()->published()->limit($max)->get())

@if($products->isNotEmpty())
    <section class="bg-white py-16">
        <div class="mx-auto max-w-6xl space-y-6 px-6">
            <div class="flex items-center justify-between">
                <h2 class="text-3xl font-semibold text-slate-900">{{ $title }}</h2>
                <a href="{{ route('shop.catalog', ['locale' => app()->getLocale()]) }}"
                   class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                    {{ __('Ver catálogo completo') }} →
                </a>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                @foreach($products as $product)
                    <article class="rounded-3xl border border-slate-100 p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-500">{{ $product->category }}</p>
                            @if($showBadges && $product->is_featured)
                                <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-600">{{ __('Destacado') }}</span>
                            @endif
                        </div>
                        <h3 class="mt-2 text-xl font-semibold text-slate-900">{{ $product->title }}</h3>
                        <p class="text-sm text-slate-500">{{ $product->excerpt }}</p>
                        <p class="mt-4 text-2xl font-bold text-slate-900">
                            ${{ number_format($product->price_amount, 2) }} {{ $product->price_currency }}
                        </p>
                        <a href="{{ route('shop.cart', ['locale' => app()->getLocale()]) }}"
                           class="mt-4 inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                            {{ __('Agregar') }}
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif

