<?php

namespace App\Filament\Resources\ProgramResource\Pages;

use App\Filament\Resources\Pages\EditRecordAndReturn;
use App\Filament\Resources\ProgramResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;

class EditProgram extends EditRecordAndReturn
{
    protected static string $resource = ProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Pratinjau')
                ->icon('heroicon-o-eye')
                ->url(fn (): ?string => filled($this->record?->slug)
                    ? route('programs.show', $this->record->slug)
                    : null)
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled($this->record?->slug)),

            ActionGroup::make([
                DeleteAction::make()
                    ->label('Hapus Program')
                    ->visible(fn (): bool => (bool) auth()->user()?->hasRole('super_admin')),
            ])
                ->label('Lainnya')
                ->icon('heroicon-o-ellipsis-vertical')
                ->color('gray'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['publication_status'] = ProgramResource::normalizePublicationStatusForForm($data['publication_status'] ?? null);
        $data['status'] = ProgramResource::normalizeStatusForForm($data['status'] ?? null);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return ProgramResource::prepareFormDataForPersistence($data);
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
