<?php

namespace App\Http\Controllers;

use App\Models\Author;
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
        $homeInsights = Insight::with(['categoryRelation', 'authors.user', 'tags'])
            ->published()
            ->orderByDesc('featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        $featuredInsight = $homeInsights->firstWhere('featured', true) ?: $homeInsights->first();

        $latestInsights = $homeInsights
            ->when($featuredInsight, fn ($collection) => $collection->where('id', '!=', $featuredInsight->id))
            ->take(3)
            ->values();

        $latestPublications = Publication::with(['type', 'authors.user', 'tags'])
            ->published()
            ->orderByDesc('featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        $latestPrograms = Program::with('categoryRelation')
            ->visible()
            ->active()
            ->orderByRaw("CASE status WHEN 'ongoing' THEN 0 WHEN 'upcoming' THEN 1 ELSE 2 END")
            ->orderByRaw('CASE WHEN event_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('event_date')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        $latestOpportunities = Opportunity::query()
            ->active()
            ->withExternalLink()
            ->orderByRaw('CASE WHEN deadline IS NULL THEN 1 ELSE 0 END')
            ->orderBy('deadline')
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        $latestMultimedia = Multimedia::query()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        $credibilityStats = collect([
            [
                'label' => 'Insight Terbit',
                'value' => Insight::query()->published()->count(),
            ],
            [
                'label' => 'Publikasi',
                'value' => Publication::query()->published()->count(),
            ],
            [
                'label' => 'Program Terlaksana',
                'value' => Program::query()->visible()->archived()->count(),
            ],
            [
                'label' => 'Kontributor Aktif',
                'value' => Author::query()->where('is_active', true)->count(),
            ],
        ])
            ->filter(fn (array $stat): bool => $stat['value'] > 0)
            ->take(4)
            ->values();

        $homeHero = EdulawSite::block('home.hero');
        $homeValues = EdulawSite::blocks('home.values');
        $sharedCta = EdulawSite::block('shared.cta');

        return view('home', compact(
            'featuredInsight',
            'latestInsights',
            'latestPublications',
            'latestPrograms',
            'latestOpportunities',
            'latestMultimedia',
            'credibilityStats',
            'homeHero',
            'homeValues',
            'sharedCta',
        ));
    }
}
