<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        if (! config('security.enabled', false)) {
            return $next($request);
        }

        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        if (! $response instanceof Response || $response instanceof BinaryFileResponse) {
            return $response;
        }

        $response->headers->set('X-Frame-Options', config('security.frame_options', 'SAMEORIGIN'), false);
        $response->headers->set('X-Content-Type-Options', 'nosniff', false);
        $response->headers->set('Referrer-Policy', config('security.referrer_policy', 'strict-origin-when-cross-origin'), false);
        $response->headers->set('Permissions-Policy', config('security.permissions_policy', 'camera=(), microphone=(), geolocation=(), payment=()'), false);

        if (config('security.csp.enabled', true) && ! $response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', trim(config('security.csp.value')), false);
        }

        $shouldApplyHsts = $request->isSecure() || app()->environment('testing');

        if (config('security.hsts.enabled', true) && $shouldApplyHsts) {
            $hsts = 'max-age='.(int) config('security.hsts.max_age', 31536000);

            if (config('security.hsts.include_subdomains', true)) {
                $hsts .= '; includeSubDomains';
            }

            if (config('security.hsts.preload', false)) {
                $hsts .= '; preload';
            }

            $response->headers->set('Strict-Transport-Security', $hsts, false);
        }

        return $response;
    }
}
