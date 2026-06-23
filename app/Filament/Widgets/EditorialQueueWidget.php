<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Insights\InsightResource;
use App\Models\Insight;
use Filament\Widgets\Widget;

class EditorialQueueWidget extends Widget
{
    protected string $view = 'filament.widgets.editorial-queue';

    protected int | string | array $columnSpan = [
        'md' => 6,
        'xl' => 6,
    ];

    protected static ?int $sort = 10;

    protected function getViewData(): array
    {
        $items = Insight::query()
            ->with(['authors', 'category'])
            ->whereIn('status', ['draft', 'submitted', 'reviewed'])
            ->latest('updated_at')
            ->take(5)
            ->get()
            ->map(fn (Insight $insight): array => [
                'title' => $insight->title,
                'meta' => trim(($insight->display_author ?: 'Edulaw Project').' - '.($insight->category?->name ?: 'Belum dikategorikan'), ' -'),
                'status' => $insight->status,
                'statusLabel' => match ($insight->status) {
                    'submitted' => 'Dalam Review',
                    'reviewed' => 'Siap Terbit',
                    default => 'Draft',
                },
                'updated' => $insight->updated_at?->diffForHumans(),
                'url' => InsightResource::getUrl('edit', ['record' => $insight->getKey()]),
            ]);

        return [
            'items' => $items,
            'count' => $items->count(),
            'indexUrl' => InsightResource::getUrl('index'),
        ];
    }
}
