<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class OfferLaunched
{
    use Dispatchable;
    use SerializesModels;

    public Collection $recipients;

    public function __construct(
        iterable $recipients,
        public string $offerTitle,
        public string $offerDescription,
        public string $tierLabel,
        public ?string $offerUrl = null,
        public ?string $validUntil = null,
        public ?string $price = null,
        public ?string $discount = null,
        public ?string $intro = null,
    ) {
        $this->recipients = collect($recipients);
    }
}
