<?php

namespace App\Filament\Widgets;

use App\Models\PageVisit;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TrafficOverviewWidget extends Widget
{
    public string $trafficPeriod = 'week';

    protected string $view = 'filament.widgets.traffic-overview';

    protected int|string|array $columnSpan = 'full';

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
        $period = in_array($this->trafficPeriod, ['week', 'month'], true)
            ? $this->trafficPeriod
            : 'week';
        $chart = $this->prepareChart(
            $period === 'month'
                ? $this->monthlySeries($lastThirtyDays)
                : $this->dailySeries($lastSevenDays),
        );

        $todayVisits = $this->visitsQuery()
            ->whereDate('visited_at', $today)
            ->count();
        $todayVisitors = $this->visitsQuery()
            ->whereDate('visited_at', $today)
            ->distinct()
            ->count('visitor_id');
        $sevenDayVisitors = $this->visitsQuery()
            ->since($lastSevenDays)
            ->distinct()
            ->count('visitor_id');
        $thirtyDayVisitors = $this->visitsQuery()
            ->since($lastThirtyDays)
            ->distinct()
            ->count('visitor_id');

        return [
            'title' => $this->isEditorDashboard() ? 'Traffic Editorial' : 'Traffic Website',
            'description' => $this->isEditorDashboard()
                ? 'Ringkasan pembaca halaman Editorial dan Riset & Publikasi.'
                : 'Ringkasan audiens dan page views website publik.',
            'stats' => [
                [
                    'label' => 'Page views hari ini',
                    'value' => number_format($todayVisits, 0, ',', '.'),
                    'icon' => 'heroicon-o-calendar-days',
                    'tone' => 'primary',
                ],
                [
                    'label' => 'Pengunjung hari ini',
                    'value' => number_format($todayVisitors, 0, ',', '.'),
                    'icon' => 'heroicon-o-user',
                    'tone' => 'success',
                ],
                [
                    'label' => 'Pengunjung 7 hari',
                    'value' => number_format($sevenDayVisitors, 0, ',', '.'),
                    'icon' => 'heroicon-o-chart-bar',
                    'tone' => 'warning',
                ],
                [
                    'label' => 'Pengunjung 30 hari',
                    'value' => number_format($thirtyDayVisitors, 0, ',', '.'),
                    'icon' => 'heroicon-o-arrow-trending-up',
                    'tone' => 'warning',
                ],
            ],
            'trendTitle' => $period === 'month' ? 'Tren 30 Hari' : 'Tren Mingguan',
            'chartSeries' => $chart['series'],
            'chartWidth' => $chart['width'],
            'linePoints' => $chart['linePoints'],
            'topPages' => $this->topPages($lastThirtyDays),
        ];
    }

    private function dailySeries(Carbon $startDate): array
    {
        $rawCounts = $this->visitsQuery()
            ->selectRaw('DATE(visited_at) as visit_date, COUNT(*) as views, COUNT(DISTINCT visitor_id) as visitors')
            ->where('visited_at', '>=', $startDate)
            ->groupBy('visit_date')
            ->get()
            ->keyBy('visit_date');

        $days = collect(range(0, 6))->map(function (int $offset) use ($startDate, $rawCounts): array {
            $date = $startDate->copy()->addDays($offset);
            $count = $rawCounts->get($date->toDateString());
            $views = (int) ($count?->views ?? 0);
            $visitors = (int) ($count?->visitors ?? 0);

            return [
                'label' => $date->format('d M'),
                'views' => $views,
                'visitors' => $visitors,
                'date' => $date->toDateString(),
            ];
        });

        return $days->all();
    }

    private function monthlySeries(Carbon $startDate): array
    {
        return collect(range(0, 5))
            ->map(function (int $bucket) use ($startDate): array {
                $bucketStart = $startDate->copy()->addDays($bucket * 5);
                $bucketEnd = $bucketStart->copy()->addDays(4)->endOfDay();
                $counts = $this->visitsQuery()
                    ->selectRaw('COUNT(*) as views, COUNT(DISTINCT visitor_id) as visitors')
                    ->whereBetween('visited_at', [$bucketStart, $bucketEnd])
                    ->first();

                return [
                    'label' => $bucketStart->format('d M').'–'.$bucketEnd->format('d M'),
                    'views' => (int) ($counts?->views ?? 0),
                    'visitors' => (int) ($counts?->visitors ?? 0),
                ];
            })
            ->all();
    }

    private function prepareChart(array $series): array
    {
        $chartBottom = 210;
        $plotHeight = 160;
        $slotWidth = 140;
        $maxValue = max(
            1,
            (int) collect($series)
                ->flatMap(fn (array $item): array => [$item['views'], $item['visitors']])
                ->max(),
        );

        $series = collect($series)
            ->values()
            ->map(function (array $item, int $index) use ($chartBottom, $maxValue, $plotHeight, $slotWidth): array {
                $x = 70 + ($index * $slotWidth);
                $viewsHeight = $item['views'] > 0
                    ? max(4, (int) round(($item['views'] / $maxValue) * $plotHeight))
                    : 0;
                $visitorsHeight = $item['visitors'] > 0
                    ? max(4, (int) round(($item['visitors'] / $maxValue) * $plotHeight))
                    : 0;

                return $item + [
                    'x' => $x,
                    'viewsY' => $chartBottom - $viewsHeight,
                    'viewsHeight' => $viewsHeight,
                    'visitorsY' => $chartBottom - $visitorsHeight,
                ];
            });

        return [
            'series' => $series->all(),
            'width' => max(700, $series->count() * $slotWidth),
            'linePoints' => $series
                ->map(fn (array $item): string => $item['x'].','.$item['visitorsY'])
                ->implode(' '),
        ];
    }

    private function topPages(Carbon $startDate): array
    {
        return $this->visitsQuery()
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

    private function visitsQuery(): Builder
    {
        $query = PageVisit::query()->where('status_code', 200);

        if ($this->isEditorDashboard()) {
            $query->whereIn('route_name', [
                'insights.index',
                'insights.category',
                'insights.show',
                'publications.index',
                'publications.show',
                'publications.download',
            ]);
        }

        return $query;
    }

    private function isEditorDashboard(): bool
    {
        $user = auth()->user();

        return (bool) $user
            && $user->hasRole('editor')
            && ! $user->hasRole('super_admin');
    }

    private function pageLabel(string $path): string
    {
        return $path === '/'
            ? 'Beranda'
            : '/'.ltrim($path, '/');
    }
}
