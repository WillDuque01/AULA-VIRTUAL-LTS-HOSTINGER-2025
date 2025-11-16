<?php

namespace App\Http\Controllers;

use App\Models\Page;

class PageController extends Controller
{
    public function show(string $locale, string $slug)
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->where('locale', $locale)
            ->published()
            ->with('publishedRevision')
            ->firstOrFail();

        $blocks = $page->publishedRevision?->layout ?? [];

        return view('page.show', compact('page', 'blocks'));
    }
}


