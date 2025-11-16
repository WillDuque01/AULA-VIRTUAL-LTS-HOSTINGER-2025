<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class PracticePackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'lesson_id',
        'title',
        'subtitle',
        'description',
        'sessions_count',
        'price_amount',
        'price_currency',
        'is_global',
        'visibility',
        'delivery_platform',
        'delivery_url',
        'status',
        'meta',
    ];

    protected $casts = [
        'is_global' => 'boolean',
        'price_amount' => 'decimal:2',
        'meta' => 'array',
        'status' => 'string',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(PracticePackageOrder::class);
    }

    public function product(): MorphOne
    {
        return $this->morphOne(Product::class, 'productable');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}


