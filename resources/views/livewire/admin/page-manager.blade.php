<div class="space-y-6">
    <header class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Landing Builder') }}</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ __('Páginas y landings') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Crea la home o duplica una landing existente para editarla en el builder.') }}</p>
            </div>
            <form wire:submit.prevent="create" class="flex flex-wrap items-center gap-2">
                <input type="text" wire:model.defer="title" placeholder="{{ __('Título') }}"
                       class="rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-slate-900 focus:ring-slate-900">
                <select wire:model.defer="type" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                    <option value="landing">{{ __('Landing') }}</option>
                    <option value="home">{{ __('Home') }}</option>
                    <option value="custom">{{ __('Custom') }}</option>
                </select>
                <select wire:model.defer="locale" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                    <option value="es">ES</option>
                    <option value="en">EN</option>
                </select>
                <button type="submit"
                        class="inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    {{ __('Crear página') }}
                </button>
            </form>
        </div>
    </header>

    <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm space-y-4">
        @if($pages->isEmpty())
            <p class="text-sm text-slate-500">{{ __('Aún no hay páginas creadas.') }}</p>
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($pages as $page)
                    <article class="rounded-2xl border border-slate-100 p-4 shadow-sm space-y-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">{{ strtoupper($page->locale) }} · {{ $page->type }}</p>
                        <h2 class="text-lg font-semibold text-slate-900">{{ $page->title }}</h2>
                        <p class="text-xs text-slate-500">{{ $page->slug }}</p>
                        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                            <span class="rounded-full border border-slate-200 px-2 py-0.5 text-slate-500">
                                {{ __('Estado: :status', ['status' => __($page->status)]) }}
                            </span>
                            <span class="text-slate-400">{{ $page->updated_at->diffForHumans() }}</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 pt-2 text-xs font-semibold">
                            <a href="{{ route('admin.pages.builder', ['locale' => app()->getLocale(), 'page' => $page->id]) }}"
                               class="rounded-full border border-slate-200 px-3 py-1 text-slate-600 hover:border-slate-400">
                                {{ __('Editar') }}
                            </a>
                            <button type="button"
                                    wire:click="duplicate({{ $page->id }})"
                                    class="rounded-full border border-slate-200 px-3 py-1 text-slate-600 hover:border-slate-400">
                                {{ __('Duplicar') }}
                            </button>
                            @if($page->status === 'published')
                                <a href="{{ $page->type === 'home' ? route('welcome', ['locale' => $page->locale]) : route('landing.show', ['locale' => $page->locale, 'slug' => $page->slug]) }}"
                                   target="_blank"
                                   class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-emerald-700">
                                    {{ __('Ver publicada') }} ↗
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
            <div>{{ $pages->links() }}</div>
        @endif
    </section>
</div>

