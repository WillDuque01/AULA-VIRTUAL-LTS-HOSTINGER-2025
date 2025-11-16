<?php

namespace App\Support\Integrations;

use App\Models\IntegrationEvent;
use Illuminate\Support\Carbon;

class WhatsAppMetrics
{
    public static function summary(int $days = 7): array
    {
        $today = IntegrationEvent::where('event', 'whatsapp.cta_clicked')
            ->where('created_at', '>=', Carbon::today())
            ->count();

        $week = IntegrationEvent::where('event', 'whatsapp.cta_clicked')
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->count();

        $trend = IntegrationEvent::selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->where('event', 'whatsapp.cta_clicked')
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn ($row) => [
                'day' => Carbon::parse($row->day)->format('d M'),
                'total' => (int) $row->total,
            ]);

        $contexts = IntegrationEvent::where('event', 'whatsapp.cta_clicked')
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->get()
            ->groupBy(fn ($event) => data_get($event->payload, 'context', 'global'))
            ->map->count()
            ->sortDesc()
            ->take(5)
            ->map(fn ($count, $context) => [
                'context' => $context,
                'count' => $count,
            ])
            ->values();

        return [
            'today' => $today,
            'week' => $week,
            'trend' => $trend,
            'contexts' => $contexts,
        ];
    }
}


