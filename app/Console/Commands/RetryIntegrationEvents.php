<?php

namespace App\Console\Commands;

use App\Jobs\DispatchIntegrationEventJob;
use App\Models\IntegrationEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class RetryIntegrationEvents extends Command
{
    protected $signature = 'integration:retry {status=failed : Status to requeue (failed|pending)} {--target= : Filter by integration target}';

    protected $description = 'Re-enqueue integration events so they can be dispatched again.';

    public function handle(): int
    {
        $status = $this->argument('status');

        if (! in_array($status, ['failed', 'pending'], true)) {
            $this->error('Status must be either "failed" or "pending".');

            return self::FAILURE;
        }

        $query = IntegrationEvent::where('status', $status);

        if ($target = $this->option('target')) {
            $query->where('target', $target);
        }

        $count = 0;

        $query->orderBy('id')
            ->chunkById(100, function ($events) use (&$count): void {
                foreach ($events as $event) {
                    $event->update([
                        'status' => 'pending',
                        'last_error' => null,
                        'attempts' => 0,
                    ]);

                    Bus::dispatch(new DispatchIntegrationEventJob($event->id));
                    $count++;
                }
            });

        $this->info("Requeued {$count} event(s).");

        return self::SUCCESS;
    }
}

