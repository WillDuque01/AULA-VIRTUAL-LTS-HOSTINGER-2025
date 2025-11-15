<?php

namespace App\Livewire\Student;

use App\Models\Message;
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
    public string $target = 'teacher_team';
    public array $selectedTeacherIds = [];
    public string $subject = '';
    public string $body = '';
    public bool $notifyEmail = true;
    public string $searchTerm = '';
    public array $searchResults = [];
    public ?int $openedMessageId = null;

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        abort_unless(Auth::user()?->hasAnyRole(['student_free', 'student_paid', 'student_vip']), 403);

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

    public function compose(): void
    {
        $this->tab = 'compose';
    }

    public function inbox(): void
    {
        $this->tab = 'inbox';
    }

    public function updatedSearchTerm(): void
    {
        $this->searchResults = $this->searchTeachers($this->searchTerm)
            ->reject(fn (User $user) => in_array($user->id, $this->selectedTeacherIds, true))
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ])->values()->all();
    }

    public function selectTeacher(int $userId): void
    {
        if (! in_array($userId, $this->selectedTeacherIds, true)) {
            $this->selectedTeacherIds[] = $userId;
        }

        $this->searchTerm = '';
        $this->searchResults = [];
    }

    public function removeTeacher(int $userId): void
    {
        $this->selectedTeacherIds = array_values(array_filter($this->selectedTeacherIds, fn (int $id) => $id !== $userId));
    }

    public function openMessage(int $messageId): void
    {
        $message = Message::query()
            ->with('recipients')
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
                    'selected_teacher_ids' => $this->selectedTeacherIds,
                ],
            ]
        );

        $this->reset(['subject', 'body', 'selectedTeacherIds']);
        $this->notifyEmail = true;
        session()->flash('status', __('Mensaje enviado correctamente.'));
        $this->tab = 'inbox';
    }

    public function render()
    {
        return view('livewire.student.message-center', [
            'messages' => $this->inboxMessages(),
            'selectedTeachers' => $this->selectedTeachers(),
            'openedMessage' => $this->currentMessage(),
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

    protected function resolveRecipients(): Collection
    {
        return match ($this->target) {
            'teacher_team' => $this->teachers(),
            'custom' => $this->usersByIds($this->selectedTeacherIds),
            default => $this->teachers(),
        };
    }

    protected function teachers(): Collection
    {
        return User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['teacher_admin', 'teacher']))
            ->get();
    }

    protected function usersByIds(array $ids): Collection
    {
        return User::query()
            ->whereIn('id', Arr::wrap($ids))
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['teacher_admin', 'teacher']))
            ->get();
    }

    protected function selectedTeachers(): Collection
    {
        return User::query()
            ->whereIn('id', $this->selectedTeacherIds)
            ->get();
    }

    protected function searchTeachers(string $term): Collection
    {
        if (Str::length($term) < 2) {
            return collect();
        }

        return User::query()
            ->where('id', '<>', Auth::id())
            ->where(fn ($query) => $query
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%"))
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['teacher_admin', 'teacher']))
            ->limit(10)
            ->get();
    }
}
