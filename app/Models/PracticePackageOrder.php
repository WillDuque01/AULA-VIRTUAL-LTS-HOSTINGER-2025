<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PracticePackageOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'practice_package_id',
        'user_id',
        'status',
        'sessions_remaining',
        'payment_reference',
        'paid_at',
        'meta',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'meta' => 'array',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(PracticePackage::class, 'practice_package_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}


