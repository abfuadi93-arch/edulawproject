<?php

namespace App\Filament\Resources\Publications\Pages;

use App\Filament\Resources\Pages\EditRecordAndReturn;
use App\Filament\Resources\Publications\PublicationResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;

class EditPublication extends EditRecordAndReturn
{
    protected static string $resource = PublicationResource::class;

    public function getHeading(): string
    {
        return 'Edit Publikasi';
    }

    public function getSubheading(): ?string
    {
        return 'Kelola identitas, ringkasan, dokumen, dan informasi penerbitan publikasi.';
    }

    public function getBreadcrumbs(): array
    {
        return [PublicationResource::getUrl('index') => 'Publikasi', 'Edit Publikasi'];
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
            Action::make('preview')
                ->label('Pratinjau')
                ->icon('heroicon-o-eye')
                ->url(fn (): ?string => filled($this->record?->slug)
                    ? route('publications.show', $this->record->slug)
                    : null)
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled($this->record?->slug)),

            ActionGroup::make([
                DeleteAction::make()
                    ->label('Hapus Publikasi'),
            ])
                ->label('Lainnya')
                ->icon('heroicon-o-ellipsis-vertical')
                ->color('gray'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['status'] = PublicationResource::normalizeStatusForForm($data['status'] ?? null);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();

        $data['updated_by'] = $user?->id;

        return PublicationResource::prepareFormDataForPersistence($data);
    }
}
