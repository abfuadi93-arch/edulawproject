<?php

namespace App\Filament\Widgets;

use App\Models\PageVisit;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TrafficOverviewWidget extends Widget
{
    protected string $view = 'filament.widgets.traffic-overview';

    protected int|string|array $columnSpan = [
        'md' => 6,
        'xl' => 6,
    ];

    protected static ?int $sort = 20;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return auth()->check();
    }

    protected function getViewData(): array
    {
        $today = today();
        $lastSevenDays = now()->subDays(6)->startOfDay();
        $lastThirtyDays = now()->subDays(29)->startOfDay();

        $todayVisits = PageVisit::query()
            ->whereDate('visited_at', $today)
            ->count();
        $todayVisitors = PageVisit::query()
            ->whereDate('visited_at', $today)
            ->distinct()
            ->count('visitor_id');
        $sevenDayVisitors = PageVisit::query()
            ->since($lastSevenDays)
            ->distinct()
            ->count('visitor_id');
        $thirtyDayVisitors = PageVisit::query()
            ->since($lastThirtyDays)
            ->distinct()
            ->count('visitor_id');

        return [
            'stats' => [
                [
                    'label' => "Today's pageviews",
                    'value' => number_format($todayVisits, 0, ',', '.'),
                    'icon' => 'heroicon-o-calendar-days',
                    'tone' => 'primary',
                ],
                [
                    'label' => "Today's visitors",
                    'value' => number_format($todayVisitors, 0, ',', '.'),
                    'icon' => 'heroicon-o-user',
                    'tone' => 'success',
                ],
                [
                    'label' => '7-day visitors',
                    'value' => number_format($sevenDayVisitors, 0, ',', '.'),
                    'icon' => 'heroicon-o-chart-bar',
                    'tone' => 'warning',
                ],
                [
                    'label' => '30-day visitors',
                    'value' => number_format($thirtyDayVisitors, 0, ',', '.'),
                    'icon' => 'heroicon-o-arrow-trending-up',
                    'tone' => 'warning',
                ],
            ],
            'dailySeries' => $this->dailySeries($lastSevenDays),
            'topPages' => $this->topPages($lastThirtyDays),
        ];
    }

    private function dailySeries(Carbon $startDate): array
    {
        $rawCounts = PageVisit::query()
            ->selectRaw('DATE(visited_at) as visit_date, COUNT(*) as views')
            ->where('visited_at', '>=', $startDate)
            ->groupBy('visit_date')
            ->pluck('views', 'visit_date');

        $days = collect(range(0, 6))->map(function (int $offset) use ($startDate, $rawCounts): array {
            $date = $startDate->copy()->addDays($offset);
            $views = (int) ($rawCounts[$date->toDateString()] ?? 0);

            return [
                'label' => $date->format('d M'),
                'views' => $views,
                'date' => $date->toDateString(),
            ];
        });

        $maxViews = max(1, $days->max('views'));

        return $days
            ->map(fn (array $day): array => $day + [
                'height' => max(8, (int) round(($day['views'] / $maxViews) * 100)),
            ])
            ->all();
    }

    private function topPages(Carbon $startDate): array
    {
        return PageVisit::query()
            ->select([
                'path',
                'route_name',
                DB::raw('COUNT(*) as views'),
                DB::raw('COUNT(DISTINCT visitor_id) as visitors'),
            ])
            ->where('visited_at', '>=', $startDate)
            ->groupBy('path', 'route_name')
            ->orderByDesc('views')
            ->limit(5)
            ->get()
            ->map(fn (PageVisit $visit): array => [
                'label' => $this->pageLabel($visit->path),
                'route' => $visit->route_name ?: '-',
                'views' => number_format((int) $visit->views, 0, ',', '.'),
                'visitors' => number_format((int) $visit->visitors, 0, ',', '.'),
                'url' => url($visit->path === '/' ? '/' : '/'.ltrim($visit->path, '/')),
            ])
            ->all();
    }

    private function pageLabel(string $path): string
    {
        return $path === '/'
            ? 'Beranda'
            : '/'.ltrim($path, '/');
    }
}
