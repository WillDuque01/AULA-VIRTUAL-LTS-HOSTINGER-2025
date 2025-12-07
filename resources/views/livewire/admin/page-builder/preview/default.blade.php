@php
    $props = $block['props'] ?? [];
    $type = $block['type'] ?? 'unknown';
@endphp

<div class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 p-6 text-center">
    <div class="text-4xl mb-3">📦</div>
    <p class="text-sm font-semibold text-slate-700 capitalize">{{ str_replace('-', ' ', $type) }}</p>
    <p class="text-xs text-slate-500 mt-1">{{ __('Preview not available. Edit in sidebar.') }}</p>
    
    @if(!empty($props))
        <details class="mt-4 text-left text-xs">
            <summary class="cursor-pointer text-slate-500 hover:text-slate-700">{{ __('View props') }}</summary>
            <pre class="mt-2 p-3 rounded-xl bg-slate-100 text-slate-600 overflow-auto max-h-48">{{ json_encode($props, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </details>
    @endif
</div>

