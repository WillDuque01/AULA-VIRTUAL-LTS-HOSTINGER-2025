<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'country',
        'state',
        'city',
        'headline',
        'bio',
        'teaching_since',
        'specialties',
        'languages',
        'certifications',
        'linkedin_url',
        'teacher_notes',
        'password',
        'experience_points',
        'current_streak',
        'last_completion_at',
        'profile_completed_at',
        'profile_completion_score',
        'profile_meta',
        'profile_last_reminded_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_completion_at' => 'datetime',
            'profile_completed_at' => 'datetime',
            'profile_meta' => 'array',
            'specialties' => 'array',
            'languages' => 'array',
            'certifications' => 'array',
            'profile_last_reminded_at' => 'datetime',
        ];
    }

    public function profileSummary(): array
    {
        return \App\Support\Profile\ProfileCompletion::summarize($this);
    }

    public function notificationPreferences()
    {
        return $this->hasOne(UserNotificationPreference::class);
    }

    public function tiers(): BelongsToMany
    {
        return $this->belongsToMany(Tier::class)
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

    public function activeTiers(): BelongsToMany
    {
        return $this->tiers()->wherePivot('status', 'active');
    }

    public function studentGroups(): BelongsToMany
    {
        return $this->belongsToMany(StudentGroup::class, 'group_user')
            ->withPivot([
                'assigned_by',
                'joined_at',
                'left_at',
                'metadata',
            ])
            ->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function gamificationEvents(): HasMany
    {
        return $this->hasMany(GamificationEvent::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function assignmentSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function discordPracticeReservations(): HasMany
    {
        return $this->hasMany(DiscordPracticeReservation::class);
    }

    public function discordPracticeRequests(): HasMany
    {
        return $this->hasMany(DiscordPracticeRequest::class);
    }

    public function practicePackageOrders(): HasMany
    {
        return $this->hasMany(PracticePackageOrder::class);
    }

    public function hasTier(string $slug): bool
    {
        return $this->activeTiers()->where('slug', $slug)->exists();
    }
}
