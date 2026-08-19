<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AssignedInsights\AssignedInsightResource;
use App\Filament\Resources\Insights\InsightResource;
use App\Models\Insight;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class EditorialStatusOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = [
        'md' => 6,
        'xl' => 12,
    ];

    protected ?string $heading = 'Status Editorial';

    protected ?string $description = 'Ringkasan alur Insight yang aktif.';

    protected static ?int $sort = -85;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        $user = auth()->user();

        return static::canMonitorEditorial()
            && ! $user?->hasAnyRole(['super_admin', 'Super Admin', 'SuperAdmin']);
    }

    /**
     * @return array{draft: int, review: int, published: int}
     */
    public static function statusCounts(): array
    {
        return static::countsFromQuery(Insight::query());
    }

    /**
     * @return array{draft: int, review: int, published: int}
     */
    public static function currentUserStatusCounts(): array
    {
        $query = Insight::query();
        $user = auth()->user();

        if ($user && ! $user->hasAnyRole(['super_admin', 'Super Admin', 'SuperAdmin'])) {
            $query->where('assigned_editor_id', $user->getKey());
        }

        return static::countsFromQuery($query);
    }

    protected function getHeading(): ?string
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'Super Admin', 'SuperAdmin'])
            ? parent::getHeading()
            : 'Status Tugas Editor';
    }

    protected function getDescription(): ?string
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'Super Admin', 'SuperAdmin'])
            ? parent::getDescription()
            : 'Ringkasan naskah yang ditugaskan kepada Anda sebagai editor.';
    }

    private static function countsFromQuery(Builder $query): array
    {
        $counts = $query
            ->selectRaw('status, COUNT(*) as aggregate')
            ->whereIn('status', ['draft', 'review', 'published'])
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'draft' => (int) ($counts['draft'] ?? 0),
            'review' => (int) ($counts['review'] ?? 0),
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
        $counts = static::currentUserStatusCounts();
        $indexUrl = auth()->user()?->hasAnyRole(['super_admin', 'Super Admin', 'SuperAdmin'])
            ? InsightResource::getUrl('index')
            : AssignedInsightResource::getUrl('index');

        return [
            Stat::make('Draft', number_format($counts['draft'], 0, ',', '.'))
                ->description('Masih dalam penyusunan')
                ->descriptionIcon('heroicon-o-pencil-square')
                ->color('primary')
                ->icon('heroicon-o-document')
                ->extraAttributes(['class' => 'edulaw-stat edulaw-stat-blue'])
                ->url($indexUrl),

            Stat::make('Review', number_format($counts['review'], 0, ',', '.'))
                ->description('Menunggu keputusan Editor')
                ->descriptionIcon('heroicon-o-clock')
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
            $user->can('view_all_editorial_insights')
            || $user->can('view_assigned_editorial_insights')
            || $user->can('update all insights')
            || $user->can('review insights')
            || $user->can('publish insights')
        );
    }
}
