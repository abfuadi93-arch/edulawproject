<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Insights\InsightResource;
use App\Models\Insight;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class InsightsWithoutCoverWidget extends Widget
{
    protected string $view = 'filament.widgets.editorial-quality-list';

    protected int|string|array $columnSpan = [
        'md' => 6,
        'xl' => 6,
    ];

    protected static ?int $sort = -28;

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
                ->whereNull('cover_image')
                ->orWhere('cover_image', ''))
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn (Insight $insight): array => static::mapInsight($insight));
    }

    protected function getViewData(): array
    {
        return [
            'title' => 'Artikel Tanpa Cover',
            'description' => 'Editorial yang memerlukan gambar utama sebelum siap ditampilkan.',
            'icon' => 'heroicon-o-photo',
            'items' => static::items(),
            'emptyMessage' => 'Semua artikel sudah memiliki cover.',
        ];
    }

    private static function mapInsight(Insight $insight): array
    {
        return [
            'title' => $insight->title ?: 'Editorial tanpa judul',
            'status' => $insight->status,
            'statusLabel' => static::statusLabel($insight->status),
            'statusTone' => static::statusTone($insight->status),
            'category' => $insight->category?->name ?: 'Tanpa kategori',
            'updated' => $insight->updated_at?->format('d M Y, H:i') ?: '-',
            'url' => InsightResource::getUrl('edit', ['record' => $insight]),
        ];
    }

    private static function statusLabel(?string $status): string
    {
        return match ($status) {
            'submitted', 'reviewed' => 'Reviewed',
            'published' => 'Published',
            default => 'Draft',
        };
    }

    private static function statusTone(?string $status): string
    {
        return match ($status) {
            'submitted', 'reviewed' => 'warning',
            'published' => 'success',
            default => 'gray',
        };
    }
}
