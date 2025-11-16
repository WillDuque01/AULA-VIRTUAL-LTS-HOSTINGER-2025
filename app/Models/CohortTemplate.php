<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CohortTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'cohort_label',
        'duration_minutes',
        'capacity',
        'requires_package',
        'practice_package_id',
        'slots',
        'meta',
        'created_by',
    ];

    protected $casts = [
        'requires_package' => 'boolean',
        'duration_minutes' => 'integer',
        'capacity' => 'integer',
        'slots' => 'array',
        'meta' => 'array',
    ];

    public function practicePackage(): BelongsTo
    {
        return $this->belongsTo(PracticePackage::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}


