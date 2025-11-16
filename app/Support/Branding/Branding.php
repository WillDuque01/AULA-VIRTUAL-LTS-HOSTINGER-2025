<?php

namespace App\Support\Branding;

use App\Models\SetupState;
use App\Settings\BrandingSettings;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Branding
{
    public static function info(): array
    {
        return Cache::remember('branding.info', now()->addMinutes(10), function () {
            $state = SetupState::query()->latest()->first();
            $data = $state?->data ?? [];
            $brandingSettings = app(BrandingSettings::class);

            $logoUrl = Arr::get($data, 'logo_url');

            if (!$logoUrl && filled($brandingSettings->logo_horizontal_path ?? '')) {
                $logoUrl = Storage::disk('public')->url($brandingSettings->logo_horizontal_path);
            }

            if (!$logoUrl && filled($brandingSettings->logo_url ?? '')) {
                $logoUrl = $brandingSettings->logo_url;
            }

            return [
                'name' => Arr::get($data, 'project_name', config('app.name')),
                'logo_url' => $logoUrl ?: url('/images/logo.png'),
                'support_email' => Arr::get($data, 'support_email', config('mail.from.address')),
                'support_phone' => Arr::get($data, 'support_phone'),
                'support_address' => Arr::get($data, 'support_address'),
                'website' => Arr::get($data, 'website', config('app.url')),
            ];
        });
    }
}
