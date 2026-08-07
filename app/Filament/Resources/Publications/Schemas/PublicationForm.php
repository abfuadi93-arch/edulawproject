<?php

namespace App\Filament\Resources\Publications\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PublicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('publication_type_id')
                    ->numeric(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                FileUpload::make('pdf_file')
                    ->label('PDF File')
                    ->disk('public')
                    ->directory('publications/pdfs')
                    ->visibility('public')
                    ->maxSize(20480)
                    ->acceptedFileTypes(['application/pdf']),
                TextInput::make('external_url')
                    ->url(),
                TextInput::make('source_name'),
                DatePicker::make('published_at'),
                TextInput::make('page_count')
                    ->numeric(),
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'reviewed' => 'Reviewed',
                        'published' => 'Published',
                    ])
                    ->required()
                    ->default('draft'),
                Toggle::make('featured')
                    ->required(),
                TextInput::make('seo_title')
                    ->maxLength(300)
                    ->helperText('Target 45–65 karakter. Nama situs ditambahkan otomatis.'),
                Textarea::make('seo_description')
                    ->columnSpanFull()
                    ->helperText('Target 120–160 karakter. Jelaskan manfaat dan topik utama secara alami.'),
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
