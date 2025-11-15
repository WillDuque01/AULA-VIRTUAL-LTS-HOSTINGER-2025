<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $locales = ['es', 'en'];
        $routes = ['welcome', 'dashboard', 'catalog'];

        $entries = [];
        foreach ($locales as $locale) {
            foreach ($routes as $routeName) {
                if (! Route::has($routeName)) {
                    continue;
                }
                $entries[] = [
                    'loc' => route($routeName, ['locale' => $locale], true),
                    'lang' => $locale,
                ];
            }
        }

        $xml = view('seo.sitemap', ['entries' => $entries])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}

