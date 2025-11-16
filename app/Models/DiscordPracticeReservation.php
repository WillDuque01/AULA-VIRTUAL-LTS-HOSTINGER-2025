<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscordPracticeReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'discord_practice_id',
        'user_id',
        'practice_package_order_id',
        'status',
        'notes',
    ];

    public function practice(): BelongsTo
    {
        return $this->belongsTo(DiscordPractice::class, 'discord_practice_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function packageOrder(): BelongsTo
    {
        return $this->belongsTo(PracticePackageOrder::class, 'practice_package_order_id');
    }
}


