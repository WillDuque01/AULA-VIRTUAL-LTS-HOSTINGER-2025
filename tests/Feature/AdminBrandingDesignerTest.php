<?php

namespace Tests\Feature;

use App\Livewire\Admin\BrandingDesigner;
use App\Settings\BrandingSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminBrandingDesignerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_logo_variants(): void
    {
        Storage::fake('public');

        Livewire::actingAs(\App\Models\User::factory()->create())
            ->test(BrandingDesigner::class)
            ->set('logo_mode', 'image')
            ->set('logoHorizontalUpload', UploadedFile::fake()->create('logo-h.svg', 12, 'image/svg+xml'))
            ->set('logoSquareUpload', UploadedFile::fake()->create('logo-s.svg', 8, 'image/svg+xml'))
            ->call('save')
            ->assertHasNoErrors();

        $settings = app(BrandingSettings::class);

        $this->assertNotEmpty($settings->logo_horizontal_path);
        $this->assertNotEmpty($settings->logo_square_path);
        Storage::disk('public')->assertExists($settings->logo_horizontal_path);
        Storage::disk('public')->assertExists($settings->logo_square_path);
    }

    public function test_branding_info_prefers_uploaded_logo(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('branding/demo.svg', '<svg></svg>');

        $settings = app(BrandingSettings::class);
        $settings->logo_horizontal_path = 'branding/demo.svg';
        $settings->logo_mode = 'image';
        $settings->save();

        Cache::forget('branding.info');

        $info = \App\Support\Branding\Branding::info();

        $this->assertSame(Storage::disk('public')->url('branding/demo.svg'), $info['logo_url']);
    }
}


