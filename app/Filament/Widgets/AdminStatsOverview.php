<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AssignedInsights\AssignedInsightResource;
use App\Filament\Resources\Insights\InsightResource;
use App\Models\CollaborationSubmission;
use App\Models\ContactMessage;
use App\Models\Insight;
use App\Models\Multimedia;
use App\Models\Program;
use App\Models\Publication;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AdminStatsOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Inventaris Konten';

    protected ?string $description = 'Total aset konten dan interaksi yang dikelola melalui website.';

    protected static ?int $sort = -20;

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

        if ($this->isContributorDashboard()) {
            return $this->getContributorStats();
        }

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
            $stats[] = Stat::make('Total Editorial', number_format($counts['insights']['total'], 0, ',', '.'))
                ->description($counts['insights']['month'].' baru bulan ini')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->chart($counts['insights']['chart'])
                ->color('primary')
                ->icon('heroicon-o-newspaper')
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-blue']);
        }

        if ($user?->can('view publications')) {
            $stats[] = Stat::make('Total Publikasi', number_format($counts['publications']['total'], 0, ',', '.'))
                ->description($counts['publications']['month'].' baru bulan ini')
                ->descriptionIcon('heroicon-o-document-check')
                ->chart($counts['publications']['chart'])
                ->color('success')
                ->icon('heroicon-o-document-text')
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-emerald']);
        }

        if ($user?->can('view programs')) {
            $stats[] = Stat::make('Total Program', number_format($counts['programs']['total'], 0, ',', '.'))
                ->description($counts['programs']['month'].' baru bulan ini')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->chart($counts['programs']['chart'])
                ->color('warning')
                ->icon('heroicon-o-academic-cap')
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-amber']);
        }

        if ($user?->can('view multimedia')) {
            $stats[] = Stat::make('Total Multimedia', number_format($counts['multimedia']['total'], 0, ',', '.'))
                ->description($counts['multimedia']['month'].' baru bulan ini')
                ->descriptionIcon('heroicon-o-play-circle')
                ->chart($counts['multimedia']['chart'])
                ->color('info')
                ->icon('heroicon-o-play-circle')
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-indigo']);
        }

        if ($user?->can('view collaboration submissions')) {
            $stats[] = Stat::make('Permintaan Kolaborasi', number_format($counts['collaborations']['total'], 0, ',', '.'))
                ->description($counts['collaborations']['month'].' baru bulan ini')
                ->descriptionIcon('heroicon-o-inbox-arrow-down')
                ->chart($counts['collaborations']['chart'])
                ->color('info')
                ->icon('heroicon-o-hand-raised')
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-violet']);
        }

        if ($user?->can('view contact messages')) {
            $stats[] = Stat::make('Pesan Kontak', number_format($counts['contacts']['total'], 0, ',', '.'))
                ->description($counts['contacts']['month'].' baru bulan ini')
                ->descriptionIcon('heroicon-o-envelope')
                ->chart($counts['contacts']['chart'])
                ->color('danger')
                ->icon('heroicon-o-envelope')
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-rose']);
        }

        return $stats;
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
}
