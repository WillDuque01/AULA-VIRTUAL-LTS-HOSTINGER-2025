<?php

namespace App\Livewire\Admin;

use App\Models\Message;
use App\Models\Tier;
use App\Models\User;
use App\Support\Messaging\MessageService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class MessageCenter extends Component
{
    use WithPagination;

    public string $tab = 'inbox';
    public string $target = 'students_all';
    public ?int $selectedTierId = null;
    public array $selectedUserIds = [];
    public string $subject = '';
    public string $body = '';
    public bool $notifyEmail = true;
    public string $searchTerm = '';
    public array $searchResults = [];
    public ?int $openedMessageId = null;

    protected $paginationTheme = 'tailwind';

    protected $listeners = [
        'refreshMessages' => '$refresh',
    ];

    public function mount(): void
    {
        abort_unless(Auth::user()?->hasAnyRole(['teacher_admin', 'teacher']), 403);

        if ($uuid = request()->query('message')) {
            $message = Message::query()
                ->forUser(Auth::user())
                ->where('uuid', $uuid)
                ->first();

            if ($message) {
                $this->openMessage($message->id);
            }
        }
    }

    public function updatingSearchTerm(): void
    {
        $this->resetPage();
    }

    public function updatedSearchTerm(): void
    {
        $this->searchResults = $this->queryUsers($this->searchTerm)
            ->reject(fn (User $user) => in_array($user->id, $this->selectedUserIds, true))
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ])->values()->all();
    }

    public function selectUser(int $userId): void
    {
        if (! in_array($userId, $this->selectedUserIds, true)) {
            $this->selectedUserIds[] = $userId;
        }

        $this->searchTerm = '';
        $this->searchResults = [];
    }

    public function removeUser(int $userId): void
    {
        $this->selectedUserIds = array_values(array_filter(
            $this->selectedUserIds,
            fn (int $id) => $id !== $userId
        ));
    }

    public function compose(): void
    {
        $this->tab = 'compose';
    }

    public function inbox(): void
    {
        $this->tab = 'inbox';
    }

    public function openMessage(int $messageId): void
    {
        $message = Message::query()
            ->with(['recipients'])
            ->forUser(Auth::user())
            ->findOrFail($messageId);

        $recipient = $message->recipients->firstWhere('user_id', Auth::id());
        if ($recipient) {
            $recipient->markAsRead();
        }

        $this->openedMessageId = $messageId;
        $this->tab = 'inbox';
    }

    public function send(MessageService $service): void
    {
        $this->validate([
            'body' => ['required', 'string', 'min:3'],
            'subject' => ['nullable', 'string', 'max:255'],
            'target' => ['required', 'string'],
            'selectedTierId' => ['nullable', 'exists:tiers,id'],
        ]);

        $recipients = $this->resolveRecipients();

        if ($recipients->isEmpty()) {
            $this->addError('recipients', __('Selecciona al menos un destinatario válido.'));

            return;
        }

        $service->send(
            Auth::user(),
            $recipients,
            [
                'body' => $this->body,
                'subject' => $this->subject,
                'type' => $this->target,
                'notify_email' => $this->notifyEmail,
                'metadata' => [
                    'selected_tier' => $this->selectedTierId,
                    'selected_user_ids' => $this->selectedUserIds,
                ],
            ]
        );

        $this->reset(['subject', 'body', 'selectedTierId', 'selectedUserIds', 'notifyEmail', 'tab']);
        $this->notifyEmail = true;
        session()->flash('status', __('Mensaje enviado correctamente.'));
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.admin.message-center', [
            'messages' => $this->inboxMessages(),
            'tiers' => $this->loadTiers(),
            'openedMessage' => $this->currentMessage(),
            'selectedUsers' => User::query()->whereIn('id', $this->selectedUserIds)->get(),
        ]);
    }

    protected function inboxMessages(): LengthAwarePaginator
    {
        return Message::query()
            ->with(['sender', 'recipients.user'])
            ->forUser(Auth::user())
            ->latest('sent_at')
            ->paginate(10);
    }

    protected function currentMessage(): ?Message
    {
        if (! $this->openedMessageId) {
            return null;
        }

        return Message::query()
            ->with(['sender', 'recipients.user', 'replies.sender'])
            ->forUser(Auth::user())
            ->find($this->openedMessageId);
    }

    protected function loadTiers(): Collection
    {
        return Tier::query()->orderByDesc('priority')->get();
    }

    protected function queryUsers(string $term): Collection
    {
        if (Str::length($term) < 2) {
            return collect();
        }

        $roles = $this->target === 'teachers_all'
            ? ['teacher_admin', 'teacher']
            : ['student_free', 'student_paid', 'student_vip'];

        return User::query()
            ->where('id', '<>', Auth::id())
            ->where(fn ($query) => $query
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%"))
            ->whereHas('roles', fn ($query) => $query->whereIn('name', $roles))
            ->limit(10)
            ->get();
    }

    protected function resolveRecipients(): Collection
    {
        return match ($this->target) {
            'teachers_all' => $this->usersByRoles(['teacher_admin', 'teacher']),
            'students_all' => $this->usersByRoles(['student_free', 'student_paid', 'student_vip']),
            'students_tier' => $this->recipientsFromTier(),
            'custom' => $this->usersByIds($this->selectedUserIds),
            default => $this->usersByRoles(['student_free', 'student_paid', 'student_vip']),
        };
    }

    protected function usersByRoles(array $roles): Collection
    {
        return User::query()
            ->where('id', '<>', Auth::id())
            ->whereHas('roles', fn ($query) => $query->whereIn('name', $roles))
            ->get();
    }

    protected function usersByIds(array $ids): Collection
    {
        return User::query()
            ->whereIn('id', Arr::wrap($ids))
            ->where('id', '<>', Auth::id())
            ->get();
    }

    protected function recipientsFromTier(): Collection
    {
        if (! $this->selectedTierId) {
            return collect();
        }

        $tier = Tier::with('activeUsers')->find($this->selectedTierId);

        if (! $tier) {
            return collect();
        }

        return $tier->activeUsers()->where('users.id', '<>', Auth::id())->get();
    }
}

