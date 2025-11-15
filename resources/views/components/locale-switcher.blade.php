@php($route = request()->route())
@php($routeName = $route?->getName())
@php($params = $route?->parameters() ?? [])

@if($routeName && array_key_exists('locale', $params))
    <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
        @foreach(['es' => 'ES', 'en' => 'EN'] as $code => $label)
            @php($localeParams = $params)
            @php($localeParams['locale'] = $code)
            <a href="{{ route($routeName, $localeParams) }}"
               class="px-2 py-1 rounded-md border {{ $params['locale'] === $code ? 'border-blue-500 text-blue-600 bg-blue-50' : 'border-transparent hover:border-slate-300' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
@endif

