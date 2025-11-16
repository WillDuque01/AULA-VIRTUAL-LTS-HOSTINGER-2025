<?php

namespace App\Services;

use App\Events\PracticePackagePurchased;
use App\Models\PracticePackage;
use App\Models\PracticePackageOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PracticePackageOrderService
{
    public function createPendingOrder(int $userId, PracticePackage $package): PracticePackageOrder
    {
        return PracticePackageOrder::create([
            'practice_package_id' => $package->id,
            'user_id' => $userId,
            'status' => 'pending',
            'sessions_remaining' => 0,
        ]);
    }

    public function markAsPaid(PracticePackageOrder $order, ?string $paymentReference = null): PracticePackageOrder
    {
        $package = $order->package;

        DB::transaction(function () use (&$order, $package, $paymentReference): void {
            $order->update([
                'status' => 'paid',
                'sessions_remaining' => $package->sessions_count,
                'paid_at' => now(),
                'payment_reference' => $paymentReference ?: Str::upper(Str::random(10)),
            ]);

            PracticePackagePurchased::dispatch($order);
        });

        return $order->fresh();
    }

    public function consumeSession(PracticePackageOrder $order): void
    {
        $order->refresh();

        if ($order->sessions_remaining <= 0) {
            return;
        }

        $order->decrement('sessions_remaining');

        if ($order->sessions_remaining <= 0) {
            $order->update(['status' => 'completed']);
        }
    }
}


