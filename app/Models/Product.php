<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'slug',
        'excerpt',
        'description',
        'status',
        'category',
        'badge',
        'thumbnail_path',
        'price_amount',
        'compare_at_amount',
        'price_currency',
        'is_featured',
        'inventory',
        'meta',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'price_amount' => 'decimal:2',
        'compare_at_amount' => 'decimal:2',
        'meta' => 'array',
    ];

    public function productable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getDisplayPriceAttribute(): string
    {
        return sprintf('%s %s', $this->price_currency, number_format((float) $this->price_amount, 2));
    }
}


