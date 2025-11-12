<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tier_id',
        'provider',
        'provider_subscription_id',
        'provider_customer_id',
        'status',
        'starts_at',
        'renews_at',
        'trial_ends_at',
        'cancelled_at',
        'amount',
        'currency',
        'metadata',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'renews_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(Tier::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ($this->cancelled_at === null || $this->cancelled_at->isFuture());
    }
}
