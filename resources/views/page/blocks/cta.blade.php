@php($title = $props['title'] ?? '')
@php($description = $props['description'] ?? '')
@php($primaryLabel = $props['primary_label'] ?? null)
@php($primaryUrl = $props['primary_url'] ?? '#')
@php($secondaryLabel = $props['secondary_label'] ?? null)
@php($secondaryUrl = $props['secondary_url'] ?? '#')

<section class="bg-slate-900 py-16 text-white">
    <div class="mx-auto flex max-w-4xl flex-col items-center gap-4 px-6 text-center">
        <h2 class="text-3xl font-semibold">{{ $title }}</h2>
        <p class="text-base text-slate-200">{{ $description }}</p>
        <div class="flex flex-wrap items-center justify-center gap-3">
            @if($primaryLabel)
                <a href="{{ $primaryUrl }}"
                   class="inline-flex items-center rounded-full bg-white px-5 py-2 text-sm font-semibold text-slate-900">
                    {{ $primaryLabel }}
                </a>
            @endif
            @if($secondaryLabel)
                <a href="{{ $secondaryUrl }}"
                   class="inline-flex items-center rounded-full border border-white/40 px-5 py-2 text-sm font-semibold text-white">
                    {{ $secondaryLabel }}
                </a>
            @endif
        </div>
    </div>
</section>

