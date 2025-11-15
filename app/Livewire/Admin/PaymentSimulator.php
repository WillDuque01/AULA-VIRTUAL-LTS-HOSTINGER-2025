<?php

namespace App\Livewire\Admin;

use App\Models\PaymentEvent;
use App\Models\Tier;
use App\Models\User;
use App\Support\Payments\PaymentSimulator as PaymentSimulatorService;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Component;

class PaymentSimulator extends Component
{
    public array $form = [
        'email' => '',
        'tier_id' => null,
        'provider' => 'simulator',
        'status' => 'active',
        'amount' => null,
        'currency' => 'USD',
    ];

    public array $tiers = [];

    public array $recentEvents = [];

    public ?string $flashStatus = null;

    public ?string $flashError = null;

    protected PaymentSimulatorService $simulator;

    protected $rules = [
        'form.email' => ['required', 'email'],
        'form.tier_id' => ['required', 'exists:tiers,id'],
        'form.provider' => ['required', 'string', 'max:255'],
        'form.status' => ['required', 'string', 'max:100'],
        'form.amount' => ['nullable', 'numeric', 'min:0'],
        'form.currency' => ['nullable', 'string', 'size:3'],
    ];

    public function boot(PaymentSimulatorService $simulator): void
    {
        $this->simulator = $simulator;
    }

    public function mount(): void
    {
        $this->tiers = Tier::query()
            ->orderByDesc('priority')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'access_type', 'price_monthly', 'currency'])
            ->map(fn (Tier $tier) => [
                'id' => $tier->id,
                'name' => $tier->name,
                'slug' => $tier->slug,
                'access_type' => $tier->access_type,
                'price' => $tier->price_monthly,
                'currency' => $tier->currency,
            ])
            ->toArray();

        $this->loadRecentEvents();
    }

    public function render()
    {
        return view('livewire.admin.payment-simulator', [
            'tiers' => $this->tiers,
            'events' => $this->recentEvents,
        ]);
    }

    public function simulate(): void
    {
        $this->flashStatus = null;
        $this->flashError = null;

        $validated = $this->validate()['form'];

        $user = User::where('email', $validated['email'])->first();
        if (! $user) {
            $this->flashError = __('No se encontro un usuario con ese correo.');

            return;
        }

        $tier = Tier::find($validated['tier_id']);
        if (! $tier) {
            $this->flashError = __('Tier no disponible.');

            return;
        }

        $payload = [
            'provider' => Str::lower($validated['provider'] ?: 'simulator'),
            'status' => $validated['status'] ?: 'active',
            'metadata' => ['origin' => 'admin-panel'],
        ];

        if ($validated['amount'] !== null) {
            $payload['amount'] = $validated['amount'];
        }

        if (! empty($validated['currency'])) {
            $payload['currency'] = Str::upper($validated['currency']);
        }

        $this->simulator->simulate($user, $tier, $payload);

        $this->flashStatus = __('Suscripcion simulada para :user en el tier :tier.', [
            'user' => $user->email,
            'tier' => $tier->name,
        ]);

        $this->form['amount'] = null;
        $this->loadRecentEvents();
    }

    private function loadRecentEvents(): void
    {
        $this->recentEvents = PaymentEvent::query()
            ->with(['user:id,name,email', 'tier:id,name', 'subscription:id,user_id,tier_id'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function (PaymentEvent $event) {
                return [
                    'id' => $event->id,
                    'user' => $event->user?->email,
                    'tier' => $event->tier?->name,
                    'provider' => $event->provider,
                    'status' => $event->status,
                    'amount' => $event->amount,
                    'currency' => $event->currency,
                    'created_at' => $event->created_at?->toDateTimeString(),
                ];
            })
            ->toArray();
    }
}
