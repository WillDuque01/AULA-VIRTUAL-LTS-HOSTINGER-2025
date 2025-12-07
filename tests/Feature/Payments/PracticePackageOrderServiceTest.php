<?php

namespace Tests\Feature\Payments;

use App\Events\PracticePackagePurchased;
use App\Events\PracticePackageSessionConsumed;
use App\Models\PracticePackage;
use App\Models\PracticePackageOrder;
use App\Models\User;
use App\Services\PracticePackageOrderService;
use Database\Seeders\AuditorProfilesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PracticePackageOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private PracticePackage $package;
    private User $studentPaid;
    private User $studentPending;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AuditorProfilesSeeder::class);

        $this->package = PracticePackage::firstWhere('title', 'QA Discord Pack');
        $this->studentPaid = User::whereEmail('student.paid@letstalkspanish.io')->firstOrFail();
        $this->studentPending = User::whereEmail('student.pending@letstalkspanish.io')->firstOrFail();
    }

    public function test_pending_order_can_be_marked_as_paid_via_service(): void
    {
        Event::fake([PracticePackagePurchased::class]);

        $order = PracticePackageOrder::where('user_id', $this->studentPending->id)->firstOrFail();
        $this->assertSame('pending', $order->status);

        $service = app(PracticePackageOrderService::class);
        $service->markAsPaid($order, 'WEBHOOK-TEST-001');

        $order->refresh();

        $this->assertSame('paid', $order->status);
        $this->assertSame($this->package->sessions_count, $order->sessions_remaining);
        $this->assertSame('WEBHOOK-TEST-001', $order->payment_reference);
        $this->assertNotNull($order->paid_at);

        Event::assertDispatched(PracticePackagePurchased::class, function ($event) use ($order) {
            return $event->order->is($order);
        });
    }

    public function test_consume_session_decrements_inventory_and_marks_completed(): void
    {
        Event::fake([PracticePackageSessionConsumed::class]);

        $order = PracticePackageOrder::where('user_id', $this->studentPaid->id)->firstOrFail();
        $order->update([
            'sessions_remaining' => 1,
            'status' => 'paid',
        ]);

        $service = app(PracticePackageOrderService::class);
        $service->consumeSession($order);

        $order->refresh();

        $this->assertSame(0, $order->sessions_remaining);
        $this->assertSame('completed', $order->status);

        Event::assertDispatched(PracticePackageSessionConsumed::class, function ($event) use ($order) {
            return $event->order->is($order);
        });
    }
}

