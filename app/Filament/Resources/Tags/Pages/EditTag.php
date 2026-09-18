<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Resources\Pages\EditRecordAndReturn;
use App\Filament\Resources\Tags\TagResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;

class EditTag extends EditRecordAndReturn
{
    protected static string $resource = TagResource::class;

    public function getSubheading(): ?string
    {
        return 'Perbarui nama dan identitas tag untuk pengelompokan artikel dan publikasi.';
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->label('Simpan Perubahan');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Batal');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus Tag')
                ->visible(fn (): bool => ! $this->getRecord()->insights()->exists() && ! $this->getRecord()->publications()->exists()),
        ];
    }
}
