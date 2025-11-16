@php($title = $props['title'] ?? '')
@php($description = $props['description'] ?? '')
@php($fields = $props['fields'] ?? [])
@php($ctaLabel = $props['cta_label'] ?? __('Enviar'))

<section class="py-16 bg-white">
    <div class="mx-auto max-w-4xl rounded-3xl border border-slate-100 bg-slate-50/70 p-8 shadow-sm">
        <div class="mb-6 text-center space-y-2">
            <h2 class="text-2xl font-semibold text-slate-900">{{ $title }}</h2>
            <p class="text-sm text-slate-600">{{ $description }}</p>
        </div>
        <form class="grid gap-4 md:grid-cols-2">
            @foreach($fields as $field)
                <label class="text-sm font-semibold text-slate-700">
                    {{ $field['label'] ?? '' }}
                    <input type="{{ $field['type'] ?? 'text' }}"
                           placeholder="{{ $field['placeholder'] ?? '' }}"
                           class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-slate-900">
                </label>
            @endforeach
            <div class="md:col-span-2 flex justify-end">
                <button type="button"
                        class="inline-flex items-center rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800">
                    {{ $ctaLabel }}
                </button>
            </div>
        </form>
    </div>
</section>

