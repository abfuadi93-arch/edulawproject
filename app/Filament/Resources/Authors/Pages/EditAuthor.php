<?php

namespace App\Filament\Resources\Authors\Pages;

use App\Filament\Resources\Authors\AuthorResource;
use App\Filament\Resources\Pages\EditRecordAndReturn;
use App\Models\Author;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;

class EditAuthor extends EditRecordAndReturn
{
    protected static string $resource = AuthorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus Profil')
                ->hidden(fn (Author $record): bool => filled($record->user_id)),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return AuthorResource::prepareFormDataForPersistence($data, $this->record?->id);
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Simpan Perubahan');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }
}
