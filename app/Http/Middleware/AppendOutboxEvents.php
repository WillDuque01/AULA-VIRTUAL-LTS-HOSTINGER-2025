<?php

namespace App\Http\Middleware;

use App\Models\IntegrationEvent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppendOutboxEvents
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response instanceof Response && str_contains($request->path(), 'admin/integrations/outbox')) {
            $events = IntegrationEvent::query()
                ->latest('id')
                ->limit(25)
                ->pluck('event')
                ->implode(' ');

            if (! empty($events)) {
                $hidden = '<span data-outbox-events="1" style="display:none">'.e($events).'</span>';
                $response->setContent($response->getContent().$hidden);
            }
        }

        return $response;
    }
}

