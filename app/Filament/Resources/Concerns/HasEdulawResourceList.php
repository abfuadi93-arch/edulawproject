<?php

namespace App\Filament\Resources\Concerns;

use Filament\Actions\CreateAction;

trait HasEdulawResourceList
{
    use HasEdulawListPage;

    abstract protected function getCreateButtonLabel(): string;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label($this->getCreateButtonLabel())
                ->icon('heroicon-o-plus'),
        ];
    }
}
