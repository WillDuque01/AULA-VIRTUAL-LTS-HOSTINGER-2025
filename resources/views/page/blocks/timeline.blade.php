@php
    $title = $props['title'] ?? '';
    $steps = $props['steps'] ?? [];
@endphp

@if(!empty($steps))
    <section class="bg-slate-50 py-16">
        <div class="mx-auto max-w-4xl space-y-6 px-6">
            <h2 class="text-3xl font-semibold text-center text-slate-900">{{ $title }}</h2>
            <div class="relative border-l-2 border-slate-200 pl-6">
                @foreach($steps as $index => $step)
                    <div class="mb-8">
                        <div class="absolute -left-3 mt-1 h-6 w-6 rounded-full border-4 border-white bg-slate-900"></div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $step['badge'] ?? __('Paso :n', ['n' => $index + 1]) }}</p>
                        <h3 class="text-lg font-semibold text-slate-900">{{ $step['title'] ?? '' }}</h3>
                        <p class="text-sm text-slate-600">{{ $step['description'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

