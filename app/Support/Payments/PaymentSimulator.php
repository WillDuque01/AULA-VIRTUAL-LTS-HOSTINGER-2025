<?php

namespace App\Support\Payments;

use App\Events\CourseUnlocked;
use App\Events\PaymentSimulated;
use App\Models\Course;
use App\Models\StudentGroup;
use App\Models\Subscription;
use App\Models\Tier;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

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
            $this->dispatchCourseUnlocks($user, $tier);

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

    private function dispatchCourseUnlocks(User $user, Tier $tier): void
    {
        $courses = $tier->courses()->with('i18n')->get();

        if ($courses->isEmpty()) {
            return;
        }

        $locale = app()->getLocale();

        foreach ($courses as $course) {
            CourseUnlocked::dispatch(
                $course,
                [$user],
                $this->resolveCourseTitle($course, $locale),
                $this->resolveCourseSummary($course, $locale, $tier->name),
                $tier->name,
                url(sprintf('/%s/courses/%s', $locale, $course->slug))
            );
        }
    }

    private function resolveCourseTitle(Course $course, string $locale): string
    {
        $translation = $course->i18n->firstWhere('locale', $locale) ?? $course->i18n->first();

        return $translation?->title ?? $course->slug;
    }

    private function resolveCourseSummary(Course $course, string $locale, string $tierName): string
    {
        $translation = $course->i18n->firstWhere('locale', $locale) ?? $course->i18n->first();
        $summary = $translation?->description ?? __('Se desbloqueó un nuevo curso asociado a :tier.', ['tier' => $tierName]);

        return Str::limit(strip_tags($summary), 160);
    }
}
