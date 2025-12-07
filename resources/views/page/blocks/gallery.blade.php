@php
    $title = $props['title'] ?? '';
    $items = $props['items'] ?? [];
@endphp

@if(!empty($items))
    <section class="bg-slate-50 py-16">
        <div class="mx-auto max-w-5xl space-y-6 px-6">
            <h2 class="text-3xl font-semibold text-slate-900 text-center">{{ $title }}</h2>
            <div class="columns-1 gap-4 sm:columns-2 lg:columns-3">
                @foreach($items as $item)
                    <figure class="mb-4 break-inside-avoid rounded-3xl border border-slate-100 bg-white p-3 shadow-sm">
                        @if(!empty($item['image']))
                            <img src="{{ $item['image'] }}" alt="{{ $item['caption'] ?? '' }}" class="w-full rounded-2xl object-cover">
                        @endif
                        @if(!empty($item['caption']))
                            <figcaption class="mt-2 text-sm text-slate-600">{{ $item['caption'] }}</figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        </div>
    </section>
@endif

