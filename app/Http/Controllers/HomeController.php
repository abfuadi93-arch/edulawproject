<?php

namespace App\Http\Controllers;

use App\Models\Insight;
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
        $featuredInsight = Insight::with(['categoryRelation', 'authors'])
            ->published()
            ->featured()
            ->ordered()
            ->first();

        $latestInsights = Insight::with(['categoryRelation', 'authors'])
            ->published()
            ->ordered()
            ->limit(3)
            ->get();

        $latestPublications = Publication::with(['type', 'authors'])
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->latest()
            ->limit(4)
            ->get();

        $latestPrograms = Program::with('categoryRelation')
            ->active()
            ->ordered()
            ->limit(3)
            ->get();

        $featuredMultimedia = Multimedia::query()
            ->published()
            ->featured()
            ->orderByDesc('published_at')
            ->latest()
            ->first();

        $latestMultimedia = Multimedia::query()
            ->published()
            ->orderByDesc('published_at')
            ->latest()
            ->limit(3)
            ->get();

        $latestOpportunities = Opportunity::query()
            ->whereIn('status', ['open', 'closed'])
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
