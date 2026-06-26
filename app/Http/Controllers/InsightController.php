<?php

namespace App\Http\Controllers;

use App\Models\Insight;
use App\Models\InsightCategory;
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
        $search = trim((string) $request->query('q', ''));
        $featuredOnly = $request->boolean('featured');

        $query = Insight::query()
            ->with(['categoryRelation', 'authors'])
            ->published()
            ->when($category, fn ($query) => $query->whereHas('categoryRelation', fn ($categoryQuery) => $categoryQuery->where('slug', $category)))
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
            ->with(['categoryRelation', 'authors'])
            ->published()
            ->orderByDesc('published_at')
            ->latest('id')
            ->take(4)
            ->get();

        return view('insights.index', [
            'latestInsights' => $latestInsights,
            'insightChannels' => $this->insightChannels($insightCategories),
            'editorialPicks' => $this->editorialPicks($latestInsights->pluck('id')->all()),
            'popularInsights' => $this->popularInsights(),
            'insights' => $query
                ->orderByDesc('published_at')
                ->latest('id')
                ->paginate(9)
                ->withQueryString(),
            'insightCategories' => $insightCategories,
            'selectedCategory' => $category,
            'search' => $search,
            'featuredOnly' => $featuredOnly,
            'showFilteredArchive' => $search !== '' || filled($category) || $featuredOnly || $request->filled('archive') || (int) $request->query('page', 1) > 1,
        ]);
    }

    public function show(string $slug): View
    {
        $insight = Insight::query()
            ->with(['categoryRelation', 'authors', 'tags', 'creator', 'reviewer'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedInsights = Insight::query()
            ->with(['categoryRelation', 'authors'])
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
                ->with(['categoryRelation', 'authors'])
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
        $excludedIds = collect($excludedIds)->filter()->unique()->values();

        $featured = Insight::query()
            ->with(['categoryRelation', 'authors'])
            ->published()
            ->featured()
            ->whereNotIn('id', $excludedIds->all())
            ->orderByDesc('published_at')
            ->latest('id')
            ->take(3)
            ->get();

        if ($featured->count() >= 3) {
            return $featured;
        }

        $fallback = Insight::query()
            ->with(['categoryRelation', 'authors'])
            ->published()
            ->whereNotIn('id', $excludedIds->merge($featured->pluck('id'))->unique()->all())
            ->orderByDesc('published_at')
            ->latest('id')
            ->take(3 - $featured->count())
            ->get();

        $picks = $featured
            ->concat($fallback)
            ->take(3)
            ->values();

        if ($picks->count() >= 3) {
            return $picks;
        }

        $secondaryFallback = Insight::query()
            ->with(['categoryRelation', 'authors'])
            ->published()
            ->whereNotIn('id', $picks->pluck('id')->all())
            ->orderByDesc('published_at')
            ->latest('id')
            ->take(3 - $picks->count())
            ->get();

        return $picks
            ->concat($secondaryFallback)
            ->take(3)
            ->values();
    }

    private function popularInsights(): Collection
    {
        $query = Insight::query()
            ->with(['categoryRelation', 'authors'])
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
            ->take(3)
            ->get();
    }

    private function insightChannels(Collection $categories): Collection
    {
        $definitions = collect([
            [
                'label' => 'Edulaw Insight',
                'icon' => 'spark',
                'aliases' => ['insight', 'legal insight', 'opini hukum', 'riset hukum'],
            ],
            [
                'label' => 'Legal 101',
                'icon' => 'book',
                'aliases' => ['legal 101', 'law 101'],
            ],
            [
                'label' => 'Law & Governance',
                'icon' => 'column',
                'aliases' => ['law governance', 'law and governance', 'constitution governance', 'constitution and governance', 'kebijakan publik'],
            ],
            [
                'label' => 'Regulatory Update',
                'icon' => 'document',
                'aliases' => ['regulatory update', 'regulation update', 'regulasi', 'pembaruan regulasi'],
            ],
        ]);

        return $definitions->map(function (array $definition) use ($categories): array {
            $category = $this->resolveInsightCategory($categories, $definition['aliases']);

            $articles = $category
                ? Insight::query()
                    ->with(['categoryRelation', 'authors'])
                    ->published()
                    ->where('insight_category_id', $category->id)
                    ->orderByDesc('published_at')
                    ->latest('id')
                    ->take(3)
                    ->get()
                : collect();

            return [
                ...$definition,
                'category' => $category,
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
