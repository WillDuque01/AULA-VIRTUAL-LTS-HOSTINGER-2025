<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageView;
use Illuminate\Support\Facades\Schema;

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
        $settings = $page->publishedRevision?->settings ?? [];

        session(['landing_ref' => $page->slug]);

        if (Schema::hasTable('page_views')) {
            PageView::create([
                'page_id' => $page->id,
                'session_id' => session()->getId(),
                'referer' => request()->headers->get('referer'),
                'user_agent' => request()->userAgent(),
            ]);
        }

        return view('page.show', compact('page', 'blocks', 'settings'));
    }
}


