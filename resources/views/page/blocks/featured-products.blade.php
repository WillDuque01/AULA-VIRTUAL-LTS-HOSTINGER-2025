@php($title = $props['title'] ?? '')
@php($max = (int) ($props['max_items'] ?? 3))
@php($showBadges = $props['show_badges'] ?? true)
@php
    $query = \App\Models\Product::query()->published();
    if (!empty($props['product_ids'])) {
        $ids = $props['product_ids'];
        $products = $query->whereIn('id', $ids)->get()->sortBy(fn($p) => array_search($p->id, $ids))->values();
    } else {
        if (!empty($props['category'])) {
            $query->where('category', $props['category']);
        }
        $products = $query->featured()->limit($max)->get();
    }
@endphp

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
                    @php($isSoldOut = $product->isSoldOut())
                    <article class="rounded-3xl border border-slate-100 p-5 shadow-sm {{ $isSoldOut ? 'opacity-80' : '' }}">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-500">{{ $product->category }}</p>
                            @if($showBadges && $product->is_featured)
                                <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-600">{{ __('Destacado') }}</span>
                            @endif
                        </div>
                        <h3 class="mt-2 text-xl font-semibold text-slate-900">{{ $product->title }}</h3>
                        <p class="text-sm text-slate-500">{{ $product->excerpt }}</p>
                        @php($resource = $product->productable)
                        @if($resource instanceof \App\Models\CohortTemplate)
                            <div class="mt-2 space-y-1 text-xs">
                                <p class="text-slate-500">
                                    {{ __('Cohorte :label · :minutes min', [
                                        'label' => $resource->cohort_label ?? __('Sin etiqueta'),
                                        'minutes' => $resource->duration_minutes,
                                    ]) }}
                                </p>
                                <p class="{{ $isSoldOut ? 'text-rose-600 font-semibold' : 'text-emerald-600 font-semibold' }}">
                                    @if($isSoldOut)
                                        {{ __('Agotado') }}
                                    @else
                                        {{ __('Cupos disponibles: :count', ['count' => $product->inventory ?? $resource->remainingSlots()]) }}
                                    @endif
                                </p>
                            </div>
                        @elseif($resource instanceof \App\Models\PracticePackage)
                            <p class="mt-2 text-xs text-slate-500">
                                {{ trans_choice(':count sesión|:count sesiones', $resource->sessions_count, ['count' => $resource->sessions_count]) }}
                            </p>
                        @endif
                        <p class="mt-4 text-2xl font-bold text-slate-900">
                            ${{ number_format($product->price_amount, 2) }} {{ $product->price_currency }}
                        </p>
                        <a href="{{ route('shop.catalog', ['locale' => app()->getLocale()]) }}"
                           class="mt-4 inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold text-white {{ $isSoldOut ? 'cursor-not-allowed bg-slate-400' : 'bg-slate-900 hover:bg-slate-800' }}">
                            {{ $isSoldOut ? __('Agotado') : __('Agregar') }}
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif

