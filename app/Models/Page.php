<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'type',
        'locale',
        'status',
        'published_revision_id',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class);
    }

    public function publishedRevision(): BelongsTo
    {
        return $this->belongsTo(PageRevision::class, 'published_revision_id');
    }

    public function latestRevision(): HasOne
    {
        return $this->hasOne(PageRevision::class)->latestOfMany();
    }

    public function views(): HasMany
    {
        return $this->hasMany(PageView::class);
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(PageConversion::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function currentLayout(): array
    {
        $revision = $this->revisions()->latest()->first() ?? $this->publishedRevision;

        return $revision?->layout ?? [];
    }

    public function currentSettings(): array
    {
        $revision = $this->revisions()->latest()->first() ?? $this->publishedRevision;

        return $revision?->settings ?? [];
    }
}


