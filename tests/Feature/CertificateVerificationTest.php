<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_verification_shows_certificate(): void
    {
        $user = User::factory()->create();
        $course = Course::create([
            'slug' => 'c1',
            'level' => 'advanced',
            'published' => true,
        ]);

        $certificate = Certificate::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'code' => 'ABC123XYZ',
            'file_path' => 'certificates/demo.pdf',
            'issued_at' => now()->subDay(),
        ]);

        $response = $this->get(route('certificates.verify', ['code' => $certificate->code]));

        $response->assertOk();
        $response->assertSee($certificate->user->name);
        $this->assertDatabaseHas('certificates', [
            'id' => $certificate->id,
            'verified_count' => 1,
        ]);
    }

    public function test_verification_page_handles_unknown_code(): void
    {
        $this->get(route('certificates.verify', ['code' => 'unknown-code']))
            ->assertOk()
            ->assertSee('No encontramos un certificado');
    }
}


