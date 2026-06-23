<?php

namespace App\Filament\Resources\CollaborationSubmissions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CollaborationSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('institution'),
                TextInput::make('position'),
                TextInput::make('collaboration_type'),
                TextInput::make('subject'),
                Textarea::make('message')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('attachment'),
                TextInput::make('status')
                    ->required()
                    ->default('new'),
                Textarea::make('internal_notes')
                    ->columnSpanFull(),
                TextInput::make('handled_by')
                    ->numeric(),
                DateTimePicker::make('handled_at'),
            ]);
    }
}
