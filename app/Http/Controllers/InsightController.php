<?php

namespace App\Http\Controllers;

use App\Models\Insight;
use App\Models\InsightCategory;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InsightController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category');
        $author = $request->query('author');
        $search = trim((string) $request->query('q', ''));
        $featuredOnly = $request->boolean('featured');

        $query = Insight::query()
            ->with(['categoryRelation', 'authors.user'])
            ->published()
            ->when($category, fn ($query) => $query->whereHas('categoryRelation', fn ($categoryQuery) => $categoryQuery->where('slug', $category)))
            ->when($author, fn ($query) => $query->whereHas('authors', fn ($authorQuery) => $authorQuery->where('slug', $author)))
            ->when($featuredOnly, fn ($query) => $query->featured())
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            });

        $insightCategories = InsightCategory::query()
            ->where('is_active', true)
            ->withCount([
                'insights as published_insights_count' => fn ($query) => $query->published(),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $latestInsights = Insight::query()
            ->with(['categoryRelation', 'authors.user'])
            ->published()
            ->orderByDesc('published_at')
            ->latest('id')
            ->take(12)
            ->get();

        return view('insights.index', [
            'latestInsights' => $latestInsights,
            'insightChannels' => $this->insightChannels($insightCategories),
            'editorialPicks' => $this->editorialPicks($latestInsights->pluck('id')->all()),
            'popularInsights' => $this->popularInsights(),
            'popularTags' => $this->popularTags(),
            'insights' => $query
                ->orderByDesc('published_at')
                ->latest('id')
                ->paginate(9)
                ->withQueryString(),
            'insightCategories' => $insightCategories,
            'selectedCategory' => $category,
            'selectedAuthor' => $author,
            'search' => $search,
            'featuredOnly' => $featuredOnly,
            'showFilteredArchive' => $search !== '' || filled($category) || filled($author) || $featuredOnly || $request->filled('archive') || (int) $request->query('page', 1) > 1,
        ]);
    }

    public function show(string $slug): View
    {
        $insight = Insight::query()
            ->with(['categoryRelation', 'authors.user', 'tags', 'creator', 'reviewer'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedInsights = Insight::query()
            ->with(['categoryRelation', 'authors.user'])
            ->published()
            ->whereKeyNot($insight->id)
            ->when($insight->insight_category_id, fn ($query) => $query->where('insight_category_id', $insight->insight_category_id))
            ->latest('published_at')
            ->take(3)
            ->get();

        if ($relatedInsights->count() < 3) {
            $excludedIds = $relatedInsights
                ->pluck('id')
                ->push($insight->id)
                ->filter()
                ->all();

            $fallbackInsights = Insight::query()
                ->with(['categoryRelation', 'authors.user'])
                ->published()
                ->whereNotIn('id', $excludedIds)
                ->latest('published_at')
                ->take(3 - $relatedInsights->count())
                ->get();

            $relatedInsights = $relatedInsights
                ->concat($fallbackInsights)
                ->take(3)
                ->values();
        }

        return view('insights.show', [
            'insight' => $insight,
            'relatedInsights' => $relatedInsights,
        ]);
    }

    private function editorialPicks(array $excludedIds = []): Collection
    {
        $target = 5;
        $excludedIds = collect($excludedIds)->filter()->unique()->values();

        $featured = Insight::query()
            ->with(['categoryRelation', 'authors.user'])
            ->published()
            ->featured()
            ->whereNotIn('id', $excludedIds->all())
            ->orderByDesc('published_at')
            ->latest('id')
            ->take($target)
            ->get();

        if ($featured->count() >= $target) {
            return $featured;
        }

        $fallback = Insight::query()
            ->with(['categoryRelation', 'authors.user'])
            ->published()
            ->whereNotIn('id', $excludedIds->merge($featured->pluck('id'))->unique()->all())
            ->orderByDesc('published_at')
            ->latest('id')
            ->take($target - $featured->count())
            ->get();

        $picks = $featured
            ->concat($fallback)
            ->take($target)
            ->values();

        if ($picks->count() >= $target) {
            return $picks;
        }

        $secondaryFallback = Insight::query()
            ->with(['categoryRelation', 'authors.user'])
            ->published()
            ->whereNotIn('id', $picks->pluck('id')->all())
            ->orderByDesc('published_at')
            ->latest('id')
            ->take($target - $picks->count())
            ->get();

        return $picks
            ->concat($secondaryFallback)
            ->take($target)
            ->values();
    }

    private function popularInsights(): Collection
    {
        $query = Insight::query()
            ->with(['categoryRelation', 'authors.user'])
            ->published();

        $countColumn = collect(['views_count', 'view_count', 'read_count', 'reads_count'])
            ->first(fn (string $column): bool => Schema::hasColumn('insights', $column));

        if ($countColumn) {
            $query->orderByDesc($countColumn);
        } else {
            $query->orderByDesc('published_at');
        }

        return $query
            ->latest('id')
            ->take(5)
            ->get();
    }

    private function popularTags(): Collection
    {
        return Tag::query()
            ->select('tags.*')
            ->selectSub(function ($query) {
                $query
                    ->from('insight_tag')
                    ->join('insights', 'insights.id', '=', 'insight_tag.insight_id')
                    ->whereColumn('insight_tag.tag_id', 'tags.id')
                    ->where('insights.status', 'published')
                    ->where(function ($query) {
                        $query->whereNull('insights.published_at')
                            ->orWhere('insights.published_at', '<=', now());
                    })
                    ->selectRaw('count(*)');
            }, 'published_insights_count')
            ->whereExists(function ($query) {
                $query
                    ->from('insight_tag')
                    ->join('insights', 'insights.id', '=', 'insight_tag.insight_id')
                    ->whereColumn('insight_tag.tag_id', 'tags.id')
                    ->where('insights.status', 'published')
                    ->where(function ($query) {
                        $query->whereNull('insights.published_at')
                            ->orWhere('insights.published_at', '<=', now());
                    });
            })
            ->orderByDesc('published_insights_count')
            ->orderBy('name')
            ->take(12)
            ->get();
    }

    private function insightChannels(Collection $categories): Collection
    {
        $definitions = collect([
            [
                'label' => 'Law & Governance',
                'icon' => 'column',
                'description' => 'Kajian hukum tata negara, kebijakan publik, dan perkembangan regulasi.',
                'aliases' => ['law governance', 'law and governance', 'constitution governance', 'constitution and governance', 'kebijakan publik'],
            ],
            [
                'label' => 'Legal 101',
                'icon' => 'book',
                'description' => 'Dasar-dasar hukum yang penting untuk dipahami semua orang.',
                'aliases' => ['legal 101', 'law 101'],
            ],
            [
                'label' => 'Regulatory Update',
                'icon' => 'document',
                'description' => 'Update regulasi terbaru dan dampaknya terhadap masyarakat.',
                'aliases' => ['regulatory update', 'regulation update', 'regulasi', 'pembaruan regulasi'],
            ],
            [
                'label' => 'Edulaw Editorial',
                'icon' => 'spark',
                'description' => 'Analisis hukum terkini dari riset dan pengalaman tim Edulaw Project.',
                'aliases' => ['insight', 'editorial', 'legal insight', 'legal editorial', 'opini hukum', 'riset hukum'],
            ],
            [
                'label' => 'Policy & Society',
                'icon' => 'people',
                'description' => 'Irisan kebijakan publik, masyarakat, hak warga, dan tata kelola sosial.',
                'aliases' => ['policy society', 'policy and society', 'kebijakan masyarakat', 'sosial'],
            ],
            [
                'label' => 'Teknologi Hukum',
                'icon' => 'tech',
                'description' => 'Perkembangan teknologi, data, AI, dan transformasi digital dalam hukum.',
                'aliases' => ['teknologi hukum', 'law technology', 'legal tech', 'technology law'],
            ],
            [
                'label' => 'Ekonomi & Bisnis',
                'icon' => 'briefcase',
                'description' => 'Analisis hukum bisnis, ekonomi, pasar, dan regulasi dunia usaha.',
                'aliases' => ['ekonomi bisnis', 'ekonomi and bisnis', 'business law', 'ekonomi'],
            ],
            [
                'label' => 'International Law',
                'icon' => 'globe',
                'description' => 'Catatan hukum internasional, diplomasi, hak asasi, dan isu lintas negara.',
                'aliases' => ['international law', 'hukum internasional', 'internasional'],
            ],
        ]);

        $resolvedCategories = $definitions
            ->map(fn (array $definition): ?InsightCategory => $this->resolveInsightCategory($categories, $definition['aliases']))
            ->filter();

        $articlesByCategory = $resolvedCategories->isNotEmpty()
            ? Insight::query()
                ->with(['categoryRelation', 'authors.user'])
                ->published()
                ->whereIn('insight_category_id', $resolvedCategories->pluck('id')->all())
                ->orderByDesc('published_at')
                ->latest('id')
                ->get()
                ->groupBy('insight_category_id')
                ->map(fn (Collection $items): Collection => $items->take(3)->values())
            : collect();

        return $definitions->map(function (array $definition) use ($categories, $articlesByCategory): array {
            $category = $this->resolveInsightCategory($categories, $definition['aliases']);

            $articles = $category
                ? $articlesByCategory->get($category->id, collect())
                : collect();

            return [
                ...$definition,
                'category' => $category,
                'article_count' => (int) ($category?->published_insights_count ?? 0),
                'articles' => $articles,
                'url' => $category
                    ? route('insights.index', ['category' => $category->slug])
                    : route('insights.index', ['q' => $definition['label']]),
            ];
        });
    }

    private function resolveInsightCategory(Collection $categories, array $aliases): ?InsightCategory
    {
        $normalizedAliases = collect($aliases)
            ->map(fn (string $alias): string => $this->normalizeCategoryName($alias))
            ->filter()
            ->values();

        return $categories->first(function (InsightCategory $category) use ($normalizedAliases): bool {
            $name = $this->normalizeCategoryName($category->name);
            $slug = $this->normalizeCategoryName($category->slug);

            return $normalizedAliases->contains($name)
                || $normalizedAliases->contains($slug)
                || $normalizedAliases->contains(fn (string $alias): bool => Str::contains($name, $alias) || Str::contains($slug, $alias));
        });
    }

    private function normalizeCategoryName(?string $value): string
    {
        return Str::of((string) $value)
            ->lower()
            ->replace('&', ' and ')
            ->replace('-', ' ')
            ->replace('_', ' ')
            ->squish()
            ->toString();
    }
}
