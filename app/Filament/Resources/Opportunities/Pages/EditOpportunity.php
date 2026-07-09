<?php

namespace App\Filament\Resources\Opportunities\Pages;

use App\Filament\Resources\Opportunities\OpportunityResource;
use App\Filament\Resources\Pages\EditRecordAndReturn;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;

class EditOpportunity extends EditRecordAndReturn
{
    protected static string $resource = OpportunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Pratinjau')
                ->icon('heroicon-o-eye')
                ->url(fn (): string => route('opportunities.index', [
                    'q' => $this->record?->title,
                ]))
                ->openUrlInNewTab(),

            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['status'] = OpportunityResource::normalizeStatusForForm($data['status'] ?? null);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return OpportunityResource::prepareFormDataForPersistence($data);
    }
}
