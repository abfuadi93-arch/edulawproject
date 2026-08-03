<?php

namespace App\Filament\Resources\Multimedia\Pages;

use App\Filament\Resources\Concerns\HasEdulawResourceList;
use App\Filament\Resources\Multimedia\MultimediaResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListMultimedia extends ListRecords
{
    use HasEdulawResourceList;

    protected static string $resource = MultimediaResource::class;

    protected function getListDescription(): string
    {
        return 'Kelola video, reels, shorts, album foto, dan konten multimedia.';
    }

    protected function getCreateButtonLabel(): string
    {
        return 'Tambah Multimedia';
    }

    public function getTabs(): array
    {
        return $this->makeStatusTabs([
            'all' => ['label' => 'Semua'],
            'draft' => [
                'label' => 'Draft',
                'query' => fn (Builder $query): Builder => $query->where('status', 'draft'),
            ],
            'published' => [
                'label' => 'Dipublikasikan',
                'query' => fn (Builder $query): Builder => $query->where('status', 'published'),
            ],
            'archived' => [
                'label' => 'Diarsipkan',
                'query' => fn (Builder $query): Builder => $query->where('status', 'archived'),
            ],
        ]);
    }
}
