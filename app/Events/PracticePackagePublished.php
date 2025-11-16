<?php

namespace App\Events;

use App\Models\PracticePackage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PracticePackagePublished
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public PracticePackage $package)
    {
    }
}


