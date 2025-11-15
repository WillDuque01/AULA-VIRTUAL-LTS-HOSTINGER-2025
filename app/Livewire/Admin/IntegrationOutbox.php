<?php

namespace App\Livewire\Admin;

use App\Jobs\DispatchIntegrationEventJob;
use App\Models\IntegrationEvent;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class IntegrationOutbox extends Component
{
    use WithPagination;

    public string $status = 'all';

    public string $search = '';

    public string $target = 'all';

    public int $perPage = 10;

    public ?int $expandedEventId = null;

    protected $queryString = [
        'status' => ['except' => 'all'],
        'search' => ['except' => ''],
        'target' => ['except' => 'all'],
    ];

    protected $listeners = [
        'integration-outbox-refresh' => '$refresh',
    ];

    public function mount(): void
    {
        abort_unless(Auth::user()?->can('manage-settings'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingTarget(): void
    {
        $this->resetPage();
    }

    public function retry(int $eventId): void
    {
        $event = IntegrationEvent::findOrFail($eventId);
        $event->update([
            'status' => 'pending',
            'last_error' => null,
            'attempts' => 0,
        ]);

        DispatchIntegrationEventJob::dispatch($event->id);

        $this->dispatch('notify', message: __('outbox.retry_dispatched'));
    }

    public function toggleDetails(int $eventId): void
    {
        $this->expandedEventId = $this->expandedEventId === $eventId ? null : $eventId;
    }

    public function render()
    {
        return view('livewire.admin.integration-outbox', [
            'events' => $this->events(),
            'statuses' => $this->availableStatuses(),
            'targets' => $this->availableTargets(),
        ]);
    }

    private function events()
    {
        return IntegrationEvent::query()
            ->when(
                $this->status !== 'all',
                fn ($query) => $query->where('status', $this->status)
            )
            ->when(
                $this->search !== '',
                fn ($query) => $query->where(function ($inner) {
                    $inner->where('event', 'like', "%{$this->search}%")
                        ->orWhere('target', 'like', "%{$this->search}%");
                })
            )
            ->when(
                $this->target !== 'all',
                fn ($query) => $query->where('target', $this->target)
            )
            ->latest()
            ->paginate($this->perPage);
    }

    private function availableStatuses(): array
    {
        return [
            'all' => __('outbox.status.all'),
            'pending' => __('outbox.status.pending'),
            'sent' => __('outbox.status.sent'),
            'failed' => __('outbox.status.failed'),
        ];
    }

    private function availableTargets(): array
    {
        $targets = IntegrationEvent::select('target')
            ->distinct()
            ->pluck('target')
            ->filter()
            ->sort()
            ->values()
            ->all();

        return array_merge(['all' => __('outbox.target.all')], array_combine($targets, $targets));
    }
}

