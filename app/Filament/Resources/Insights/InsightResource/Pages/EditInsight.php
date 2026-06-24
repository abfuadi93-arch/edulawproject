<?php

namespace App\Filament\Resources\Insights\InsightResource\Pages;

use App\Filament\Resources\Insights\InsightResource;
use App\Filament\Resources\Pages\EditRecordAndReturn;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;

class EditInsight extends EditRecordAndReturn
{
    protected static string $resource = InsightResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Pratinjau')
                ->icon('heroicon-o-eye')
                ->url(fn (): ?string => filled($this->record?->slug)
                    ? route('insights.show', $this->record->slug)
                    : null)
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled($this->record?->slug)),

            DeleteAction::make(),
        ];
    }
}
