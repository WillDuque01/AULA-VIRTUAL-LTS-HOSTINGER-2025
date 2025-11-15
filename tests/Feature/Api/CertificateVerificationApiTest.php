<?php

namespace Tests\Feature\Api;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class CertificateVerificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_requires_code(): void
    {
        $response = $this->getJson(route('api.certificates.verify'));
        $response->assertStatus(422)->assertJson(['valid' => false]);
    }

    public function test_rejects_invalid_signature(): void
    {
        Config::set('services.certificates.verify_secret', 'secret');

        $response = $this->getJson(route('api.certificates.verify', ['code' => 'abc']), [
            'X-Verify-Signature' => 'invalid',
        ]);

        $response->assertStatus(401)->assertJson(['valid' => false]);
    }

    public function test_returns_certificate_payload(): void
    {
        Config::set('services.certificates.verify_secret', 'secret');

        $user = User::factory()->create(['name' => 'Ana', 'email' => 'ana@example.com']);
        $course = Course::create(['slug' => 'c1', 'level' => 'a2', 'published' => true]);

        $certificate = Certificate::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'code' => 'CODE123',
            'file_path' => 'certificates/demo.pdf',
            'issued_at' => now()->subDay(),
        ]);

        $signature = hash_hmac('sha256', $certificate->code, 'secret');

        $response = $this->getJson(route('api.certificates.verify', ['code' => $certificate->code]), [
            'X-Verify-Signature' => $signature,
        ]);

        $response->assertOk()
            ->assertJson([
                'valid' => true,
                'certificate' => [
                    'code' => 'CODE123',
                    'student' => ['name' => 'Ana'],
                ],
            ]);

        $this->assertDatabaseHas('certificates', [
            'id' => $certificate->id,
            'verified_count' => 1,
        ]);

        $this->assertDatabaseHas('certificate_verification_logs', [
            'certificate_id' => $certificate->id,
            'source' => 'api',
        ]);
    }
}


