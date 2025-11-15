<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_security_headers_are_applied(): void
    {
        config([
            'security.enabled' => true,
            'security.csp.enabled' => true,
            'security.hsts.enabled' => true,
        ]);

        $response = $this->get($this->localized());

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->assertHeader('Content-Security-Policy');
        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_security_headers_can_be_disabled(): void
    {
        config(['security.enabled' => false]);

        $response = $this->get($this->localized());

        $response->assertHeaderMissing('X-Frame-Options');
        $response->assertHeaderMissing('Content-Security-Policy');
        $response->assertHeaderMissing('Strict-Transport-Security');
    }
}
