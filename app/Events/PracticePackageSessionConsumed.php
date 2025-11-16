<?php

namespace App\Events;

use App\Models\PracticePackageOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PracticePackageSessionConsumed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public PracticePackageOrder $order
    ) {
    }
}


