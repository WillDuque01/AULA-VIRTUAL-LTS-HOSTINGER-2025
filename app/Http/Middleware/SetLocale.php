<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\URL;

class SetLocale
{
    protected array $available;

    public function handle(Request $request, Closure $next)
    {
        $this->available = config('app.available_locales', ['es', 'en']);
        $locale = $request->route('locale');

        if (! $locale) {
            $locale = $request->segment(1);
        }

        if (! in_array($locale, $this->available, true)) {
            $locale = Cookie::get('locale', config('app.locale', 'es'));
        }

        if (! in_array($locale, $this->available, true)) {
            $locale = 'es';
        }

        App::setLocale($locale);
        URL::defaults(['locale' => $locale]);
        if ($request->route()) {
            $request->route()->setParameter('locale', $locale);
        }
        view()->share('currentLocale', $locale);
        Cookie::queue('locale', $locale, 60 * 24 * 30);

        return $next($request);
    }
}

