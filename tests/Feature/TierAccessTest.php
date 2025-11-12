<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\StudentGroup;
use App\Models\Tier;
use App\Models\User;
use Database\Seeders\TierSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TierAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_tiers_and_groups_are_seeded(): void
    {
        $this->seed(TierSeeder::class);

        $this->assertDatabaseHas('tiers', [
            'slug' => 'free',
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('student_groups', [
            'slug' => 'vip-mentoring-circle',
        ]);

        $tier = Tier::where('slug', 'vip')->first();
        $group = StudentGroup::where('slug', 'vip-mentoring-circle')->first();

        $this->assertNotNull($tier);
        $this->assertNotNull($group);
        $this->assertTrue($tier->is($group->tier));
    }

    public function test_user_can_be_assigned_to_tier_with_metadata(): void
    {
        $tier = Tier::factory()->create(['access_type' => 'paid']);
        $user = User::factory()->create();
        $teacherAdmin = User::factory()->create();

        $user->tiers()->attach($tier->id, [
            'status' => 'active',
            'source' => 'manual',
            'assigned_by' => $teacherAdmin->id,
            'starts_at' => now(),
            'metadata' => json_encode(['note' => 'Asignado por prueba']),
        ]);

        $this->assertTrue($user->hasTier($tier->slug));

        $this->assertDatabaseHas('tier_user', [
            'tier_id' => $tier->id,
            'user_id' => $user->id,
            'assigned_by' => $teacherAdmin->id,
            'status' => 'active',
        ]);
    }

    public function test_course_can_be_associated_with_tier(): void
    {
        $tier = Tier::factory()->create();

        $course = Course::create([
            'slug' => 'creative-writing',
            'level' => 'intermediate',
            'published' => false,
        ]);

        $course->tiers()->attach($tier->id);

        $course->refresh();
        $tier->refresh();

        $this->assertTrue($course->tiers->contains($tier));
        $this->assertTrue($tier->courses->contains($course));
    }
}
