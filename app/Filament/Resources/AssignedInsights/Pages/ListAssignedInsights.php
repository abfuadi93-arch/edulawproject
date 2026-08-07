<?php

namespace App\Filament\Resources\AssignedInsights\Pages;

use App\Filament\Resources\AssignedInsights\AssignedInsightResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAssignedInsights extends ListRecords
{
    protected static string $resource = AssignedInsightResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua'),
            'draft' => Tab::make('Draft')->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'draft')),
            'review' => Tab::make('Review')->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'review')),
            'published' => Tab::make('Published')->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'published')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
