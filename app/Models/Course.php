<?php

namespace App\Models;

use App\Events\CourseUnlocked;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['slug','level','published'];

    protected static array $publishingStates = [];

    protected static function boot()
    {
        parent::boot();

        static::updating(function (self $course): void {
            self::$publishingStates[$course->getKey()] = ! $course->getOriginal('published') && $course->published;
        });

        static::updated(function (self $course): void {
            $publishing = self::$publishingStates[$course->getKey()] ?? false;
            unset(self::$publishingStates[$course->getKey()]);

            if (! $publishing) {
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
            $title = $course->i18n
                ->firstWhere('locale', app()->getLocale())
                ?->title ?? $course->slug;
            $summary = $course->i18n
                ->firstWhere('locale', app()->getLocale())
                ?->description ?? __('Se desbloqueó un nuevo curso.');
            $courseUrl = url(sprintf('/%s/catalog', app()->getLocale()));

            CourseUnlocked::dispatch(
                $course,
                $recipients,
                $title,
                $summary,
                $audience ?: __('Students'),
                $courseUrl
            );
        });
    }

    public function i18n()
    {
        return $this->hasMany(CourseI18n::class);
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class)->orderBy('position');
    }

    public function tiers(): MorphToMany
    {
        return $this->morphToMany(Tier::class, 'tierable')->withTimestamps();
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_teacher', 'course_id', 'teacher_id')
            ->withPivot(['assigned_by'])
            ->withTimestamps();
    }
}
