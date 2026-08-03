<?php

namespace App\Filament\Resources\Multimedia\Pages;

use App\Filament\Resources\Multimedia\MultimediaResource;
use App\Filament\Resources\Pages\EditRecordAndReturn;
use App\Support\MultimediaThumbnail;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;

class EditMultimedia extends EditRecordAndReturn
{
    protected static string $resource = MultimediaResource::class;

    protected function afterSave(): void
    {
        MultimediaThumbnail::importFromSource($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewOnPlatform')
                ->label('Lihat di Platform')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn (): ?string => $this->record?->media_url)
                ->openUrlInNewTab()
                ->tooltip('Buka konten pada platform asal.')
                ->visible(fn (): bool => filled($this->record?->media_url)),

            DeleteAction::make()->label('Hapus')->requiresConfirmation(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['type'] = match ($data['type'] ?? null) {
            'shorts' => 'reels',
            'documentation' => 'gallery',
            default => $data['type'] ?? 'video',
        };

        if ($data['type'] === 'gallery' && in_array($data['platform'] ?? null, ['website', 'other'], true)) {
            $data['platform'] = 'google_photos';
        }

        return $data;
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->label('Simpan Perubahan');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Batal');
    }
}
