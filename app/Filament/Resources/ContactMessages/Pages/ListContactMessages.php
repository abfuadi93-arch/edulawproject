<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Filament\Resources\Concerns\HasEdulawListPage;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListContactMessages extends ListRecords
{
    use HasEdulawListPage;

    protected static string $resource = ContactMessageResource::class;

    protected function getListDescription(): string
    {
        return 'Kelola pesan masuk, respons, dan catatan tindak lanjut.';
    }

    public function getTabs(): array
    {
        return $this->makeStatusTabs([
            'all' => ['label' => 'Semua'],
            'new' => ['label' => 'Baru', 'query' => fn (Builder $query): Builder => $query->where('status', 'new')],
            'read' => ['label' => 'Dibaca', 'query' => fn (Builder $query): Builder => $query->where('status', 'read')],
            'replied' => ['label' => 'Dibalas', 'query' => fn (Builder $query): Builder => $query->where('status', 'replied')],
            'archived' => ['label' => 'Diarsipkan', 'query' => fn (Builder $query): Builder => $query->where('status', 'archived')],
        ]);
    }
}
