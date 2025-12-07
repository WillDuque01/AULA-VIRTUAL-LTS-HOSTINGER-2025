@php
    $palette = $emailPalette ?? ['accent' => '#14b8a6'];
@endphp
<a href="{{ $url }}"
   class="btn"
   target="_blank"
   rel="noopener"
   style="background-color: {{ $palette['accent'] }}; color: #ffffff; text-decoration: none;">
    {{ $slot }}
</a>
