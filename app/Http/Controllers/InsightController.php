<?php

namespace App\Http\Controllers;

use App\Models\Insight;
use App\Models\InsightCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InsightController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category');
        $search = trim((string) $request->query('q', ''));

        $query = Insight::with(['categoryRelation', 'authors'])
            ->published()
            ->when($category, fn ($query) => $query->whereHas('categoryRelation', fn ($categoryQuery) => $categoryQuery->where('slug', $category)))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            });

        $featuredInsight = (clone $query)
            ->featured()
            ->ordered()
            ->first();

        return view('insights.index', [
            'featuredInsight' => $featuredInsight,
            'insights' => $query->ordered()->paginate(9)->withQueryString(),
            'insightCategories' => InsightCategory::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'selectedCategory' => $category,
            'search' => $search,
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
}
