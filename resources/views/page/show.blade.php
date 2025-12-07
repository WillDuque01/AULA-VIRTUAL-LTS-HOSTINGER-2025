@php
    $layout = $blocks ?? [];
    $settings = $settings ?? [];
    $theme = $settings['theme'] ?? [];
@endphp
<x-app-layout>
    <div class="bg-slate-50" style="
        --page-primary: {{ $theme['primary'] ?? '#0f172a' }};
        --page-secondary: {{ $theme['secondary'] ?? '#14b8a6' }};
        --page-background: {{ $theme['background'] ?? '#f8fafc' }};
        --page-font: {{ $theme['font_family'] ?? 'Inter, sans-serif' }};
    ">
        <style>
            body {
                font-family: var(--page-font);
            }
            .page-primary {
                color: var(--page-primary);
            }
        </style>
        @foreach($layout as $block)
            @includeIf('page.blocks.'.$block['type'], ['props' => $block['props'] ?? []])
        @endforeach
    </div>
</x-app-layout>

