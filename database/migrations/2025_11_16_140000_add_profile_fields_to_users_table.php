<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone')->nullable()->after('email');
            $table->string('country')->nullable()->after('phone');
            $table->string('state')->nullable()->after('country');
            $table->string('city')->nullable()->after('state');
            $table->string('headline')->nullable()->after('city');
            $table->text('bio')->nullable()->after('headline');
            $table->string('teaching_since', 10)->nullable()->after('bio');
            $table->json('specialties')->nullable()->after('teaching_since');
            $table->json('languages')->nullable()->after('specialties');
            $table->json('certifications')->nullable()->after('languages');
            $table->string('linkedin_url')->nullable()->after('certifications');
            $table->text('teacher_notes')->nullable()->after('linkedin_url');
            $table->timestamp('profile_completed_at')->nullable()->after('remember_token');
            $table->unsignedSmallInteger('profile_completion_score')->default(0)->after('profile_completed_at');
            $table->json('profile_meta')->nullable()->after('profile_completion_score');
            $table->timestamp('profile_last_reminded_at')->nullable()->after('profile_meta');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'first_name',
                'last_name',
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
                'profile_completed_at',
                'profile_completion_score',
                'profile_meta',
                'profile_last_reminded_at',
            ]);
        });
    }
};

