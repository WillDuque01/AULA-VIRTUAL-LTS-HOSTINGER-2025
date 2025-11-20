<?php

namespace App\Models;

use App\Models\CohortRegistration;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

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
        'enrolled_count',
        'price_amount',
        'price_currency',
        'status',
        'is_featured',
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
        'price_amount' => 'decimal:2',
        'is_featured' => 'boolean',
        'slots' => 'array',
        'meta' => 'array',
        'enrolled_count' => 'integer',
    ];

    public function practicePackage(): BelongsTo
    {
        return $this->belongsTo(PracticePackage::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function product(): MorphOne
    {
        return $this->morphOne(Product::class, 'productable');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(CohortRegistration::class);
    }

    public function remainingSlots(): int
    {
        $capacity = (int) ($this->capacity ?? 0);
        $enrolled = (int) ($this->enrolled_count ?? 0);

        return max(0, $capacity - $enrolled);
    }

    public function isSoldOut(): bool
    {
        return $this->remainingSlots() <= 0;
    }

    public function refreshEnrollmentMetrics(): void
    {
        $enrolled = $this->registrations()
            ->whereIn('status', ['paid', 'confirmed'])
            ->count();

        $this->forceFill([
            'enrolled_count' => $enrolled,
        ])->saveQuietly();

        $this->product?->update([
            'inventory' => $this->remainingSlots(),
        ]);
    }
}


