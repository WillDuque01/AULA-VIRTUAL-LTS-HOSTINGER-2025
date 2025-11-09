<?php

namespace Tests\Feature;

use App\Support\Integrations\IntegrationConfigurator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IntegrationStatusTest extends TestCase
{
    protected function tearDown(): void
    {
        config()->set('integrations.force_free_storage', false);
        config()->set('integrations.force_free_realtime', false);
        config()->set('integrations.force_youtube_only', false);

        config()->set('services', []);

        IntegrationConfigurator::apply();

        parent::tearDown();
    }

    public function test_storage_driver_switches_to_s3_when_credentials_exist(): void
    {
        config([
            'integrations.force_free_storage' => false,
            'services.s3.key' => 'key',
            'services.s3.secret' => 'secret',
            'services.s3.bucket' => 'bucket',
        ]);

        IntegrationConfigurator::apply();

        $this->assertSame('s3', config('filesystems.default'));
    }

    public function test_storage_driver_stays_public_when_forced(): void
    {
        config([
            'integrations.force_free_storage' => true,
            'services.s3.key' => 'key',
            'services.s3.secret' => 'secret',
            'services.s3.bucket' => 'bucket',
        ]);

        IntegrationConfigurator::apply();

        $this->assertSame('public', config('filesystems.default'));
    }

    public function test_credentials_check_outputs_status_block(): void
    {
        config([
            'integrations.force_free_storage' => false,
            'services.s3.key' => null,
            'services.s3.secret' => null,
            'services.s3.bucket' => null,
        ]);

        IntegrationConfigurator::apply();

        $this->artisan('credentials:check', ['--no-env' => true])
            ->expectsOutput('=== Estado de integraciones ===')
            ->expectsOutputToContain('Almacenamiento');
    }

    public function test_storage_migrate_copies_files_between_disks(): void
    {
        Storage::fake('from_disk');
        Storage::fake('to_disk');

        Storage::disk('from_disk')->put('foo.txt', 'contenido');
        Storage::disk('from_disk')->put('nested/bar.txt', 'otro');

        Artisan::call('storage:migrate', [
            'from' => 'from_disk',
            'to' => 'to_disk',
        ]);

        Storage::disk('to_disk')->assertExists('foo.txt');
        Storage::disk('to_disk')->assertExists('nested/bar.txt');
        Storage::disk('from_disk')->assertExists('foo.txt');
    }

    public function test_storage_migrate_dry_run_does_not_copy_files(): void
    {
        Storage::fake('dry_from');
        Storage::fake('dry_to');

        Storage::disk('dry_from')->put('dummy.txt', 'contenido');

        Artisan::call('storage:migrate', [
            'from' => 'dry_from',
            'to' => 'dry_to',
            '--dry-run' => true,
        ]);

        Storage::disk('dry_to')->assertMissing('dummy.txt');
        Storage::disk('dry_from')->assertExists('dummy.txt');
    }
}
