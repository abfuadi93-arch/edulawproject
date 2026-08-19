<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Editorial\EditorialResource;
use App\Models\Insight;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class EditorialPipelineWidget extends Widget
{
    protected string $view = 'filament.widgets.editorial-pipeline';

    protected int|string|array $columnSpan = [
        'md' => 6,
        'xl' => 8,
    ];

    protected static ?int $sort = -70;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        $user = auth()->user();

        return (bool) $user && (
            $user->hasAnyRole(['super_admin', 'Super Admin', 'SuperAdmin'])
            || $user->can('view_all_editorial_insights')
            || $user->can('update all insights')
        );
    }

    protected function getViewData(): array
    {
        $counts = Cache::remember('dashboard.editorial-pipeline.v1', now()->addMinutes(3), fn (): array => [
            'draft' => Insight::query()->where('status', 'draft')->count(),
            'review' => Insight::query()->where('status', 'review')->count(),
            'unassigned' => Insight::query()
                ->where('status', 'review')
                ->whereNull('assigned_editor_id')
                ->count(),
            'overdue' => Insight::query()
                ->whereIn('status', ['draft', 'review'])
                ->where(function ($query): void {
                    $query
                        ->where(fn ($deadline) => $deadline
                            ->whereNotNull('editor_deadline')
                            ->where('editor_deadline', '<', now()))
                        ->orWhere(fn ($deadline) => $deadline
                            ->whereNotNull('writer_deadline')
                            ->where('writer_deadline', '<', now()));
                })
                ->count(),
        ]);

        $items = Insight::query()
            ->with('assignedEditor')
            ->whereIn('status', ['draft', 'review'])
            ->latest('updated_at')
            ->take(5)
            ->get()
            ->map(function (Insight $insight): array {
                $status = $insight->status->canonical();
                $deadline = $this->nearestDeadline($insight);

                return [
                    'id' => $insight->getKey(),
                    'title' => $insight->title ?: 'Tanpa judul',
                    'status' => $status->label(),
                    'statusColor' => $status->color(),
                    'editor' => $insight->assignedEditor?->name ?: 'Belum ditugaskan',
                    'deadline' => $deadline?->translatedFormat('d M Y') ?: 'Tanpa deadline',
                    'overdue' => $deadline?->isPast() ?? false,
                    'url' => EditorialResource::getUrl('workspace', ['record' => $insight]),
                ];
            });

        return [
            'counts' => $counts,
            'items' => $items,
            'workspaceUrl' => EditorialResource::getUrl('index'),
        ];
    }

    private function nearestDeadline(Insight $insight): ?Carbon
    {
        return collect([
            $insight->getAttribute('editor_deadline'),
            $insight->getAttribute('writer_deadline'),
        ])
            ->filter()
            ->map(fn (mixed $deadline): Carbon => Carbon::parse($deadline))
            ->sort()
            ->first();
    }
}
