<?php

namespace App\Filament\Resources\SiteSettings\Pages;

use App\Filament\Resources\Pages\EditRecordAndReturn;
use App\Filament\Resources\SiteSettings\SiteSettingResource;
use Filament\Actions\DeleteAction;

class EditSiteSetting extends EditRecordAndReturn
{
    protected static string $resource = SiteSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
