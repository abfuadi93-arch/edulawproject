<?php

namespace App\Filament\Resources\Multimedia\Tables;

use App\Filament\Resources\Multimedia\MultimediaResource;
use Filament\Tables\Table;

class MultimediaTable
{
    public static function configure(Table $table): Table
    {
        return MultimediaResource::table($table);
    }
}
