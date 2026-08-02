<?php

namespace App\Filament\Resources\CollaborationSubmissions\Pages;

use App\Filament\Resources\CollaborationSubmissions\CollaborationSubmissionResource;
use App\Filament\Resources\Concerns\HasEdulawListPage;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCollaborationSubmissions extends ListRecords
{
    use HasEdulawListPage;

    protected static string $resource = CollaborationSubmissionResource::class;

    protected function getListDescription(): string
    {
        return 'Kelola pengajuan kerja sama, tindak lanjut, dan catatan internal.';
    }

    public function getTabs(): array
    {
        return $this->makeStatusTabs([
            'all' => ['label' => 'Semua'],
            'new' => ['label' => 'Baru', 'query' => fn (Builder $query): Builder => $query->where('status', 'new')],
            'reviewed' => ['label' => 'Ditinjau', 'query' => fn (Builder $query): Builder => $query->where('status', 'reviewed')],
            'followed_up' => ['label' => 'Ditindaklanjuti', 'query' => fn (Builder $query): Builder => $query->where('status', 'followed_up')],
            'accepted' => ['label' => 'Diterima', 'query' => fn (Builder $query): Builder => $query->where('status', 'accepted')],
            'rejected' => ['label' => 'Ditolak', 'query' => fn (Builder $query): Builder => $query->where('status', 'rejected')],
            'archived' => ['label' => 'Diarsipkan', 'query' => fn (Builder $query): Builder => $query->where('status', 'archived')],
        ]);
    }
}
