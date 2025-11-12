<?php

namespace Tests\Feature;

use App\Models\IntegrationAudit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CredentialsAuditCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_warns_when_no_audits_exist(): void
    {
        $this->artisan('credentials:audit --limit=5')
            ->expectsOutput('No hay auditorías registradas.')
            ->assertSuccessful();
    }

    public function test_it_displays_recent_audits(): void
    {
        Carbon::setTestNow('2025-11-12 12:00:00');

        $user = User::factory()->create(['email' => 'admin@example.com']);

        IntegrationAudit::create([
            'user_id' => $user->id,
            'changes' => [
                'GOOGLE_CLIENT_ID' => [
                    'old_hash' => 'aaa',
                    'new_hash' => 'bbb',
                    'changed' => true,
                ],
                'AWS_BUCKET' => [
                    'old_hash' => 'ccc',
                    'new_hash' => 'ccc',
                    'changed' => false,
                ],
            ],
            'ip_address' => '10.0.0.1',
            'user_agent' => 'phpunit',
        ]);

        $this->artisan('credentials:audit --limit=1')
            ->expectsTable(
                ['Fecha', 'Usuario', 'IP', 'Claves modificadas'],
                [[
                    '2025-11-12 12:00:00',
                    'admin@example.com',
                    '10.0.0.1',
                    'GOOGLE_CLIENT_ID',
                ]]
            )
            ->assertSuccessful();

        Carbon::setTestNow();
    }
}
