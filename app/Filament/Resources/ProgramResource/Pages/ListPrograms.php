<?php

namespace App\Filament\Resources\ProgramResource\Pages;

use App\Filament\Resources\Concerns\HasEdulawResourceList;
use App\Filament\Resources\ProgramResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPrograms extends ListRecords
{
    use HasEdulawResourceList;

    protected static string $resource = ProgramResource::class;

    protected function getListDescription(): string
    {
        return 'Kelola program, jadwal kegiatan, narasumber, dan pendaftaran.';
    }

    protected function getCreateButtonLabel(): string
    {
        return 'Tambah Program';
    }

    public function getTabs(): array
    {
        return $this->makeStatusTabs([
            'all' => ['label' => 'Semua'],
            'upcoming' => [
                'label' => 'Akan Datang',
                'query' => fn (Builder $query): Builder => $query->where('status', 'upcoming'),
            ],
            'ongoing' => [
                'label' => 'Berlangsung',
                'query' => fn (Builder $query): Builder => $query->where('status', 'ongoing'),
            ],
            'archived' => [
                'label' => 'Diarsipkan',
                'query' => fn (Builder $query): Builder => $query->whereIn('status', ['archived', 'completed', 'portfolio']),
            ],
        ]);
    }
}
