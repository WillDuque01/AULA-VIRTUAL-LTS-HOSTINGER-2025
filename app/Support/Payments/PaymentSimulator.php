<?php

namespace App\Support\Payments;

use App\Events\PaymentSimulated;
use App\Models\StudentGroup;
use App\Models\Subscription;
use App\Models\Tier;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class PaymentSimulator
{
    public function simulate(User $user, Tier $tier, array $payload = []): Subscription
    {
        return DB::transaction(function () use ($user, $tier, $payload) {
            $status = $payload['status'] ?? 'active';
            $provider = $payload['provider'] ?? 'simulator';
            $amount = $payload['amount'] ?? ($tier->price_monthly ?? 0);
            $currency = $payload['currency'] ?? ($tier->currency ?? 'USD');
            $startsAt = Arr::get($payload, 'starts_at') ? CarbonImmutable::parse($payload['starts_at']) : now();
            $renewsAt = Arr::get($payload, 'renews_at');
            $metadata = $payload['metadata'] ?? ['simulated' => true];

            $subscription = Subscription::updateOrCreate([
                'user_id' => $user->id,
                'tier_id' => $tier->id,
                'provider' => $provider,
            ], [
                'status' => $status,
                'amount' => $amount,
                'currency' => $currency,
                'starts_at' => $startsAt,
                'renews_at' => $renewsAt,
                'metadata' => $metadata,
            ]);

            $user->tiers()->syncWithoutDetaching([
                $tier->id => [
                    'status' => $status,
                    'source' => $payload['source'] ?? 'simulator',
                    'assigned_by' => $payload['assigned_by'] ?? null,
                    'starts_at' => now(),
                    'ends_at' => Arr::get($payload, 'ends_at'),
                    'cancelled_at' => Arr::get($payload, 'cancelled_at'),
                    'metadata' => json_encode(array_merge(['provider' => $provider], $metadata ?? [])),
                ],
            ]);

            $this->assignToGroup($user, $tier, $payload);

            Event::dispatch(new PaymentSimulated($subscription));

            return $subscription;
        });
    }

    private function assignToGroup(User $user, Tier $tier, array $payload): void
    {
        $groupIds = Arr::wrap($payload['groups'] ?? []);

        if (empty($groupIds)) {
            $group = StudentGroup::query()
                ->where('tier_id', $tier->id)
                ->where('is_active', true)
                ->get()
                ->first(function (StudentGroup $group) {
                    if ($group->capacity === null) {
                        return true;
                    }

                    return $group->students()->whereNull('group_user.left_at')->count() < $group->capacity;
                });

            if (! $group) {
                return;
            }

            $groupIds = [$group->id];
        }

        foreach ($groupIds as $groupId) {
            $group = StudentGroup::find($groupId);

            if (! $group) {
                continue;
            }

            $group->students()->syncWithoutDetaching([
                $user->id => [
                    'assigned_by' => $payload['assigned_by'] ?? null,
                    'joined_at' => now(),
                    'metadata' => json_encode(['provider' => $payload['provider'] ?? 'simulator']),
                ],
            ]);
        }
    }
}
