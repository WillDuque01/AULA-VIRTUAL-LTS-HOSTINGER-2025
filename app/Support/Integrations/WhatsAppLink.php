<?php

namespace App\Support\Integrations;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class WhatsAppLink
{
    public static function isAvailable(): bool
    {
        $config = config('services.whatsapp', []);

        return (bool) Arr::get($config, 'enabled')
            || filled(Arr::get($config, 'deeplink'))
            || filled(Arr::get($config, 'default_to'));
    }

    public static function assignmentSummary(array $summary, ?string $course = null): ?string
    {
        if (! self::isAvailable()) {
            return null;
        }

        $message = __('whatsapp.assignment.summary', [
            'course' => $course ?? config('app.name'),
            'pending' => $summary['pending'] ?? 0,
            'submitted' => $summary['submitted'] ?? 0,
            'approved' => $summary['approved'] ?? 0,
            'rejected' => $summary['rejected'] ?? 0,
        ]);

        return self::buildLink($message);
    }

    public static function assignment(array $context): ?string
    {
        if (! self::isAvailable()) {
            return null;
        }

        $message = __('whatsapp.assignment.help', [
            'title' => $context['title'] ?? 'Tarea',
            'status' => $context['status'] ?? 'pending',
        ]);

        return self::buildLink($message);
    }

    private static function buildLink(string $message): ?string
    {
        $config = config('services.whatsapp', []);
        $deeplink = Arr::get($config, 'deeplink');

        if ($deeplink) {
            return Str::contains($deeplink, 'text=')
                ? $deeplink
                : sprintf('%s?text=%s', rtrim($deeplink, '?'), urlencode($message));
        }

        $phone = Arr::get($config, 'default_to');

        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (! $digits) {
            return null;
        }

        return sprintf('https://wa.me/%s?text=%s', $digits, urlencode($message));
    }
}


