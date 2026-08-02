<?php

namespace App\Filament\Resources\Opportunities\Pages;

use App\Filament\Resources\Concerns\HasEdulawResourceList;
use App\Filament\Resources\Opportunities\OpportunityResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOpportunities extends ListRecords
{
    use HasEdulawResourceList;

    protected static string $resource = OpportunityResource::class;

    protected function getListDescription(): string
    {
        return 'Kelola peluang, tenggat pendaftaran, dan informasi aplikasi.';
    }

    protected function getCreateButtonLabel(): string
    {
        return 'Tambah Peluang';
    }

    public function getTabs(): array
    {
        return $this->makeStatusTabs([
            'all' => ['label' => 'Semua'],
            'open' => [
                'label' => 'Dibuka',
                'query' => fn (Builder $query): Builder => $query->where('status', 'open'),
            ],
            'closed' => [
                'label' => 'Ditutup',
                'query' => fn (Builder $query): Builder => $query->where('status', 'closed'),
            ],
            'archived' => [
                'label' => 'Diarsipkan',
                'query' => fn (Builder $query): Builder => $query->where('status', 'archived'),
            ],
        ]);
    }
}
