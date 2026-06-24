<?php

namespace App\Filament\Resources\Pages;

use Filament\Resources\Pages\EditRecord;

abstract class EditRecordAndReturn extends EditRecord
{
    protected function getRedirectUrl(): ?string
    {
        return $this->previousUrl ?: $this->getResourceUrl();
    }
}
