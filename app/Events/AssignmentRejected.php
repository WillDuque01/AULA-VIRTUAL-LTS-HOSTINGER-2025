<?php

namespace App\Events;

use App\Models\AssignmentSubmission;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssignmentRejected
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public AssignmentSubmission $submission, public ?string $reason = null)
    {
    }
}


