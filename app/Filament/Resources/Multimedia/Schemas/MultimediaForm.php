<?php

namespace App\Filament\Resources\Multimedia\Schemas;

use App\Filament\Resources\Multimedia\MultimediaResource;
use Filament\Schemas\Schema;

class MultimediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return MultimediaResource::form($schema);
    }
}
