@php($title = $props['title'] ?? '')
@php($items = $props['items'] ?? [])

<section class="bg-slate-50 py-16">
    <div class="mx-auto max-w-5xl space-y-8 px-6">
        <div class="text-center">
            <h2 class="text-3xl font-semibold text-slate-900">{{ $title }}</h2>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            @foreach($items as $item)
                <figure class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                    <blockquote class="text-lg text-slate-700">“{{ $item['quote'] ?? '' }}”</blockquote>
                    <figcaption class="mt-4 text-sm font-semibold text-slate-900">
                        {{ $item['author'] ?? '' }}
                        <span class="block text-xs font-normal text-slate-500">{{ $item['role'] ?? '' }}</span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>

