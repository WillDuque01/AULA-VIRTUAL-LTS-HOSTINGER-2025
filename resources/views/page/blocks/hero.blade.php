@php
    $headline = $props['headline'] ?? '';
    $subheadline = $props['subheadline'] ?? '';
    $ctaLabel = $props['cta_label'] ?? null;
    $ctaUrl = $props['cta_url'] ?? '#';
    $image = $props['image'] ?? null;
@endphp

<section class="relative overflow-hidden bg-white py-16">
    <div class="mx-auto flex max-w-6xl flex-col gap-10 px-6 lg:flex-row lg:items-center">
        <div class="flex-1 space-y-4">
            <h1 class="text-4xl font-semibold text-slate-900">{{ $headline }}</h1>
            <p class="text-lg text-slate-600">{{ $subheadline }}</p>
            @if($ctaLabel)
                <a href="{{ $ctaUrl }}"
                   class="inline-flex items-center rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800">
                    {{ $ctaLabel }}
                </a>
            @endif
        </div>
        @if($image)
            <div class="flex-1">
                <img src="{{ $image }}" alt="{{ $headline }}" class="w-full rounded-3xl border border-slate-100 object-cover shadow-lg">
            </div>
        @endif
    </div>
</section>

