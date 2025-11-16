<div class="space-y-6">
    <header class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Catálogo') }}</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ __('Productos & Cohortes') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Gestiona todos los assets de pago y marca los destacados para la página pública.') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-sm">
                <label class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600">
                    <input type="checkbox" wire:model="onlyFeatured" class="rounded border-slate-300">
                    {{ __('Solo destacados') }}
                </label>
                <select wire:model.live="type" class="rounded-2xl border border-slate-200 px-3 py-1 text-sm text-slate-700">
                    <option value="">{{ __('Todos los tipos') }}</option>
                    @foreach($types as $entry)
                        <option value="{{ $entry }}">{{ \Illuminate\Support\Str::headline($entry) }}</option>
                    @endforeach
                </select>
                <select wire:model.live="status" class="rounded-2xl border border-slate-200 px-3 py-1 text-sm text-slate-700">
                    <option value="published">{{ __('Publicados') }}</option>
                    <option value="draft">{{ __('Borradores') }}</option>
                    <option value="archived">{{ __('Archivados') }}</option>
                    <option value="all">{{ __('Todos') }}</option>
                </select>
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-xs font-semibold text-slate-500">{{ __('Buscar') }}</label>
            <input type="search"
                   wire:model.live.debounce.400ms="search"
                   placeholder="{{ __('Título, categoría o descripción...') }}"
                   class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900" />
        </div>
    </header>

    <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
        @if($products->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No encontramos productos con los filtros actuales.') }}</p>
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($products as $product)
                    <article class="rounded-2xl border border-slate-100 p-4 shadow-sm space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ \Illuminate\Support\Str::headline($product->type) }}</p>
                                <h2 class="text-lg font-semibold text-slate-900">{{ $product->title }}</h2>
                                @if($product->excerpt)
                                    <p class="text-sm text-slate-500">{{ $product->excerpt }}</p>
                                @endif
                            </div>
                            <button type="button"
                                    wire:click="toggleFeatured({{ $product->id }})"
                                    class="rounded-full border px-3 py-1 text-[11px] font-semibold
                                        {{ $product->is_featured ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 text-slate-500' }}">
                                {{ $product->is_featured ? __('Destacado') : __('Destacar') }}
                            </button>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 text-sm font-semibold text-slate-700">
                            <span>${{ number_format($product->price_amount, 2) }} {{ $product->price_currency }}</span>
                            @if($product->compare_at_amount)
                                <span class="text-xs font-normal text-rose-500 line-through">${{ number_format($product->compare_at_amount, 2) }}</span>
                            @endif
                            <span class="rounded-full border border-slate-200 px-2 py-0.5 text-[11px] font-semibold text-slate-500">
                                {{ __('Estado: :status', ['status' => __($product->status)]) }}
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <button type="button"
                                    wire:click="edit({{ $product->id }})"
                                    class="rounded-full border border-slate-200 px-3 py-1 font-semibold text-slate-600 hover:border-slate-400">
                                {{ __('Editar') }}
                            </button>
                            @if($product->productable instanceof \App\Models\PracticePackage)
                                <a href="{{ route('professor.practice-packs', ['locale' => app()->getLocale()]) }}"
                                   class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-3 py-1 font-semibold text-slate-500 hover:border-slate-400"
                                   target="_blank" rel="noopener">
                                    {{ __('Abrir origen') }} ↗
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
            <div>
                {{ $products->links() }}
            </div>
        @endif
    </section>

    <x-modal name="product-editor" :show="$showEditor">
        <div class="space-y-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Editar producto') }}</p>
                <h3 class="text-lg font-semibold text-slate-900">{{ $form['title'] ?? __('Producto') }}</h3>
            </div>
            <div class="space-y-3">
                <label class="block text-sm font-semibold text-slate-700">
                    {{ __('Título') }}
                    <input type="text" wire:model.defer="form.title"
                           class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
                </label>
                <label class="block text-sm font-semibold text-slate-700">
                    {{ __('Resumen') }}
                    <textarea wire:model.defer="form.excerpt"
                              rows="2"
                              class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900"></textarea>
                </label>
                <div class="grid gap-3 md:grid-cols-3">
                    <label class="block text-sm font-semibold text-slate-700">
                        {{ __('Precio') }}
                        <input type="number" min="0" step="0.01" wire:model.defer="form.price_amount"
                               class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
                    </label>
                    <label class="block text-sm font-semibold text-slate-700">
                        {{ __('Moneda') }}
                        <input type="text" maxlength="3" wire:model.defer="form.price_currency"
                               class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 uppercase focus:border-slate-900 focus:ring-slate-900">
                    </label>
                    <label class="block text-sm font-semibold text-slate-700">
                        {{ __('Estado') }}
                        <select wire:model.defer="form.status"
                                class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
                            <option value="draft">{{ __('Borrador') }}</option>
                            <option value="published">{{ __('Publicado') }}</option>
                            <option value="archived">{{ __('Archivado') }}</option>
                        </select>
                    </label>
                </div>
                <label class="block text-sm font-semibold text-slate-700">
                    {{ __('Categoría') }}
                    <input type="text" wire:model.defer="form.category"
                           class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900"
                           placeholder="{{ __('Ej. Cohortes intensivas') }}">
                </label>
            </div>
            <div class="flex items-center justify-end gap-3">
                <button type="button"
                        wire:click="$set('showEditor', false)"
                        class="text-sm font-semibold text-slate-500 hover:text-slate-700">
                    {{ __('Cancelar') }}
                </button>
                <button type="button"
                        wire:click="save"
                        class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    {{ __('Guardar cambios') }}
                </button>
            </div>
        </div>
    </x-modal>
</div>

