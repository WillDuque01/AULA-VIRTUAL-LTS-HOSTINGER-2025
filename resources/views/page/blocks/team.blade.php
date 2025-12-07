@php
    $title = $props['title'] ?? '';
    $members = $props['members'] ?? [];
@endphp

@if(!empty($members))
    <section class="bg-white py-16">
        <div class="mx-auto max-w-5xl space-y-8 px-6">
            <h2 class="text-3xl font-semibold text-slate-900 text-center">{{ $title }}</h2>
            <div class="grid gap-4 md:grid-cols-2">
                @foreach($members as $member)
                    <article class="rounded-3xl border border-slate-100 p-5 shadow-sm flex items-center gap-4">
                        @if(!empty($member['avatar']))
                            <img src="{{ $member['avatar'] }}" alt="{{ $member['name'] ?? '' }}" class="h-16 w-16 rounded-full object-cover">
                        @else
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-xl font-semibold text-slate-500">
                                {{ \Illuminate\Support\Str::of($member['name'] ?? '??')->substr(0, 1) }}
                            </div>
                        @endif
                        <div>
                            <p class="text-base font-semibold text-slate-900">{{ $member['name'] ?? '' }}</p>
                            <p class="text-sm text-emerald-600">{{ $member['role'] ?? '' }}</p>
                            <p class="text-sm text-slate-600">{{ $member['bio'] ?? '' }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif

