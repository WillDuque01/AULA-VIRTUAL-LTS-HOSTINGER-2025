@php
    $sections = __('docs.sections');
    $firstSection = array_key_first($sections);
@endphp

<x-app-layout>
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8" x-data="docsPage('{{ $firstSection }}')">
        <div class="flex items-center justify-between lg:hidden">
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('docs.title') }}</h1>
            <button type="button"
                    class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1 text-sm font-semibold text-slate-600"
                    @click="openMobile = !openMobile">
                <span x-text="openMobile ? '×' : 'Menu'"></span>
            </button>
        </div>
        <div class="lg:grid lg:grid-cols-4 lg:gap-8 mt-6">
            <aside class="lg:col-span-1">
                <nav class="sticky top-24 space-y-1 hidden lg:block">
                    @foreach($sections as $key => $section)
                        <a href="#{{ $key }}"
                           class="group flex items-center rounded-md px-3 py-2 text-sm font-medium transition-colors"
                           :class="active === '{{ $key }}' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                           @click.prevent="scrollTo('{{ $key }}')">
                            {{ $section['title'] }}
                        </a>
                    @endforeach
                </nav>
                <div class="mt-4 lg:hidden" x-show="openMobile" x-transition>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm space-y-2">
                        @foreach($sections as $key => $section)
                            <button type="button"
                                    class="w-full rounded-md px-3 py-2 text-left text-sm font-medium"
                                    :class="active === '{{ $key }}' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                                    @click="scrollTo('{{ $key }}')">
                                {{ $section['title'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </aside>
            <main class="lg:col-span-3 space-y-12">
                <div class="prose prose-indigo max-w-none">
                    <h1 class="hidden lg:block">{{ __('docs.title') }}</h1>
                </div>
                @foreach($sections as $key => $section)
                    <section id="{{ $key }}" class="scroll-mt-28" data-doc-section="{{ $key }}">
                        <div class="prose prose-indigo max-w-none bg-white/80 rounded-3xl border border-slate-100 p-6 shadow-sm">
                            <h2>{{ $section['title'] }}</h2>
                            <div class="text-slate-700 text-base leading-relaxed space-y-4">
                                {!! \Illuminate\Support\Str::markdown($section['content']) !!}
                            </div>
                        </div>
                    </section>
                @endforeach
            </main>
        </div>
    </div>
</x-app-layout>

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('docsPage', (initial) => ({
                active: initial,
                openMobile: false,
                observer: null,
                setActive(id) {
                    this.active = id;
                },
                scrollTo(id) {
                    this.setActive(id);
                    const el = document.getElementById(id);
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                    this.openMobile = false;
                },
                init() {
                    const sections = this.$root.querySelectorAll('[data-doc-section]');
                    this.observer = new IntersectionObserver((entries) => {
                        entries.forEach((entry) => {
                            if (entry.isIntersecting) {
                                this.setActive(entry.target.getAttribute('data-doc-section'));
                            }
                        });
                    }, { threshold: 0.2 });
                    sections.forEach((section) => this.observer.observe(section));
                }
            }));
        });
    </script>
@endpush

