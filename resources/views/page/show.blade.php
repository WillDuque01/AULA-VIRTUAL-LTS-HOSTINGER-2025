@php($layout = $blocks ?? [])
<x-app-layout>
    <div class="bg-slate-50">
        @foreach($layout as $block)
            @includeIf('page.blocks.'.$block['type'], ['props' => $block['props'] ?? []])
        @endforeach
    </div>
</x-app-layout>

