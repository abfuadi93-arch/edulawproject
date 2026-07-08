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

        if (($data['status'] ?? null) === 'published' && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        return PublicationResource::prepareFormDataForPersistence($data);
    }
}
