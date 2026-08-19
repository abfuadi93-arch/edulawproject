<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Insights\InsightResource;
use App\Filament\Resources\Opportunities\OpportunityResource;
use App\Filament\Resources\ProgramResource;
use App\Models\Insight;
use App\Models\Opportunity;
use App\Models\Program;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class RequiresAttentionWidget extends Widget
{
    protected string $view = 'filament.widgets.requires-attention';

    protected int|string|array $columnSpan = [
        'md' => 6,
        'xl' => 4,
    ];

    protected static ?int $sort = -70;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        $user = auth()->user();

        return (bool) $user && collect([
            'view insights',
            'view programs',
            'view opportunities',
        ])->contains(fn (string $permission): bool => $user->can($permission));
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $counts = Cache::remember('dashboard.requires-attention.v2', now()->addMinutes(3), fn (): array => [
            'draft_editorials' => Insight::query()->where('status', 'draft')->count(),
            'insights_without_cover' => Insight::query()
                ->whereIn('status', ['draft', 'review', 'published'])
                ->where(fn ($query) => $query->whereNull('cover_image')->orWhere('cover_image', ''))
                ->count(),
            'insights_without_excerpt' => Insight::query()
                ->whereIn('status', ['draft', 'review', 'published'])
                ->where(fn ($query) => $query->whereNull('excerpt')->orWhere('excerpt', ''))
                ->count(),
            'programs_without_poster' => Program::query()
                ->whereIn('status', ['upcoming', 'ongoing'])
                ->where(fn ($query) => $query
                    ->where(fn ($imageQuery) => $imageQuery->whereNull('image')->orWhere('image', ''))
                    ->where(fn ($heroQuery) => $heroQuery->whereNull('hero_image')->orWhere('hero_image', '')))
                ->count(),
            'expired_open_opportunities' => Opportunity::query()
                ->where('status', 'open')
                ->whereDate('deadline', '<', today())
                ->count(),
        ]);

        $items = collect([
            $user?->can('view insights') ? [
                'label' => 'Draft editorial',
                'count' => $counts['draft_editorials'],
                'tone' => 'blue',
                'icon' => 'heroicon-o-pencil-square',
                'url' => InsightResource::getUrl('index'),
            ] : null,
            $user?->can('view insights') ? [
                'label' => 'Artikel tanpa cover',
                'count' => $counts['insights_without_cover'],
                'tone' => 'orange',
                'icon' => 'heroicon-o-photo',
                'url' => InsightResource::getUrl('index'),
            ] : null,
            $user?->can('view insights') ? [
                'label' => 'Artikel tanpa ringkasan',
                'count' => $counts['insights_without_excerpt'],
                'tone' => 'orange',
                'icon' => 'heroicon-o-bars-3-bottom-left',
                'url' => InsightResource::getUrl('index'),
            ] : null,
            $user?->can('view programs') ? [
                'label' => 'Program aktif tanpa poster',
                'count' => $counts['programs_without_poster'],
                'tone' => 'orange',
                'icon' => 'heroicon-o-calendar-days',
                'url' => ProgramResource::getUrl('index'),
            ] : null,
            $user?->can('view opportunities') ? [
                'label' => 'Opportunity lewat deadline',
                'count' => $counts['expired_open_opportunities'],
                'tone' => 'red',
                'icon' => 'heroicon-o-clock',
                'url' => OpportunityResource::getUrl('index'),
            ] : null,
        ])->filter(fn (?array $item): bool => $item !== null && $item['count'] > 0)->values();

        return [
            'items' => $items,
            'pendingCount' => $items->sum('count'),
        ];
    }
}
