<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Editorial\EditorialResource;
use App\Models\Insight;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EditorialReviewOverviewWidget extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Kontrol Review Editorial';

    protected ?string $description = 'Penugasan, tenggat, komentar, dan kesiapan publikasi.';

    protected static ?int $sort = -28;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    protected function getColumns(): int|array|null
    {
        return ['sm' => 2, 'lg' => 4, 'xl' => 7];
    }

    protected function getStats(): array
    {
        $url = EditorialResource::getUrl('index');
        $activeStatuses = ['editor_assigned', 'in_review', 'revised'];
        $writerStatuses = ['revision_requested'];

        return [
            Stat::make('Belum Ditugaskan', Insight::query()->where('status', 'submitted')->whereDoesntHave('editorAssignments', fn ($query) => $query->active())->count())->color('warning')->url($url),
            Stat::make('Deadline Editor Dekat', Insight::query()->whereIn('status', $activeStatuses)->whereNull('editor_deadline_completed_at')->whereBetween('editor_deadline', [now(), now()->addDay()])->count())->color('warning')->url($url),
            Stat::make('Review Overdue', Insight::query()->whereIn('status', $activeStatuses)->whereNull('editor_deadline_completed_at')->where('editor_deadline', '<', now())->count())->color('danger')->url($url),
            Stat::make('Deadline Writer Dekat', Insight::query()->whereIn('status', $writerStatuses)->whereNull('writer_deadline_completed_at')->whereBetween('writer_deadline', [now(), now()->addDay()])->count())->color('warning')->url($url),
            Stat::make('Revisi Overdue', Insight::query()->whereIn('status', $writerStatuses)->whereNull('writer_deadline_completed_at')->where('writer_deadline', '<', now())->count())->color('danger')->url($url),
            Stat::make('Menunggu Approval', Insight::query()->where('status', 'in_review')->count())->color('info')->url($url),
            Stat::make('Siap Terbit', Insight::query()->where('status', 'approved')->count())->color('success')->url($url),
        ];
    }
}
