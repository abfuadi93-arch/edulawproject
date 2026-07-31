<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Insights\InsightResource;
use App\Models\Insight;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class InsightsWithoutExcerptWidget extends Widget
{
    protected string $view = 'filament.widgets.editorial-quality-list';

    protected int|string|array $columnSpan = [
        'md' => 6,
        'xl' => 6,
    ];

    protected static ?int $sort = -27;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return EditorialStatusOverview::canView();
    }

    public static function items(): Collection
    {
        return Insight::query()
            ->with('category')
            ->whereIn('status', ['draft', 'submitted', 'reviewed', 'published'])
            ->where(fn ($query) => $query
                ->whereNull('excerpt')
                ->orWhere('excerpt', ''))
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn (Insight $insight): array => [
                'title' => $insight->title ?: 'Editorial tanpa judul',
                'status' => $insight->status,
                'statusLabel' => match ($insight->status) {
                    'submitted', 'reviewed' => 'Reviewed',
                    'published' => 'Published',
                    default => 'Draft',
                },
                'statusTone' => match ($insight->status) {
                    'submitted', 'reviewed' => 'warning',
                    'published' => 'success',
                    default => 'gray',
                },
                'category' => $insight->category?->name ?: 'Tanpa kategori',
                'updated' => $insight->updated_at?->format('d M Y, H:i') ?: '-',
                'url' => InsightResource::getUrl('edit', ['record' => $insight]),
            ]);
    }

    protected function getViewData(): array
    {
        return [
            'title' => 'Artikel Tanpa Ringkasan',
            'description' => 'Editorial yang belum memiliki excerpt untuk card dan metadata.',
            'icon' => 'heroicon-o-bars-3-bottom-left',
            'items' => static::items(),
            'emptyMessage' => 'Semua artikel sudah memiliki ringkasan.',
        ];
    }
}
