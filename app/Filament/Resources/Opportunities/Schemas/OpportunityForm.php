<?php

namespace App\Filament\Resources\Opportunities\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
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
                TextInput::make('organizer')
                    ->label('Penyelenggara')
                    ->maxLength(255),
                Textarea::make('excerpt')
                    ->label('Ringkasan Kurasi')
                    ->rows(3)
                    ->maxLength(500)
                    ->helperText('Ringkas relevansi peluang; detail lengkap tetap dibaca di situs resmi.')
                    ->columnSpanFull(),
                FileUpload::make('poster')
                    ->label('Poster Slide 1')
                    ->image()
                    ->live()
                    ->disk('public')
                    ->directory('opportunities')
                    ->visibility('public')
                    ->imageEditor()
                    ->maxSize(4096)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                Repeater::make('additional_posters')
                    ->label('Poster Tambahan')
                    ->schema([
                        FileUpload::make('image')
                            ->label('Poster')
                            ->image()
                            ->disk('public')
                            ->directory('opportunities')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(4096)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->required(),
                    ])
                    ->defaultItems(0)
                    ->maxItems(9)
                    ->addable(fn ($get): bool => filled($get('poster')))
                    ->addActionLabel('Tambah Poster')
                    ->reorderable(),
                DatePicker::make('deadline'),
                TextInput::make('application_link')
                    ->label('URL Informasi Resmi')
                    ->url(),
                TextInput::make('format'),
                TextInput::make('location'),
                Textarea::make('eligibility')
                    ->label('Target Peserta')
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required()
                    ->default('open'),
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
