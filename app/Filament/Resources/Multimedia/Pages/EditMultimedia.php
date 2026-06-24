<?php

namespace App\Filament\Resources\Multimedia\Pages;

use App\Filament\Resources\Multimedia\MultimediaResource;
use App\Filament\Resources\Pages\EditRecordAndReturn;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;

class EditMultimedia extends EditRecordAndReturn
{
    protected static string $resource = MultimediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Pratinjau')
                ->icon('heroicon-o-eye')
                ->url(fn (): string => route('multimedia.index', [
                    'q' => $this->record?->title,
                ]))
                ->openUrlInNewTab(),

            DeleteAction::make(),
        ];
    }
}
