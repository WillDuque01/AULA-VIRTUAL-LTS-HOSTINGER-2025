<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CohortRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'cohort_template_id',
        'user_id',
        'status',
        'payment_reference',
        'amount',
        'currency',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
        'status' => 'string',
    ];

    public function cohortTemplate(): BelongsTo
    {
        return $this->belongsTo(CohortTemplate::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}


