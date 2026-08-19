<?php

namespace App\Filament\Widgets;

use App\Models\Insight;
use App\Models\PageVisit;
use App\Models\Program;
use App\Models\Publication;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ContentPerformanceWidget extends Widget
{
    protected string $view = 'filament.widgets.content-performance';

    protected int|string|array $columnSpan = [
        'md' => 6,
        'xl' => 6,
    ];

    protected static ?int $sort = 30;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        $user = auth()->user();

        return (bool) $user && collect([
            'view insights',
            'view publications',
            'view programs',
        ])->contains(fn (string $permission): bool => $user->can($permission));
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $scope = collect([
            'insights' => $user?->can('view insights') ?? false,
            'publications' => $user?->can('view publications') ?? false,
            'programs' => $user?->can('view programs') ?? false,
        ]);
        $cacheKey = 'dashboard.content-performance.v3.'.sha1($scope->toJson());

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($scope): array {
            $items = collect();

            if ($scope['insights']) {
                $items->push($this->topEditorial());
            }

            if ($scope['publications']) {
                $items->push($this->topPublication());
            }

            if ($scope['programs']) {
                $items->push($this->topProgram());
            }

            if ($scope['insights']) {
                $items->push($this->latestPublishedInsight());
            }

            return ['items' => $items->values()->all()];
        });
    }

    private function topEditorial(): array
    {
        return $this->topVisitedContent(
            label: 'Editorial terpopuler',
            routeName: 'insights.show',
            model: Insight::class,
            titleColumn: 'title',
            tone: 'blue',
            icon: 'heroicon-o-newspaper',
            urlResolver: fn (Insight $insight): string => route('insights.show', $insight->slug),
        );
    }

    private function topPublication(): array
    {
        return $this->topVisitedContent(
            label: 'Publikasi paling banyak diunduh',
            routeName: 'publications.download',
            model: Publication::class,
            titleColumn: 'title',
            tone: 'green',
            icon: 'heroicon-o-document-text',
            urlResolver: fn (Publication $publication): string => route('publications.show', $publication->slug),
            metricLabel: 'unduhan',
        );
    }

    private function topProgram(): array
    {
        return $this->topVisitedContent(
            label: 'Program terpopuler',
            routeName: 'programs.show',
            model: Program::class,
            titleColumn: 'name',
            tone: 'orange',
            icon: 'heroicon-o-academic-cap',
            urlResolver: fn (Program $program): string => route('programs.show', $program->slug),
        );
    }

    /**
     * @param  class-string<Model>  $model
     */
    private function topVisitedContent(
        string $label,
        string $routeName,
        string $model,
        string $titleColumn,
        string $tone,
        string $icon,
        callable $urlResolver,
        string $metricLabel = 'views',
    ): array {
        $visit = PageVisit::query()
            ->selectRaw('path, COUNT(*) as views')
            ->where('route_name', $routeName)
            ->where('visited_at', '>=', now()->subDays(30)->startOfDay())
            ->groupBy('path')
            ->orderByDesc('views')
            ->first();

        if (! $visit) {
            return $this->emptyItem($label, $tone, $icon);
        }

        $segments = Str::of($visit->path)
            ->trim('/')
            ->explode('/')
            ->filter()
            ->values();
        $slug = $routeName === 'publications.download'
            ? $segments->get($segments->count() - 2)
            : $segments->last();

        if (blank($slug)) {
            return $this->emptyItem($label, $tone, $icon);
        }

        $record = $model::query()
            ->where('slug', $slug)
            ->first();

        if (! $record) {
            return $this->emptyItem($label, $tone, $icon);
        }

        return [
            'label' => $label,
            'title' => $record->{$titleColumn},
            'metric' => number_format((int) $visit->views, 0, ',', '.').' '.$metricLabel,
            'tone' => $tone,
            'icon' => $icon,
            'url' => $urlResolver($record),
            'isEmpty' => false,
        ];
    }

    private function latestPublishedInsight(): array
    {
        $insight = Insight::query()
            ->published()
            ->latest('published_at')
            ->first();

        if (! $insight) {
            return $this->emptyItem('Editorial terbaru', 'blue', 'heroicon-o-clock');
        }

        return [
            'label' => 'Editorial terbaru',
            'title' => $insight->title,
            'metric' => $insight->published_at?->diffForHumans() ?? 'Sudah terbit',
            'tone' => 'blue',
            'icon' => 'heroicon-o-clock',
            'url' => route('insights.show', $insight->slug),
            'isEmpty' => false,
        ];
    }

    private function emptyItem(string $label, string $tone, string $icon): array
    {
        return [
            'label' => $label,
            'title' => 'No data yet',
            'metric' => 'Menunggu data yang valid',
            'tone' => $tone,
            'icon' => $icon,
            'url' => null,
            'isEmpty' => true,
        ];
    }
}
