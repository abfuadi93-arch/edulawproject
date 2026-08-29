<?php

namespace App\Filament\Resources\Authors\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AuthorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name'),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                Textarea::make('bio')
                    ->label('Bio Profil')
                    ->rows(6)
                    ->maxLength(1000)
                    ->helperText('Maksimal 1000 karakter.')
                    ->columnSpanFull(),
                FileUpload::make('photo')
                    ->label('Photo')
                    ->image()
                    ->disk('public')
                    ->directory('authors')
                    ->visibility('public')
                    ->imageEditor()
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                TextInput::make('institution'),
                TextInput::make('position'),
                DatePicker::make('joined_at')
                    ->label('Bergabung sejak')
                    ->native(false)
                    ->helperText('Opsional. Kosongkan jika tanggal bergabung tidak dapat diverifikasi.'),
                Textarea::make('social_links')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
