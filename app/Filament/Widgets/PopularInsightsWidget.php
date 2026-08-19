<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Insights\InsightResource;
use App\Models\Insight;
use App\Models\PageVisit;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PopularInsightsWidget extends Widget
{
    protected string $view = 'filament.widgets.popular-insights';

    protected int|string|array $columnSpan = [
        'md' => 6,
        'xl' => 12,
    ];

    protected static ?int $sort = 20;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return auth()->user()?->can('view insights') ?? false;
    }

    public static function items(): Collection
    {
        $visitsBySlug = PageVisit::query()
            ->selectRaw('path, COUNT(*) as visit_count')
            ->where('route_name', 'insights.show')
            ->where('status_code', 200)
            ->since(now()->subDays(30)->startOfDay())
            ->groupBy('path')
            ->orderByDesc('visit_count')
            ->limit(25)
            ->get()
            ->mapWithKeys(fn (PageVisit $visit): array => [
                Str::afterLast(rawurldecode(rtrim($visit->path, '/')), '/') => (int) $visit->visit_count,
            ]);

        if ($visitsBySlug->isEmpty()) {
            return collect();
        }

        $insights = Insight::query()
            ->with('category')
            ->published()
            ->whereIn('slug', $visitsBySlug->keys()->all())
            ->get()
            ->keyBy('slug');

        return $visitsBySlug
            ->map(function (int $visitCount, string $slug) use ($insights): ?array {
                $insight = $insights->get($slug);

                if (! $insight) {
                    return null;
                }

                return [
                    'title' => $insight->title ?: 'Editorial tanpa judul',
                    'category' => $insight->category?->name ?: 'Tanpa kategori',
                    'views' => $visitCount,
                    'viewsLabel' => number_format($visitCount, 0, ',', '.'),
                    'published' => $insight->published_at?->format('d M Y') ?: '-',
                    'editUrl' => InsightResource::getUrl('edit', ['record' => $insight]),
                    'publicUrl' => filled($insight->slug) ? route('insights.show', $insight->slug) : null,
                ];
            })
            ->filter()
            ->take(5)
            ->values();
    }

    protected function getViewData(): array
    {
        return [
            'items' => static::items(),
            'periodLabel' => '30 hari terakhir',
        ];
    }
}
