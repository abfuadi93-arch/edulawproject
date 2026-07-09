<?php

namespace App\Http\Controllers;

use App\Models\Insight;
use App\Models\InsightCategory;
use App\Models\Multimedia;
use App\Models\Opportunity;
use App\Models\Program;
use App\Models\Publication;
use App\Support\EdulawSite;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $homeInsights = Insight::with(['categoryRelation', 'authors.user', 'tags'])
            ->published()
            ->orderByDesc('featured')
            ->ordered()
            ->limit(4)
            ->get();

        $featuredInsight = $homeInsights->firstWhere('featured', true) ?: $homeInsights->first();

        $latestInsights = $homeInsights
            ->when($featuredInsight, fn ($collection) => $collection->where('id', '!=', $featuredInsight->id))
            ->take(3)
            ->values();

        $insightCategories = InsightCategory::query()
            ->where('is_active', true)
            ->whereHas('insights', fn ($query) => $query->published())
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(5)
            ->get(['name', 'slug']);

        $latestPublications = Publication::with(['type', 'authors.user', 'tags'])
            ->published()
            ->orderByDesc('featured')
            ->orderByDesc('published_at')
            ->latest()
            ->limit(4)
            ->get();

        $latestPrograms = Program::with('categoryRelation')
            ->visible()
            ->active()
            ->orderByDesc('featured')
            ->orderBy('sort_order')
            ->orderByRaw('CASE WHEN event_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('event_date')
            ->latest()
            ->limit(3)
            ->get();

        $homeMultimedia = Multimedia::query()
            ->published()
            ->orderByDesc('featured')
            ->orderByDesc('published_at')
            ->latest()
            ->limit(4)
            ->get();

        $featuredMultimedia = $homeMultimedia->firstWhere('featured', true) ?: $homeMultimedia->first();

        $latestMultimedia = $homeMultimedia
            ->when($featuredMultimedia, fn ($collection) => $collection->where('id', '!=', $featuredMultimedia->id))
            ->take(3)
            ->values();

        $latestOpportunities = Opportunity::query()
            ->open()
            ->where(function ($query) {
                $query->whereNull('deadline')
                    ->orWhereDate('deadline', '>=', now()->toDateString());
            })
            ->orderByDesc('featured')
            ->orderByRaw('CASE WHEN deadline IS NULL THEN 1 ELSE 0 END')
            ->orderBy('deadline')
            ->latest()
            ->limit(3)
            ->get();

        $homeHero = EdulawSite::block('home.hero');
        $homeValues = EdulawSite::blocks('home.values');
        $homeAudienceIntro = EdulawSite::block('home.audience_intro');
        $homeAudiences = EdulawSite::blocks('home.audience');
        $sharedCta = EdulawSite::block('shared.cta');

        return view('home', compact(
            'featuredInsight',
            'latestInsights',
            'insightCategories',
            'latestPublications',
            'latestPrograms',
            'featuredMultimedia',
            'latestMultimedia',
            'latestOpportunities',
            'homeHero',
            'homeValues',
            'homeAudienceIntro',
            'homeAudiences',
            'sharedCta',
        ));
    }
}
