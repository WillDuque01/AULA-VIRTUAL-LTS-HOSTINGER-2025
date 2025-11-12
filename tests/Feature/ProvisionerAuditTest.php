<?php

namespace Tests\Feature;

use App\Models\IntegrationAudit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ProvisionerAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::define('manage-settings', static fn ($user = null): bool => true);
    }

    public function test_provisioner_changes_are_audited(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        putenv('GOOGLE_CLIENT_ID=old-client-id');
        $_ENV['GOOGLE_CLIENT_ID'] = 'old-client-id';

        $response = $this->post(route('provisioner.save'), [
            'GOOGLE_CLIENT_ID' => 'new-client-id',
        ]);

        $response->assertOk();

        $this->assertTrue(app()->bound('provisioner.last_audit'));
        $auditSnapshot = app('provisioner.last_audit');

        $this->assertArrayHasKey('GOOGLE_CLIENT_ID', $auditSnapshot);
        $this->assertTrue($auditSnapshot['GOOGLE_CLIENT_ID']['changed']);
        $this->assertNotEquals(
            $auditSnapshot['GOOGLE_CLIENT_ID']['old_hash'],
            $auditSnapshot['GOOGLE_CLIENT_ID']['new_hash']
        );

        $this->assertTrue(app()->bound('provisioner.debug.GOOGLE_CLIENT_ID'));
        $debug = app('provisioner.debug.GOOGLE_CLIENT_ID');
        $this->assertSame('old-client-id', $debug['previous']);
        $this->assertSame('new-client-id', $debug['new']);

        $this->assertSame(0, IntegrationAudit::count());
    }
}
