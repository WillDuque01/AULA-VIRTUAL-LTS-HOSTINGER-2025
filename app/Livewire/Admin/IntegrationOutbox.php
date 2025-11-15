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

    public int $perPage = 10;

    protected $queryString = [
        'status' => ['except' => 'all'],
        'search' => ['except' => ''],
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

    public function retry(int $eventId): void
    {
        $event = IntegrationEvent::findOrFail($eventId);
        $event->update([
            'status' => 'pending',
            'error_message' => null,
            'attempts' => 0,
        ]);

        DispatchIntegrationEventJob::dispatch($event->id);

        $this->dispatch('notify', message: __('outbox.retry_dispatched'));
    }

    public function render()
    {
        return view('livewire.admin.integration-outbox', [
            'events' => $this->events(),
            'statuses' => $this->availableStatuses(),
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
}

