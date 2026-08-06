<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Insights\InsightResource;
use App\Models\Insight;
use Filament\Widgets\Widget;

class EditorialQueueWidget extends Widget
{
    protected string $view = 'filament.widgets.editorial-queue';

    protected int|string|array $columnSpan = [
        'md' => 6,
        'xl' => 8,
    ];

    protected static ?int $sort = -10;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('view insights');
    }

    protected function getViewData(): array
    {
        $query = InsightResource::getEloquentQuery()
            ->with(['authors', 'category'])
            ->whereIn('status', ['draft', 'submitted', 'editor_assigned', 'in_review', 'revision_requested', 'revised', 'approved', 'rejected', 'reviewed', 'published'])
            ->latest('updated_at');

        if (! InsightResource::canManageEditorialWorkflow()) {
            $query->where('status', 'draft');
        }

        $items = $query->take(5)
            ->get()
            ->map(fn (Insight $insight): array => [
                'title' => $insight->title,
                'author' => $insight->display_author ?: 'Edulaw Project',
                'category' => $insight->display_category ?: 'Editorial',
                'status' => $insight->status->value,
                'statusLabel' => $insight->status->label(),
                'statusTone' => match ($insight->status->color()) {
                    'success' => 'green',
                    'warning' => 'amber',
                    default => 'blue',
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
