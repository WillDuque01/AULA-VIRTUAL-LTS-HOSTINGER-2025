<?php

namespace App\Models;

use App\Events\ModuleUnlocked;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = ['chapter_id','type','config','position','locked'];

    protected $casts = [
        'config' => 'array',
        'locked' => 'boolean',
    ];

    protected static array $unlockingStates = [];

    protected static function boot()
    {
        parent::boot();

        static::updating(function (self $lesson): void {
            self::$unlockingStates[$lesson->getKey()] = $lesson->getOriginal('locked') && ! $lesson->locked;
        });

        static::updated(function (self $lesson): void {
            $unlocking = self::$unlockingStates[$lesson->getKey()] ?? false;
            unset(self::$unlockingStates[$lesson->getKey()]);

            if (! $unlocking) {
                return;
            }

            $chapter = $lesson->chapter;
            $course = $chapter?->course;

            if (! $course) {
                return;
            }

            $tiers = $course->tiers()->with(['users' => function ($query) {
                $query->wherePivot('status', 'active');
            }])->get();

            $recipients = $tiers
                ->flatMap(fn ($tier) => $tier->users)
                ->filter()
                ->unique('id')
                ->values();

            if ($recipients->isEmpty()) {
                return;
            }

            $audience = $tiers->pluck('name')->filter()->implode(', ');
            $moduleTitle = data_get($lesson->config, 'title', $chapter->title ?? __('Lesson'));
            $moduleUrl = route('lessons.player', ['locale' => app()->getLocale(), 'lesson' => $lesson]);

            ModuleUnlocked::dispatch(
                $course,
                $recipients,
                $moduleTitle,
                $audience ?: __('Students'),
                $moduleUrl
            );
        });
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function quiz()
    {
        return $this->hasOne(Quiz::class);
    }

    public function assignment()
    {
        return $this->hasOne(Assignment::class);
    }
}

