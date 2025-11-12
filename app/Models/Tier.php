<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tier extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'tagline',
        'description',
        'priority',
        'access_type',
        'is_default',
        'is_active',
        'price_monthly',
        'price_yearly',
        'currency',
        'features',
        'metadata',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'priority' => 'integer',
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'features' => 'array',
        'metadata' => 'array',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot([
                'status',
                'source',
                'assigned_by',
                'starts_at',
                'ends_at',
                'cancelled_at',
                'metadata',
            ])
            ->withTimestamps();
    }

    public function activeUsers(): BelongsToMany
    {
        return $this->users()->wherePivot('status', 'active');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(StudentGroup::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function courses(): MorphToMany
    {
        return $this->morphedByMany(Course::class, 'tierable')->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isPaid(): bool
    {
        return in_array($this->access_type, ['paid', 'vip', 'premium'], true);
    }
}
