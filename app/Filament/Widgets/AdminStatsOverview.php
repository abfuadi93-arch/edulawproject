<?php

namespace App\Filament\Widgets;

use App\Models\CollaborationSubmission;
use App\Models\ContactMessage;
use App\Models\Insight;
use App\Models\Multimedia;
use App\Models\Program;
use App\Models\Publication;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class AdminStatsOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -20;

    protected function getColumns(): int|array|null
    {
        return [
            'sm' => 2,
            'xl' => 6,
        ];
    }

    public static function canView(): bool
    {
        $user = auth()->user();

        return (bool) $user && collect([
            'view insights',
            'view publications',
            'view programs',
            'view multimedia',
            'view contact messages',
            'view collaboration submissions',
        ])->contains(fn (string $permission): bool => $user->can($permission));
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $counts = Cache::remember('dashboard.stats-overview', now()->addMinutes(5), fn (): array => [
            'insights' => [
                'total' => Insight::query()->count(),
                'month' => Insight::query()->where('created_at', '>=', now()->startOfMonth())->count(),
                'chart' => $this->weeklyCounts(Insight::class),
            ],
            'publications' => [
                'total' => Publication::query()->count(),
                'month' => Publication::query()->where('created_at', '>=', now()->startOfMonth())->count(),
                'chart' => $this->weeklyCounts(Publication::class),
            ],
            'programs' => [
                'total' => Program::query()->count(),
                'month' => Program::query()->where('created_at', '>=', now()->startOfMonth())->count(),
                'chart' => $this->weeklyCounts(Program::class),
            ],
            'multimedia' => [
                'total' => Multimedia::query()->count(),
                'month' => Multimedia::query()->where('created_at', '>=', now()->startOfMonth())->count(),
                'chart' => $this->weeklyCounts(Multimedia::class),
            ],
            'collaborations' => [
                'total' => CollaborationSubmission::query()->count(),
                'month' => CollaborationSubmission::query()->where('created_at', '>=', now()->startOfMonth())->count(),
                'chart' => $this->weeklyCounts(CollaborationSubmission::class),
            ],
            'contacts' => [
                'total' => ContactMessage::query()->count(),
                'month' => ContactMessage::query()->where('created_at', '>=', now()->startOfMonth())->count(),
                'chart' => $this->weeklyCounts(ContactMessage::class),
            ],
        ]);
        $stats = [];

        if ($user?->can('view insights')) {
            $stats[] = Stat::make('Editorials', number_format($counts['insights']['total'], 0, ',', '.'))
                ->description($counts['insights']['month'].' new this month')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->chart($counts['insights']['chart'])
                ->color('primary')
                ->icon('heroicon-o-newspaper');
        }

        if ($user?->can('view publications')) {
            $stats[] = Stat::make('Publications', number_format($counts['publications']['total'], 0, ',', '.'))
                ->description($counts['publications']['month'].' new this month')
                ->descriptionIcon('heroicon-o-document-check')
                ->chart($counts['publications']['chart'])
                ->color('success')
                ->icon('heroicon-o-document-text');
        }

        if ($user?->can('view programs')) {
            $stats[] = Stat::make('Programs', number_format($counts['programs']['total'], 0, ',', '.'))
                ->description($counts['programs']['month'].' new this month')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->chart($counts['programs']['chart'])
                ->color('warning')
                ->icon('heroicon-o-academic-cap');
        }

        if ($user?->can('view multimedia')) {
            $stats[] = Stat::make('Multimedia', number_format($counts['multimedia']['total'], 0, ',', '.'))
                ->description($counts['multimedia']['month'].' new this month')
                ->descriptionIcon('heroicon-o-play-circle')
                ->chart($counts['multimedia']['chart'])
                ->color('info')
                ->icon('heroicon-o-play-circle');
        }

        if ($user?->can('view collaboration submissions')) {
            $stats[] = Stat::make('Collaboration Requests', number_format($counts['collaborations']['total'], 0, ',', '.'))
                ->description($counts['collaborations']['month'].' new this month')
                ->descriptionIcon('heroicon-o-inbox-arrow-down')
                ->chart($counts['collaborations']['chart'])
                ->color('danger')
                ->icon('heroicon-o-hand-raised');
        }

        if ($user?->can('view contact messages')) {
            $stats[] = Stat::make('Contact Messages', number_format($counts['contacts']['total'], 0, ',', '.'))
                ->description($counts['contacts']['month'].' new this month')
                ->descriptionIcon('heroicon-o-envelope')
                ->chart($counts['contacts']['chart'])
                ->color('danger')
                ->icon('heroicon-o-envelope');
        }

        return $stats;
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $model
     */
    private function weeklyCounts(string $model): array
    {
        return collect(range(6, 0))
            ->map(fn (int $daysAgo): int => $model::query()
                ->whereDate('created_at', now()->subDays($daysAgo)->toDateString())
                ->count())
            ->all();
    }
}
