<div class="space-y-8">
    <section class="rounded-3xl border border-slate-100 bg-white p-8 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Catálogo') }}</p>
                <h1 class="text-3xl font-semibold text-slate-900">{{ __('Explora nuestros productos y cohortes') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Agrega lo que necesites al carrito y finaliza cuando estés listo.') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <select wire:model.live="category"
                        class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:border-emerald-300 focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="all">{{ __('Todas las categorías') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>
                <label class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600">
                    <input type="checkbox" wire:model="onlyFeatured" class="rounded border-slate-300">
                    {{ __('Destacados') }}
                </label>
                <a href="{{ route('shop.cart', ['locale' => app()->getLocale()]) }}"
                   class="inline-flex items-center gap-2 rounded-full border border-slate-900/10 bg-slate-900/5 px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-900/10">
                    🛒 {{ __('Ver carrito') }} ({{ \App\Support\Practice\PracticeCart::count() }})
                </a>
            </div>
        </div>
        @if($flash)
            <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ $flash }}
            </div>
        @endif
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($products as $product)
            <article class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        @if($product->category)
                            <p class="text-[11px] font-semibold uppercase tracking-[0.35em] text-slate-400">{{ $product->category }}</p>
                        @endif
                        <h2 class="text-xl font-semibold text-slate-900">{{ $product->title }}</h2>
                        @if($product->excerpt)
                            <p class="text-sm text-slate-500">{{ $product->excerpt }}</p>
                        @endif
                    </div>
                    @if($product->is_featured)
                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-600">
                            {{ __('Destacado') }}
                        </span>
                    @endif
                </div>
                <div class="text-3xl font-bold text-slate-900">
                    ${{ number_format($product->price_amount, 2) }}
                    <span class="text-base font-semibold text-slate-500">{{ $product->price_currency }}</span>
                    @if($product->compare_at_amount)
                        <span class="ml-2 text-sm font-semibold text-rose-500 line-through">
                            ${{ number_format($product->compare_at_amount, 2) }}
                        </span>
                    @endif
                </div>
                @if($product->productable instanceof \App\Models\PracticePackage)
                    <p class="text-sm text-slate-500">
                        {{ trans_choice(':count sesión guiada|:count sesiones guiadas', $product->productable->sessions_count, ['count' => $product->productable->sessions_count]) }}
                        · {{ $product->productable->delivery_platform === 'discord' ? __('Discord') : ucfirst($product->productable->delivery_platform) }}
                    </p>
                @endif
                <button type="button"
                        wire:click="addToCart({{ $product->id }})"
                        class="w-full rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    {{ __('Añadir') }}
                </button>
            </article>
        @empty
            <p class="text-sm text-slate-500">{{ __('No hay productos para mostrar todavía.') }}</p>
        @endforelse
    </section>
</div>

