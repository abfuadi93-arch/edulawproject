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
                ->hidden(fn (Author $record): bool => filled($record->user_id)
                    || $record->insights()->exists()
                    || $record->publications()->exists()),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return AuthorResource::prepareFormDataForPersistence($data, $this->record?->id);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['social_links'] = $this->record->socialLinksMap();
        $data['profile_type'] = Author::canonicalProfileType($data['profile_type'] ?? null) ?? 'team';

        return $data;
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
