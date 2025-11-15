<?php

namespace App\Support\Branding;

use App\Models\SetupState;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class Branding
{
    public static function info(): array
    {
        return Cache::remember('branding.info', now()->addMinutes(10), function () {
            $state = SetupState::query()->latest()->first();
            $data = $state?->data ?? [];

            return [
                'name' => Arr::get($data, 'project_name', config('app.name')),
                'logo_url' => Arr::get($data, 'logo_url', url('/images/logo.png')),
                'support_email' => Arr::get($data, 'support_email', config('mail.from.address')),
                'support_phone' => Arr::get($data, 'support_phone'),
                'support_address' => Arr::get($data, 'support_address'),
                'website' => Arr::get($data, 'website', config('app.url')),
            ];
        });
    }
}
