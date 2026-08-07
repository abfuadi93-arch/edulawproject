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

        if ($this->isEditorDashboard()) {
            return $this->getEditorStats();
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
        return $this->isEditorDashboard()
            ? 'Naskah Saya'
            : parent::getHeading();
    }

    protected function getDescription(): ?string
    {
        return $this->isEditorDashboard()
            ? 'Naskah yang ditugaskan dan menunggu keputusan Anda.'
            : parent::getDescription();
    }

    /**
     * @return array<Stat>
     */
    private function getEditorStats(): array
    {
        $editorId = auth()->id();
        $counts = Cache::remember("dashboard.editor-stats-overview.v2.{$editorId}", now()->addMinutes(5), fn (): array => [
            'review' => Insight::query()->where('assigned_editor_id', $editorId)->where('status', 'review')->count(),
            'assigned' => Insight::query()->where('assigned_editor_id', $editorId)->count(),
        ]);

        return [
            Stat::make('Naskah Menunggu Review', number_format($counts['review'], 0, ',', '.'))
                ->description('perlu keputusan Anda')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning')
                ->icon('heroicon-o-document-magnifying-glass')
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-amber']),
            Stat::make('Naskah Saya', number_format($counts['assigned'], 0, ',', '.'))
                ->description('seluruh naskah yang ditugaskan')
                ->descriptionIcon('heroicon-o-inbox-stack')
                ->color('primary')
                ->icon('heroicon-o-inbox-stack')
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-blue']),
        ];
    }

    private function isEditorDashboard(): bool
    {
        $user = auth()->user();

        return (bool) $user
            && $user->hasRole('editor')
            && ! $user->hasRole('super_admin');
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
