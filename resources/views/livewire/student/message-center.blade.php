{{-- [AGENTE: OPUS 4.5] - Márgenes corregidos --}}
<section class="space-y-6 px-4 sm:px-6 lg:px-8 py-6">
    <header class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.35em] text-slate-400">{{ __('Mensajes con el equipo docente') }}</p>
            <h2 class="text-2xl font-semibold text-slate-900">{{ __('Consulta novedades o resuelve dudas con tus profesores.') }}</h2>
        </div>
        <div class="flex gap-2">
            <button wire:click="inbox" @class([
                'px-3 py-2 rounded-xl text-sm font-semibold transition border',
                'bg-slate-900 text-white border-slate-900 shadow-sm' => $tab === 'inbox',
                'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' => $tab !== 'inbox',
            ])>{{ __('Bandeja') }}</button>
            <button wire:click="compose" @class([
                'px-3 py-2 rounded-xl text-sm font-semibold transition border',
                'bg-slate-900 text-white border-slate-900 shadow-sm' => $tab === 'compose',
                'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' => $tab !== 'compose',
            ])>{{ __('Redactar') }}</button>
        </div>
    </header>

    <div class="grid gap-6 lg:grid-cols-[320px_1fr]">
        <aside class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">{{ __('Mensajes') }}</h3>
                <span class="text-xs text-slate-500">{{ $messages->total() }}</span>
            </div>
            <ul class="divide-y divide-slate-100 max-h-[520px] overflow-y-auto">
                @forelse($messages as $message)
                    <li>
                        <button wire:click="openMessage({{ $message->id }})" class="w-full text-left px-4 py-3 transition hover:bg-slate-50 {{ optional($message->recipients->firstWhere('user_id', auth()->id()))?->status === 'unread' ? 'bg-slate-50' : '' }}">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-slate-900 truncate">{{ $message->sender->name }}</span>
                                <span class="text-xs text-slate-500">{{ optional($message->sent_at)->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-slate-500 truncate mt-1">{{ $message->subject ?? __('(Sin asunto)') }}</p>
                        </button>
                    </li>
                @empty
                    <li class="px-4 py-6 text-sm text-slate-500 text-center">{{ __('Aún no hay mensajes.') }}</li>
                @endforelse
            </ul>
            <div class="px-4 py-3 border-t border-slate-100 bg-slate-50">
                {{ $messages->links() }}
            </div>
        </aside>

        <main class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            @if(session('status'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            {{-- [AGENTE: OPUS 4.5] - Migración completa a tema claro UIX 2030 --}}
            @if($tab === 'compose')
                <form wire:submit.prevent="send" class="space-y-5">
                    <div class="grid gap-3 md:grid-cols-2">
                        <label class="flex items-center gap-2 p-3 rounded-xl border cursor-pointer transition {{ $target === 'teacher_team' ? 'border-sky-200 bg-sky-50' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                            <input type="radio" class="text-sky-500 focus:ring-sky-500" value="teacher_team" wire:model="target">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ __('Enviar al equipo docente') }}</p>
                                <p class="text-xs text-slate-500">{{ __('Llega a los profesores responsables del programa.') }}</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-2 p-3 rounded-xl border cursor-pointer transition {{ $target === 'custom' ? 'border-sky-200 bg-sky-50' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                            <input type="radio" class="text-sky-500 focus:ring-sky-500" value="custom" wire:model="target">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ __('Elegir profesores específicos') }}</p>
                                <p class="text-xs text-slate-500">{{ __('Busca y selecciona con quién quieres conversar.') }}</p>
                            </div>
                        </label>
                    </div>

                    @if($target === 'custom')
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Buscar profesores') }}</label>
                                <input type="text" wire:model.live="searchTerm" class="w-full rounded-xl border border-slate-200 bg-white text-slate-900 px-4 py-2 focus:border-sky-500 focus:ring-sky-500" placeholder="{{ __('Nombre o correo...') }}">
                            </div>
                            @if(!empty($searchResults))
                                <ul class="bg-white border border-slate-200 rounded-xl divide-y divide-slate-100 shadow-sm">
                                    @foreach($searchResults as $result)
                                        <li>
                                            <button type="button" wire:click="selectTeacher({{ $result['id'] }})" class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                                <span class="font-medium text-slate-900">{{ $result['name'] }}</span>
                                                <span class="block text-xs text-slate-500">{{ $result['email'] }}</span>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                            <div>
                                <p class="text-xs uppercase text-slate-500 tracking-[0.2em] mb-2">{{ __('Profesores seleccionados') }}</p>
                                <div class="flex flex-wrap gap-2">
                                    @forelse($selectedTeachers as $teacher)
                                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 text-xs border border-slate-200">
                                            {{ $teacher->name }}
                                            <button type="button" wire:click="removeTeacher({{ $teacher->id }})" class="text-slate-400 hover:text-slate-600">&times;</button>
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-500">{{ __('Aún no seleccionas profesores.') }}</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="grid gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Asunto (opcional)') }}</label>
                            <input type="text" wire:model="subject" class="w-full rounded-xl border border-slate-200 bg-white text-slate-900 px-4 py-2 focus:border-sky-500 focus:ring-sky-500" placeholder="{{ __('Tema del mensaje') }}">
                            @error('subject')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Mensaje') }}</label>
                            <textarea wire:model="body" rows="6" class="w-full rounded-2xl border border-slate-200 bg-white text-slate-900 px-4 py-3 focus:border-sky-500 focus:ring-sky-500" placeholder="{{ __('Escribe tu consulta o retroalimentación...') }}"></textarea>
                            @error('body')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                            @error('recipients')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" wire:model="notifyEmail" class="rounded text-sky-500 focus:ring-sky-500">
                            <span>{{ __('Recibir copia por correo') }}</span>
                        </label>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-sky-500 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-sky-500/20 hover:bg-sky-600 transition">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-9 4v9" /></svg>
                            {{ __('Enviar mensaje') }}
                        </button>
                    </div>
                </form>
            @else
                @if($openedMessage)
                    <article class="space-y-4">
                        <header class="border-b border-slate-100 pb-4">
                            <p class="text-sm text-slate-500">{{ __('De') }}</p>
                            <h3 class="text-lg font-semibold text-slate-900">{{ $openedMessage->sender->name }}</h3>
                            <p class="text-xs text-slate-500">{{ optional($openedMessage->sent_at)->translatedFormat('d M Y H:i') }}</p>
                        </header>
                        <div class="prose prose-slate max-w-none text-slate-700">{!! nl2br(e($openedMessage->body)) !!}</div>
                        <footer class="border-t border-slate-100 pt-4">
                            <p class="text-xs text-slate-500 uppercase tracking-[0.2em] mb-2">{{ __('Destinatarios') }}</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($openedMessage->recipients as $recipient)
                                    <span class="px-3 py-1.5 rounded-full bg-slate-100 text-xs text-slate-700 border border-slate-200">{{ $recipient->user->name }}</span>
                                @endforeach
                            </div>
                        </footer>
                    </article>
                @else
                    <div class="flex h-full items-center justify-center text-slate-500 text-sm">
                        {{ __('Selecciona un mensaje para ver el detalle.') }}
                    </div>
                @endif
            @endif
        </main>
    </div>
</section>
