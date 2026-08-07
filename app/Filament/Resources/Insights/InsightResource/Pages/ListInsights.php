<?php

namespace App\Filament\Resources\Insights\InsightResource\Pages;

use App\Filament\Resources\Concerns\HasEdulawResourceList;
use App\Filament\Resources\Insights\InsightResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListInsights extends ListRecords
{
    use HasEdulawResourceList;

    protected static string $resource = InsightResource::class;

    protected function getListDescription(): string
    {
        return 'Kelola naskah dengan alur sederhana: Draft, Review, dan Published.';
    }

    protected function getCreateButtonLabel(): string
    {
        return 'Tambah Artikel';
    }

    public function getTabs(): array
    {
        return $this->makeStatusTabs([
            'all' => ['label' => 'Semua'],
            'draft' => [
                'label' => 'Draft',
                'query' => fn (Builder $query): Builder => $query->where('status', 'draft'),
            ],
            'review' => [
                'label' => 'Review',
                'query' => fn (Builder $query): Builder => $query->where('status', 'review'),
            ],
            'published' => [
                'label' => 'Published',
                'query' => fn (Builder $query): Builder => $query->where('status', 'published'),
            ],
        ]);
    }
}
