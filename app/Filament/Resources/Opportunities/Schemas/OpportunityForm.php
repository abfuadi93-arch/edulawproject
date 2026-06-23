<?php

namespace App\Filament\Resources\Opportunities\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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
                Textarea::make('excerpt')
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('poster'),
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
                TextInput::make('seo_title'),
                Textarea::make('seo_description')
                    ->columnSpanFull(),
                FileUpload::make('og_image')
                    ->image(),
                TextInput::make('created_by')
                    ->numeric(),
                TextInput::make('updated_by')
                    ->numeric(),
            ]);
    }
}
