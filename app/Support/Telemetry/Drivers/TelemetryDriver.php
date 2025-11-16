<?php

namespace App\Support\Telemetry\Drivers;

use Illuminate\Support\Collection;

interface TelemetryDriver
{
    public function name(): string;

    public function enabled(): bool;

    /**
     * @param \Illuminate\Support\Collection<int,\App\Models\VideoPlayerEvent> $events
     */
    public function send(Collection $events): void;
}

