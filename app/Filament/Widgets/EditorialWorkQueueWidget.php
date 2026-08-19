<?php

namespace App\Filament\Widgets;

use App\Enums\InsightStatus;
use App\Filament\Resources\AssignedInsights\AssignedInsightResource;
use App\Filament\Resources\Editorial\EditorialResource;
use App\Filament\Resources\Insights\InsightResource;
use App\Models\Insight;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class EditorialWorkQueueWidget extends Widget
{
    protected string $view = 'filament.widgets.editorial-work-queue';

    protected int|string|array $columnSpan = [
        'md' => 6,
        'xl' => 12,
    ];

    protected static ?int $sort = -70;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        $user = auth()->user();

        return (bool) $user
            && ! $user->hasAnyRole(['super_admin', 'Super Admin', 'SuperAdmin'])
            && (static::canWrite($user) || $user->canAccessAssignedEditorialInsights());
    }

    protected function getViewData(): array
    {
        /** @var User $user */
        $user = auth()->user();
        $panels = collect();

        if (static::canWrite($user)) {
            $panels->push($this->writtenPanel($user));
        }

        if ($user->canAccessAssignedEditorialInsights()) {
            $panels->push($this->assignedPanel($user));
        }

        return ['panels' => $panels];
    }

    private function writtenPanel(User $user): array
    {
        $query = Insight::query()->where('created_by', $user->getKey());

        return [
            'key' => 'written',
            'title' => 'Tulisan Saya',
            'description' => 'Naskah yang Anda buat sebagai writer.',
            'count' => (clone $query)->count(),
            'icon' => 'heroicon-o-pencil-square',
            'color' => 'primary',
            'url' => InsightResource::getUrl('index'),
            'action' => 'Lihat semua tulisan',
            'empty' => 'Anda belum membuat tulisan.',
            'items' => $this->mapWrittenItems(
                $query->latest('updated_at')->take(5)->get(),
                $user,
            ),
        ];
    }

    private function assignedPanel(User $user): array
    {
        $query = Insight::query()->where('assigned_editor_id', $user->getKey());

        return [
            'key' => 'assigned',
            'title' => 'Tugas Editor',
            'description' => 'Naskah yang ditugaskan kepada Anda untuk ditinjau.',
            'count' => (clone $query)->count(),
            'icon' => 'heroicon-o-inbox-stack',
            'color' => 'warning',
            'url' => AssignedInsightResource::getUrl('index'),
            'action' => 'Lihat semua tugas',
            'empty' => 'Belum ada naskah yang ditugaskan kepada Anda.',
            'items' => $this->mapAssignedItems(
                $query->latest('updated_at')->take(5)->get(),
            ),
        ];
    }

    private function mapWrittenItems(Collection $insights, User $user): Collection
    {
        return $insights->map(function (Insight $insight) use ($user): array {
            $status = $insight->status->canonical();
            $url = $user->can('update', $insight)
                ? InsightResource::getUrl('edit', ['record' => $insight])
                : ($status === InsightStatus::Published && filled($insight->slug)
                    ? route('insights.show', $insight->slug)
                    : InsightResource::getUrl('index'));

            return $this->item($insight, $url);
        });
    }

    private function mapAssignedItems(Collection $insights): Collection
    {
        return $insights->map(fn (Insight $insight): array => $this->item(
            $insight,
            EditorialResource::getUrl('workspace', ['record' => $insight]),
        ));
    }

    private function item(Insight $insight, string $url): array
    {
        $status = $insight->status->canonical();

        return [
            'id' => $insight->getKey(),
            'title' => $insight->title ?: 'Tanpa judul',
            'status' => $status->label(),
            'statusColor' => $status->color(),
            'updated' => $insight->updated_at?->diffForHumans() ?? '—',
            'url' => $url,
        ];
    }

    private static function canWrite(User $user): bool
    {
        return $user->hasAnyRole(['writer', 'Writer']) || $user->can('create insights');
    }
}
