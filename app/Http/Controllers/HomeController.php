<?php

namespace App\Http\Controllers;

use App\Models\Author;
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
        $featuredInsight = Insight::with(['categoryRelation', 'authors.user', 'tags'])
            ->published()
            ->featured()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->first();

        $latestInsights = Insight::with(['categoryRelation', 'authors.user', 'tags'])
            ->published()
            ->when($featuredInsight, fn ($query) => $query->whereKeyNot($featuredInsight->id))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit($featuredInsight ? 2 : 3)
            ->get();

        $featuredInsight ??= $latestInsights->first();

        if ($featuredInsight) {
            $latestInsights = $latestInsights
                ->where('id', '!=', $featuredInsight->id)
                ->take(2)
                ->values();
        }

        $topicDefinitions = collect([
            ['slug' => 'law-governance', 'name' => 'Law & Governance', 'description' => 'Tata kelola, kelembagaan, dan kebijakan publik.', 'aliases' => ['law-governance', 'law governance', 'law and governance']],
            ['slug' => 'legal-101', 'name' => 'Legal 101', 'description' => 'Memahami konsep, asas, dan istilah hukum.', 'aliases' => ['legal-101', 'legal 101', 'law 101']],
            ['slug' => 'regulatory-update', 'name' => 'Regulatory Update', 'description' => 'Perkembangan regulasi dan kebijakan terbaru.', 'aliases' => ['regulatory-update', 'regulatory update', 'regulation update']],
            ['slug' => 'edulaw-insight', 'name' => 'Edulaw Insight', 'description' => 'Analisis isu hukum dan kebijakan kontemporer.', 'aliases' => ['edulaw-insight', 'edulaw insight', 'insight', 'editorial']],
        ]);
        $topicCategories = InsightCategory::query()
            ->where('is_active', true)
            ->withCount(['insights as published_insights_count' => fn ($query) => $query->published()])
            ->get();
        $homeTopics = $topicDefinitions->map(function (array $definition) use ($topicCategories): array {
            $aliases = collect($definition['aliases'])->map(fn (string $value): string => str($value)->lower()->replace('-', ' ')->squish()->toString());
            $category = $topicCategories->first(function (InsightCategory $category) use ($aliases): bool {
                $keys = collect([$category->slug, $category->getRawOriginal('name')])
                    ->map(fn ($value): string => str((string) $value)->lower()->replace('-', ' ')->squish()->toString());

                return $keys->intersect($aliases)->isNotEmpty();
            });

            return [
                'name' => $definition['name'],
                'description' => $definition['description'],
                'url' => route('insights.categories.show', $definition['slug']),
                'count' => (int) ($category?->published_insights_count ?? 0),
            ];
        });

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
                'value' => Author::query()
                    ->publicProfile()
                    ->visibleInContributorSection()
                    ->withPublicContribution()
                    ->count(),
            ],
            [
                'label' => 'Peluang Aktif',
                'value' => Opportunity::query()->active()->withExternalLink()->count(),
            ],
        ]);

        $homeHero = EdulawSite::block('home.hero');
        $homeValues = EdulawSite::blocks('home.values');
        $sharedCta = EdulawSite::block('shared.cta');

        return view('home', compact(
            'featuredInsight',
            'latestInsights',
            'homeTopics',
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
