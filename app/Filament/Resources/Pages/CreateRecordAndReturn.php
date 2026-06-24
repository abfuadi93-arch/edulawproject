<?php

namespace App\Filament\Resources\Pages;

use Filament\Resources\Pages\CreateRecord;

abstract class CreateRecordAndReturn extends CreateRecord
{
    public function createAnother(): void
    {
        $this->create();
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?: $this->getResourceUrl();
    }
}
