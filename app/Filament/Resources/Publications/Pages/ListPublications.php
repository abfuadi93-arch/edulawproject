<?php

namespace App\Filament\Resources\Publications\Pages;

use App\Filament\Resources\Concerns\HasEdulawResourceList;
use App\Filament\Resources\Publications\PublicationResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPublications extends ListRecords
{
    use HasEdulawResourceList;

    protected static string $resource = PublicationResource::class;

    protected function getListDescription(): string
    {
        return 'Kelola riset, publikasi, dokumen, dan metadata penerbitan.';
    }

    protected function getCreateButtonLabel(): string
    {
        return 'Tambah Publikasi';
    }

    public function getTabs(): array
    {
        return $this->makeStatusTabs([
            'all' => ['label' => 'Semua'],
            'draft' => [
                'label' => 'Draft',
                'query' => fn (Builder $query): Builder => $query->whereIn('status', ['draft', 'reviewed']),
            ],
            'published' => [
                'label' => 'Published',
                'query' => fn (Builder $query): Builder => $query->where('status', 'published'),
            ],
            'archived' => [
                'label' => 'Diarsipkan',
                'query' => fn (Builder $query): Builder => $query->where('status', 'archived'),
            ],
        ]);
    }
}
