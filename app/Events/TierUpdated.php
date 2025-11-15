<?php

namespace App\Events;

use App\Models\Tier;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class TierUpdated
{
    use Dispatchable;
    use SerializesModels;

    public Collection $recipients;

    public function __construct(public Tier $tier, iterable $recipients)
    {
        $this->recipients = collect($recipients);
    }
}
