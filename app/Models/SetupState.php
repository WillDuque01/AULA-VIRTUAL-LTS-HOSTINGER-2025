<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class SetupState extends Model
{
    protected $fillable = [
        'is_completed',
        'data',
        'completed_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_completed' => 'bool',
        'completed_at' => 'datetime',
    ];

    public static function status(): self
    {
        return static::query()->latest('id')->first() ?? new self(['is_completed' => false]);
    }

    public static function markCompleted(array $data = []): void
    {
        static::query()->create([
            'is_completed' => true,
            'data' => $data,
            'completed_at' => Carbon::now(),
        ]);
    }

    public function scopeIncomplete($query)
    {
        return $query->where('is_completed', false);
    }

    public static function isCompleted(): bool
    {
        return static::query()->where('is_completed', true)->exists();
    }
}
