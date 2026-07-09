<?php

namespace App\Filament\Resources\Opportunities\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OpportunityForm
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
                    ->default('open_collaboration'),
                Textarea::make('description')
                    ->columnSpanFull(),
                FileUpload::make('poster')
                    ->label('Poster')
                    ->image()
                    ->disk('public')
                    ->directory('opportunities')
                    ->visibility('public')
                    ->imageEditor()
                    ->maxSize(4096)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                DatePicker::make('deadline'),
                TextInput::make('application_link'),
                TextInput::make('format'),
                TextInput::make('location'),
                Textarea::make('eligibility')
                    ->columnSpanFull(),
                Textarea::make('benefits')
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required()
                    ->default('open'),
                Toggle::make('featured')
                    ->required(),
                TextInput::make('seo_title')
                    ->maxLength(300)
                    ->helperText('Maks. 300 karakter.'),
                Textarea::make('seo_description')
                    ->columnSpanFull(),
                FileUpload::make('og_image')
                    ->label('OG Image')
                    ->image()
                    ->disk('public')
                    ->directory('seo/og-images')
                    ->visibility('public')
                    ->imageEditor()
                    ->maxSize(4096)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                TextInput::make('created_by')
                    ->numeric(),
                TextInput::make('updated_by')
                    ->numeric(),
            ]);
    }
}
