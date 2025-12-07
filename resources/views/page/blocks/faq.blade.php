@php
    $title = $props['title'] ?? '';
    $items = $props['items'] ?? [];
@endphp

@if(!empty($items))
    <section class="bg-slate-900 py-16 text-white">
        <div class="mx-auto max-w-4xl space-y-6 px-6">
            <h2 class="text-3xl font-semibold text-center">{{ $title }}</h2>
            <div class="space-y-3">
                @foreach($items as $item)
                    <article class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <p class="text-sm font-semibold">{{ $item['question'] ?? '' }}</p>
                        <p class="text-sm text-white/80">{{ $item['answer'] ?? '' }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif

