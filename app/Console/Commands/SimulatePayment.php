<?php

namespace App\Console\Commands;

use App\Models\Tier;
use App\Models\User;
use App\Support\Payments\PaymentSimulator;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class SimulatePayment extends Command
{
    protected $signature = 'simulate:payment {email : Correo del estudiante} {tier : Slug del tier} {--provider=simulator} {--amount=} {--currency=} {--status=active} {--group=* : IDs de grupos a asignar}';

    protected $description = 'Genera una suscripcion simulada y asigna tiers/grupos sin pasar por la pasarela real.';

    public function __construct(private readonly PaymentSimulator $simulator)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $email = $this->argument('email');
        $tierSlug = $this->argument('tier');

        $user = User::where('email', $email)->first();
        if (! $user) {
            $this->error("No se encontro un usuario con email {$email}.");

            return self::FAILURE;
        }

        $tier = Tier::where('slug', $tierSlug)->first();
        if (! $tier) {
            $this->error("No se encontro un tier con slug {$tierSlug}.");

            return self::FAILURE;
        }

        $payload = [
            'provider' => $this->option('provider'),
            'status' => $this->option('status') ?? 'active',
            'amount' => $this->option('amount') ?? $tier->price_monthly,
            'currency' => $this->option('currency') ?? $tier->currency,
            'groups' => Arr::wrap($this->option('group')),
            'metadata' => ['initiator' => 'simulate:payment'],
        ];

        $subscription = $this->simulator->simulate($user, $tier, $payload);

        $this->info("Suscripcion simulada creada para {$user->email} en tier {$tier->slug}." );
        $this->line("Subscription ID: {$subscription->id}, status: {$subscription->status}, provider: {$subscription->provider}");

        return self::SUCCESS;
    }
}
