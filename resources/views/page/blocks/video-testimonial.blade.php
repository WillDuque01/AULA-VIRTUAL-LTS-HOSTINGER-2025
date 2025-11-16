@php($title = $props['title'] ?? '')
@php($videoUrl = $props['video_url'] ?? '')
@php($quote = $props['quote'] ?? '')
@php($author = $props['author'] ?? '')
@php($role = $props['role'] ?? '')

<section class="py-16 bg-slate-50">
    <div class="mx-auto max-w-5xl grid gap-8 lg:grid-cols-2 items-center px-6">
        <div class="rounded-3xl overflow-hidden shadow-xl aspect-video bg-black">
            @if($videoUrl)
                <iframe src="{{ $videoUrl }}"
                        title="Video testimonial"
                        class="h-full w-full"
                        frameborder="0"
                        allowfullscreen></iframe>
            @else
                <div class="h-full w-full flex items-center justify-center text-white">Video</div>
            @endif
        </div>
        <div class="space-y-4">
            <p class="text-xs uppercase font-semibold tracking-[0.35em] text-slate-400">{{ __('Testimonio') }}</p>
            <h3 class="text-3xl font-semibold text-slate-900">{{ $title }}</h3>
            <p class="text-lg text-slate-600 italic">“{{ $quote }}”</p>
            <p class="text-sm font-semibold text-slate-900">{{ $author }}</p>
            <p class="text-xs text-slate-500">{{ $role }}</p>
        </div>
    </div>
</section>

