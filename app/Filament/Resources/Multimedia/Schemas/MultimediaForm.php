<?php

namespace App\Filament\Resources\Multimedia\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MultimediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('type')
                    ->required()
                    ->default('video'),
                Textarea::make('description')
                    ->columnSpanFull(),
                FileUpload::make('thumbnail')
                    ->label('Thumbnail')
                    ->image()
                    ->disk('public')
                    ->directory('multimedia')
                    ->visibility('public')
                    ->imageEditor()
                    ->maxSize(4096)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                TextInput::make('media_url')
                    ->url(),
                TextInput::make('embed_url')
                    ->url(),
                TextInput::make('platform')
                    ->required()
                    ->default('website'),
                TextInput::make('duration'),
                DatePicker::make('published_at'),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
                Toggle::make('featured')
                    ->required(),
                TextInput::make('created_by')
                    ->numeric(),
                TextInput::make('updated_by')
                    ->numeric(),
            ]);
    }
}
