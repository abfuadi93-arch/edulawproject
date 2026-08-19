<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AssignedInsights\AssignedInsightResource;
use App\Filament\Resources\Authors\AuthorResource;
use App\Filament\Resources\Insights\InsightResource;
use App\Filament\Resources\Multimedia\MultimediaResource;
use App\Filament\Resources\Opportunities\OpportunityResource;
use App\Filament\Resources\ProgramResource;
use App\Filament\Resources\Publications\PublicationResource;
use App\Models\Author;
use App\Models\Insight;
use App\Models\Multimedia;
use App\Models\Opportunity;
use App\Models\PageVisit;
use App\Models\Program;
use App\Models\Publication;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AdminStatsOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = [
        'md' => 6,
        'xl' => 12,
    ];

    protected ?string $heading = 'Ringkasan Edulaw';

    protected ?string $description = 'Metrik utama editorial, publikasi, kontributor, dan audiens website.';

    protected static ?int $sort = -90;

    protected static bool $isLazy = false;

    protected function getColumns(): int|array|null
    {
        if ($this->isContributorDashboard()) {
            return [
                'sm' => 2,
                'lg' => $this->contributorStatCount(),
            ];
        }

        return [
            'sm' => 2,
            'lg' => 3,
            'xl' => 3,
        ];
    }

    public static function canView(): bool
    {
        return auth()->check();
    }

    protected function getStats(): array
    {
        $user = auth()->user();

        if ($this->isContributorDashboard()) {
            return $this->getContributorStats();
        }

        $counts = Cache::remember('dashboard.stats-overview.v4', now()->addMinutes(5), fn (): array => [
            'insights' => [
                'total' => Insight::query()->count(),
                'month' => Insight::query()->where('created_at', '>=', now()->startOfMonth())->count(),
                'published' => Insight::query()->where('status', 'published')->count(),
                'published_month' => Insight::query()
                    ->where('status', 'published')
                    ->where('published_at', '>=', now()->startOfMonth())
                    ->count(),
                'review' => Insight::query()->where('status', 'review')->count(),
                'chart' => $this->weeklyCounts(Insight::class),
            ],
            'publications' => [
                'total' => Publication::query()->count(),
                'month' => Publication::query()->where('created_at', '>=', now()->startOfMonth())->count(),
                'chart' => $this->weeklyCounts(Publication::class),
            ],
            'authors' => [
                'active' => Author::query()->where('is_active', true)->count(),
                'month' => Author::query()->where('created_at', '>=', now()->startOfMonth())->count(),
                'chart' => $this->weeklyCounts(Author::class),
            ],
            'visits' => [
                'month' => PageVisit::query()
                    ->where('status_code', 200)
                    ->since(now()->subDays(29)->startOfDay())
                    ->count(),
                'week' => PageVisit::query()
                    ->where('status_code', 200)
                    ->since(now()->subDays(6)->startOfDay())
                    ->count(),
                'chart' => $this->weeklyVisitCounts(),
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
            'opportunities' => [
                'open' => Opportunity::query()->where('status', 'open')->count(),
                'month' => Opportunity::query()->where('created_at', '>=', now()->startOfMonth())->count(),
                'chart' => $this->weeklyCounts(Opportunity::class),
            ],
        ]);
        $stats = [];

        if ($user?->can('view insights')) {
            $stats[] = Stat::make('Total Insight', number_format($counts['insights']['total'], 0, ',', '.'))
                ->description($counts['insights']['month'].' baru bulan ini')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->chart($counts['insights']['chart'])
                ->color('primary')
                ->icon('heroicon-o-newspaper')
                ->url(InsightResource::getUrl('index'))
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-navy']);

            $stats[] = Stat::make('Insight Terbit', number_format($counts['insights']['published'], 0, ',', '.'))
                ->description($counts['insights']['published_month'].' terbit bulan ini')
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success')
                ->icon('heroicon-o-globe-alt')
                ->url(InsightResource::getUrl('index', ['activeTab' => 'published']))
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-emerald']);

            $stats[] = Stat::make('Dalam Review', number_format($counts['insights']['review'], 0, ',', '.'))
                ->description('memerlukan keputusan editorial')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning')
                ->icon('heroicon-o-document-magnifying-glass')
                ->url(InsightResource::getUrl('index', ['activeTab' => 'review']))
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-amber']);
        }

        if ($user?->can('view publications')) {
            $stats[] = Stat::make('Total Publikasi', number_format($counts['publications']['total'], 0, ',', '.'))
                ->description($counts['publications']['month'].' baru bulan ini')
                ->descriptionIcon('heroicon-o-document-check')
                ->chart($counts['publications']['chart'])
                ->color('success')
                ->icon('heroicon-o-document-text')
                ->url(PublicationResource::getUrl('index'))
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-emerald']);
        }

        if ($user?->can('view authors')) {
            $stats[] = Stat::make('Kontributor Aktif', number_format($counts['authors']['active'], 0, ',', '.'))
                ->description($counts['authors']['month'].' profil baru bulan ini')
                ->descriptionIcon('heroicon-o-user-group')
                ->chart($counts['authors']['chart'])
                ->color('primary')
                ->icon('heroicon-o-users')
                ->url(AuthorResource::getUrl('index'))
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-navy']);
        }

        if ($user?->can('view programs') && ! $user?->can('view insights')) {
            $stats[] = Stat::make('Total Program', number_format($counts['programs']['total'], 0, ',', '.'))
                ->description($counts['programs']['month'].' baru bulan ini')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->chart($counts['programs']['chart'])
                ->color('warning')
                ->icon('heroicon-o-academic-cap')
                ->url(ProgramResource::getUrl('index'))
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-amber']);
        }

        if ($user?->can('view multimedia') && ! $user?->can('view insights')) {
            $stats[] = Stat::make('Total Multimedia', number_format($counts['multimedia']['total'], 0, ',', '.'))
                ->description($counts['multimedia']['month'].' baru bulan ini')
                ->descriptionIcon('heroicon-o-play-circle')
                ->chart($counts['multimedia']['chart'])
                ->color('info')
                ->icon('heroicon-o-play-circle')
                ->url(MultimediaResource::getUrl('index'))
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-indigo']);
        }

        if ($user?->can('view opportunities') && ! $user?->can('view insights')) {
            $stats[] = Stat::make('Peluang Terbuka', number_format($counts['opportunities']['open'], 0, ',', '.'))
                ->description($counts['opportunities']['month'].' baru bulan ini')
                ->descriptionIcon('heroicon-o-megaphone')
                ->chart($counts['opportunities']['chart'])
                ->color('warning')
                ->icon('heroicon-o-briefcase')
                ->url(OpportunityResource::getUrl('index'))
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-amber']);
        }

        $stats[] = Stat::make('Kunjungan 30 Hari', number_format($counts['visits']['month'], 0, ',', '.'))
            ->description(number_format($counts['visits']['week'], 0, ',', '.').' page views dalam 7 hari')
            ->descriptionIcon('heroicon-o-arrow-trending-up')
            ->chart($counts['visits']['chart'])
            ->color('primary')
            ->icon('heroicon-o-chart-bar-square')
            ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-navy']);

        return array_slice($stats, 0, 6);
    }

    protected function getHeading(): ?string
    {
        return $this->isContributorDashboard()
            ? 'Pekerjaan Editorial Saya'
            : parent::getHeading();
    }

    protected function getDescription(): ?string
    {
        return $this->isContributorDashboard()
            ? 'Tulisan yang Anda buat sebagai writer dan naskah yang ditugaskan kepada Anda sebagai editor.'
            : parent::getDescription();
    }

    /**
     * @return array<Stat>
     */
    private function getContributorStats(): array
    {
        $user = auth()->user();
        $userId = $user?->getKey();
        $counts = Cache::remember("dashboard.contributor-stats-overview.v3.{$userId}", now()->addMinutes(5), fn (): array => [
            'written_draft' => Insight::query()->where('created_by', $userId)->where('status', 'draft')->count(),
            'written' => Insight::query()->where('created_by', $userId)->count(),
            'assigned_review' => Insight::query()->where('assigned_editor_id', $userId)->where('status', 'review')->count(),
            'assigned' => Insight::query()->where('assigned_editor_id', $userId)->count(),
        ]);
        $stats = [];

        if ($this->canWriteInsights()) {
            $stats[] = Stat::make('Draft Tulisan Saya', number_format($counts['written_draft'], 0, ',', '.'))
                ->description('masih dapat Anda kerjakan')
                ->descriptionIcon('heroicon-o-pencil-square')
                ->color('primary')
                ->icon('heroicon-o-document-text')
                ->url(InsightResource::getUrl('index', ['activeTab' => 'draft']))
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-blue']);
            $stats[] = Stat::make('Tulisan Saya', number_format($counts['written'], 0, ',', '.'))
                ->description('seluruh tulisan yang Anda buat')
                ->descriptionIcon('heroicon-o-document-duplicate')
                ->color('info')
                ->icon('heroicon-o-pencil')
                ->url(InsightResource::getUrl('index'))
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-indigo']);
        }

        if ($this->canReviewInsights()) {
            $stats[] = Stat::make('Menunggu Review Anda', number_format($counts['assigned_review'], 0, ',', '.'))
                ->description('perlu keputusan Anda')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning')
                ->icon('heroicon-o-document-magnifying-glass')
                ->url(AssignedInsightResource::getUrl('index', ['activeTab' => 'review']))
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-amber']);
            $stats[] = Stat::make('Tugas Editor', number_format($counts['assigned'], 0, ',', '.'))
                ->description('seluruh naskah yang ditugaskan')
                ->descriptionIcon('heroicon-o-inbox-stack')
                ->color('success')
                ->icon('heroicon-o-inbox-stack')
                ->url(AssignedInsightResource::getUrl('index'))
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-emerald']);
        }

        return $stats;
    }

    private function isContributorDashboard(): bool
    {
        $user = auth()->user();

        return (bool) $user
            && ! $user->hasAnyRole(['super_admin', 'Super Admin', 'SuperAdmin'])
            && ($this->canWriteInsights() || $this->canReviewInsights());
    }

    private function canWriteInsights(): bool
    {
        $user = auth()->user();

        return (bool) $user && ($user->hasAnyRole(['writer', 'Writer']) || $user->can('create insights'));
    }

    private function canReviewInsights(): bool
    {
        return auth()->user()?->canAccessAssignedEditorialInsights() ?? false;
    }

    private function contributorStatCount(): int
    {
        return ($this->canWriteInsights() ? 2 : 0) + ($this->canReviewInsights() ? 2 : 0);
    }

    /**
     * @param  class-string<Model>  $model
     */
    private function weeklyCounts(string $model): array
    {
        return collect(range(6, 0))
            ->map(fn (int $daysAgo): int => $model::query()
                ->whereDate('created_at', now()->subDays($daysAgo)->toDateString())
                ->count())
            ->all();
    }

    private function weeklyVisitCounts(): array
    {
        return collect(range(6, 0))
            ->map(fn (int $daysAgo): int => PageVisit::query()
                ->where('status_code', 200)
                ->whereDate('visited_at', now()->subDays($daysAgo)->toDateString())
                ->count())
            ->all();
    }
}
