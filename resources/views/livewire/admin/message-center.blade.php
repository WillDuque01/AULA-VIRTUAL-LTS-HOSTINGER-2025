<section class="space-y-6">
    <header class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-slate-100">{{ __('Centro de mensajes') }}</h2>
            <p class="text-sm text-slate-400">{{ __('Coordina comunicaciones internas entre docentes y estudiantes.') }}</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="inbox" class="px-3 py-2 rounded-xl text-sm font-medium {{ $tab === 'inbox' ? 'bg-sky-500/20 text-sky-300' : 'bg-slate-800 text-slate-300' }}">{{ __('Bandeja') }}</button>
            <button wire:click="compose" class="px-3 py-2 rounded-xl text-sm font-medium {{ $tab === 'compose' ? 'bg-sky-500/20 text-sky-300' : 'bg-slate-800 text-slate-300' }}">{{ __('Redactar') }}</button>
        </div>
    </header>

    <div class="grid gap-6 lg:grid-cols-[340px_1fr]">
        <aside class="space-y-4">
            <div class="bg-slate-900/70 border border-slate-800 rounded-2xl overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-200">{{ __('Mensajes recientes') }}</h3>
                    <span class="text-xs text-slate-500">{{ $messages->total() }}</span>
                </div>
                <ul class="divide-y divide-slate-800 max-h-[520px] overflow-y-auto">
                    @forelse($messages as $message)
                        <li>
                            <button wire:click="openMessage({{ $message->id }})" class="w-full text-left px-4 py-3 hover:bg-slate-800/60 {{ optional($message->recipients->firstWhere('user_id', auth()->id()))?->status === 'unread' ? 'bg-slate-800/30' : '' }}">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-slate-100 truncate">{{ $message->sender->name }}</span>
                                    <span class="text-xs text-slate-500">{{ optional($message->sent_at)->diffForHumans() }}</span>
                                </div>
                                <p class="text-xs text-slate-400 truncate mt-1">{{ $message->subject ?? __('(Sin asunto)') }}</p>
                                <p class="text-xs text-slate-500 line-clamp-2 mt-1">{{ \Illuminate\Support\Str::limit(strip_tags($message->body), 120) }}</p>
                            </button>
                        </li>
                    @empty
                        <li class="px-4 py-6 text-sm text-slate-500 text-center">{{ __('Aún no hay mensajes.') }}</li>
                    @endforelse
                </ul>
                <div class="px-4 py-3 border-t border-slate-800">
                    {{ $messages->links() }}
                </div>
            </div>
        </aside>

        <main class="bg-slate-900/70 border border-slate-800 rounded-2xl p-6">
            @if(session('status'))
                <div class="mb-4 rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            @if($tab === 'compose')
                <form wire:submit.prevent="send" class="space-y-5">
                    <div class="grid gap-3 md:grid-cols-2">
                        <label class="flex items-center gap-2 p-3 rounded-xl border {{ $target === 'teachers_all' ? 'border-sky-500/60 bg-sky-500/10' : 'border-slate-800 bg-transparent' }} cursor-pointer">
                            <input type="radio" class="text-sky-500 focus:ring-sky-500" value="teachers_all" wire:model="target">
                            <div>
                                <p class="text-sm font-semibold text-slate-100">{{ __('Docentes (todos)') }}</p>
                                <p class="text-xs text-slate-400">{{ __('Envía a teacher admin y docentes activos.') }}</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-2 p-3 rounded-xl border {{ $target === 'students_all' ? 'border-sky-500/60 bg-sky-500/10' : 'border-slate-800 bg-transparent' }} cursor-pointer">
                            <input type="radio" class="text-sky-500 focus:ring-sky-500" value="students_all" wire:model="target">
                            <div>
                                <p class="text-sm font-semibold text-slate-100">{{ __('Estudiantes (todos)') }}</p>
                                <p class="text-xs text-slate-400">{{ __('Incluye estudiantes gratuitos, pagos y VIP.') }}</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-2 p-3 rounded-xl border {{ $target === 'students_tier' ? 'border-sky-500/60 bg-sky-500/10' : 'border-slate-800 bg-transparent' }} cursor-pointer">
                            <input type="radio" class="text-sky-500 focus:ring-sky-500" value="students_tier" wire:model="target">
                            <div>
                                <p class="text-sm font-semibold text-slate-100">{{ __('Por tier') }}</p>
                                <p class="text-xs text-slate-400">{{ __('Selecciona un tier para enviar mensaje segmentado.') }}</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-2 p-3 rounded-xl border {{ $target === 'custom' ? 'border-sky-500/60 bg-sky-500/10' : 'border-slate-800 bg-transparent' }} cursor-pointer">
                            <input type="radio" class="text-sky-500 focus:ring-sky-500" value="custom" wire:model="target">
                            <div>
                                <p class="text-sm font-semibold text-slate-100">{{ __('Destinatarios específicos') }}</p>
                                <p class="text-xs text-slate-400">{{ __('Elige manualmente docentes o estudiantes.') }}</p>
                            </div>
                        </label>
                    </div>

                    @if($target === 'students_tier')
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">{{ __('Selecciona un tier') }}</label>
                            <select wire:model="selectedTierId" class="w-full rounded-xl border border-slate-800 bg-slate-950/60 text-slate-100 px-4 py-2">
                                <option value="">{{ __('Selecciona...') }}</option>
                                @foreach($tiers as $tier)
                                    <option value="{{ $tier->id }}">{{ $tier->name }}</option>
                                @endforeach
                            </select>
                            @error('selectedTierId')
                                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    @if($target === 'custom')
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">{{ __('Buscar usuarios') }}</label>
                                <input type="text" wire:model.live="searchTerm" class="w-full rounded-xl border border-slate-800 bg-slate-950/60 text-slate-100 px-4 py-2" placeholder="{{ __('Nombre o correo...') }}">
                            </div>

                            @if(!empty($searchResults))
                                <ul class="bg-slate-950/90 border border-slate-800 rounded-xl divide-y divide-slate-800">
                                    @foreach($searchResults as $result)
                                        <li>
                                            <button type="button" wire:click="selectUser({{ $result['id'] }})" class="w-full text-left px-4 py-2 text-sm text-slate-200 hover:bg-slate-800/60">
                                                <span class="font-medium">{{ $result['name'] }}</span>
                                                <span class="block text-xs text-slate-400">{{ $result['email'] }}</span>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            <div>
                                <p class="text-xs uppercase text-slate-500 tracking-[0.2em] mb-2">{{ __('Destinatarios seleccionados') }}</p>
                                <div class="flex flex-wrap gap-2">
                                    @forelse($selectedUsers as $user)
                                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-800 text-slate-200 text-xs">
                                            {{ $user->name }}
                                            <button type="button" wire:click="removeUser({{ $user->id }})" class="text-slate-400 hover:text-slate-200">&times;</button>
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-500">{{ __('Todavía no seleccionas usuarios.') }}</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="grid gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">{{ __('Asunto (opcional)') }}</label>
                            <input type="text" wire:model="subject" class="w-full rounded-xl border border-slate-800 bg-slate-950/60 text-slate-100 px-4 py-2" placeholder="{{ __('Resumen del mensaje') }}">
                            @error('subject')
                                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">{{ __('Mensaje') }}</label>
                            <textarea wire:model="body" rows="6" class="w-full rounded-2xl border border-slate-800 bg-slate-950/60 text-slate-100 px-4 py-3" placeholder="{{ __('Comparte novedades, materiales o recordatorios...') }}"></textarea>
                            @error('body')
                                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                            @error('recipients')
                                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                            <input type="checkbox" wire:model="notifyEmail" class="text-sky-500 focus:ring-sky-500">
                            <span>{{ __('Enviar también por correo electrónico') }}</span>
                        </label>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-sky-500 px-5 py-2 text-sm font-semibold text-slate-950 shadow-lg shadow-sky-500/20">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-9 4v9" /></svg>
                            {{ __('Enviar mensaje') }}
                        </button>
                    </div>
                </form>
            @else
                @if($openedMessage)
                    <article class="space-y-4">
                        <header class="border-b border-slate-800 pb-4">
                            <p class="text-sm text-slate-400">{{ __('De') }}</p>
                            <h3 class="text-lg font-semibold text-slate-100">{{ $openedMessage->sender->name }}</h3>
                            <p class="text-xs text-slate-500">{{ optional($openedMessage->sent_at)->translatedFormat('d M Y H:i') }}</p>
                        </header>
                        <div class="prose prose-invert max-w-none text-slate-200">{!! nl2br(e($openedMessage->body)) !!}</div>
                        <footer class="border-t border-slate-800 pt-4">
                            <p class="text-xs text-slate-500 uppercase tracking-[0.2em] mb-2">{{ __('Destinatarios') }}</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($openedMessage->recipients as $recipient)
                                    <span class="px-3 py-1.5 rounded-full bg-slate-800/70 text-xs text-slate-300">{{ $recipient->user->name }}</span>
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
