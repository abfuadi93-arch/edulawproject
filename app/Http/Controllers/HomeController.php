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
            ->limit(4)
            ->get();

        $today = now()->toDateString();

        $latestPrograms = Program::with('categoryRelation')
            ->visible()
            ->active()
            ->orderByDesc('featured')
            ->orderByRaw(
                'CASE WHEN event_date <= ? AND COALESCE(end_date, event_date) >= ? THEN 0 ELSE 1 END',
                [$today, $today],
            )
            ->orderByRaw('CASE WHEN event_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('event_date')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        if ($latestPrograms->isEmpty()) {
            $latestPrograms = Program::with('categoryRelation')
                ->visible()
                ->archived()
                ->orderByRaw('CASE WHEN COALESCE(end_date, event_date) IS NULL THEN 1 ELSE 0 END')
                ->orderByRaw('COALESCE(end_date, event_date) DESC')
                ->orderByDesc('id')
                ->limit(3)
                ->get();
        }

        $latestOpportunities = Opportunity::query()
            ->active()
            ->withExternalLink()
            ->orderByRaw('CASE WHEN deadline IS NULL THEN 1 ELSE 0 END')
            ->orderBy('deadline')
            ->orderByDesc('featured')
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        $homepageYoutubeVideos = Multimedia::query()
            ->published()
            ->youtubeVideos()
            ->whereNotNull('media_url')
            ->where('media_url', '!=', '')
            ->orderByDesc('featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $homepageFeaturedMultimedia = $homepageYoutubeVideos->first();
        $homepageVideoFallbacks = $homepageYoutubeVideos
            ->when($homepageFeaturedMultimedia, fn ($items) => $items->where('id', '!=', $homepageFeaturedMultimedia->id))
            ->values();

        $homepageShort = Multimedia::query()
            ->published()
            ->shortsReels()
            ->whereNotNull('media_url')
            ->where('media_url', '!=', '')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->first();

        $homepageAlbum = Multimedia::query()
            ->published()
            ->photoAlbums()
            ->whereNotNull('media_url')
            ->where('media_url', '!=', '')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->first();

        $homepageSecondaryMultimedia = collect([
            $homepageVideoFallbacks->shift(),
            $homepageShort,
            $homepageAlbum,
        ])
            ->filter()
            ->concat($homepageVideoFallbacks)
            ->unique('id')
            ->take(3)
            ->values();

        $credibilityStats = collect([
            [
                'label' => 'Insight Terbit',
                'value' => Insight::query()->published()->count(),
            ],
            [
                'label' => 'Program Edulaw',
                'value' => Program::query()->visible()->count(),
            ],
            [
                'label' => 'Riset & Publikasi',
                'value' => Publication::query()->published()->count(),
            ],
            [
                'label' => 'Konten Multimedia',
                'value' => Multimedia::query()->published()->count(),
            ],
            [
                'label' => 'Kontributor Aktif',
                'value' => Author::query()->where('is_active', true)->count(),
            ],
            [
                'label' => 'Peluang Aktif',
                'value' => Opportunity::query()->active()->count(),
            ],
        ]);

        $homeHero = EdulawSite::block('home.hero');
        $homeValues = EdulawSite::blocks('home.values');
        $sharedCta = EdulawSite::block('shared.cta');

        return view('home', compact(
            'featuredInsight',
            'latestInsights',
            'latestPublications',
            'latestPrograms',
            'latestOpportunities',
            'homepageFeaturedMultimedia',
            'homepageSecondaryMultimedia',
            'credibilityStats',
            'homeHero',
            'homeValues',
            'sharedCta',
        ));
    }
}
