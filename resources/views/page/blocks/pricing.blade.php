@php
    $title = $props['title'] ?? '';
    $items = $props['items'] ?? [];
@endphp

<section class="bg-white py-16">
    <div class="mx-auto max-w-6xl space-y-8 px-6">
        <div class="text-center">
            <h2 class="text-3xl font-semibold text-slate-900">{{ $title }}</h2>
        </div>
        <div class="grid gap-4 md:grid-cols-3">
            @foreach($items as $item)
                <div @class([
                    'rounded-3xl border p-6 shadow-sm',
                    'border-emerald-300 bg-emerald-50/50' => $item['highlight'] ?? false,
                    'border-slate-100 bg-white' => !($item['highlight'] ?? false),
                ])>
                    <p class="text-sm font-semibold text-slate-500">{{ $item['name'] ?? '' }}</p>
                    <p class="mt-2 text-4xl font-bold text-slate-900">
                        ${{ $item['price'] ?? '0' }}
                        <span class="text-base text-slate-500">{{ $item['currency'] ?? 'USD' }}</span>
                    </p>
                    <ul class="mt-4 space-y-2 text-sm text-slate-600">
                        @foreach($item['features'] ?? [] as $feature)
                            <li>• {{ $feature }}</li>
                        @endforeach
                    </ul>
                    @if(!empty($item['cta_label']))
                        <a href="{{ $item['cta_url'] ?? '#' }}"
                           class="mt-6 inline-flex w-full items-center justify-center rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                            {{ $item['cta_label'] }}
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>

