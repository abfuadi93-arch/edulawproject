<?php

namespace App\Filament\Resources\AssignedInsights\Pages;

use App\Filament\Resources\AssignedInsights\AssignedInsightResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListAssignedInsights extends ListRecords
{
    protected static string $resource = AssignedInsightResource::class;

    public function getTabs(): array
    {
        return [
            'new' => Tab::make('Penugasan Baru')->modifyQueryUsing(fn (Builder $query): Builder => $this->forCurrentEditor($query, ['assigned', 'accepted'])),
            'in_review' => Tab::make('Sedang Direview')->modifyQueryUsing(fn (Builder $query): Builder => $this->forCurrentEditor($query->where('status', 'in_review'), ['active'])),
            'waiting_writer' => Tab::make('Menunggu Penulis')->modifyQueryUsing(fn (Builder $query): Builder => $this->forCurrentEditor($query->where('status', 'revision_requested'), ['active'])),
            'revised' => Tab::make('Revisi Masuk')->modifyQueryUsing(fn (Builder $query): Builder => $this->forCurrentEditor($query->where('status', 'revised'), ['active'])),
            'approved' => Tab::make('Disetujui')->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'approved')->whereHas('editorAssignments', fn (Builder $query): Builder => $query->where('editor_id', Auth::id()))),
            'done' => Tab::make('Selesai')->modifyQueryUsing(fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                $query->whereIn('status', ['published', 'rejected', 'archived'])
                    ->orWhereHas('editorAssignments', fn (Builder $query): Builder => $query->where('editor_id', Auth::id())->whereIn('status', ['completed', 'reassigned', 'cancelled']));
            })),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    private function forCurrentEditor(Builder $query, array $assignmentStatuses): Builder
    {
        return $query->whereHas('editorAssignments', fn (Builder $query): Builder => $query
            ->where('editor_id', Auth::id())
            ->whereIn('status', $assignmentStatuses));
    }
}
