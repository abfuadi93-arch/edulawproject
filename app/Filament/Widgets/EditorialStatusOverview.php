<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Insights\InsightResource;
use App\Models\Insight;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EditorialStatusOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Status Editorial';

    protected ?string $description = 'Distribusi naskah berdasarkan tahapan workflow saat ini.';

    protected static ?int $sort = -29;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return static::canMonitorEditorial();
    }

    /**
     * @return array{draft: int, reviewed: int, published: int}
     */
    public static function statusCounts(): array
    {
        $counts = Insight::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->whereIn('status', ['draft', 'reviewed', 'published'])
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'draft' => (int) ($counts['draft'] ?? 0),
            'reviewed' => (int) ($counts['reviewed'] ?? 0),
            'published' => (int) ($counts['published'] ?? 0),
        ];
    }

    protected function getColumns(): int|array|null
    {
        return [
            'sm' => 3,
        ];
    }

    protected function getStats(): array
    {
        $counts = static::statusCounts();
        $indexUrl = InsightResource::getUrl('index');

        return [
            Stat::make('Draft', number_format($counts['draft'], 0, ',', '.'))
                ->description('Masih dalam penyusunan')
                ->descriptionIcon('heroicon-o-pencil-square')
                ->color('primary')
                ->icon('heroicon-o-document')
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-blue'])
                ->url($indexUrl),

            Stat::make('Reviewed', number_format($counts['reviewed'], 0, ',', '.'))
                ->description('Menunggu keputusan terbit')
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('warning')
                ->icon('heroicon-o-clipboard-document-check')
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-amber'])
                ->url($indexUrl),

            Stat::make('Published', number_format($counts['published'], 0, ',', '.'))
                ->description('Sudah diterbitkan')
                ->descriptionIcon('heroicon-o-globe-alt')
                ->color('success')
                ->icon('heroicon-o-newspaper')
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-emerald'])
                ->url($indexUrl),
        ];
    }

    private static function canMonitorEditorial(): bool
    {
        $user = auth()->user();

        return (bool) $user && (
            $user->can('update all insights')
            || $user->can('review insights')
            || $user->can('publish insights')
        );
    }
}
