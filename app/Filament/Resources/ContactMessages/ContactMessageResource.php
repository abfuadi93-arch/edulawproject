<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages\CreateContactMessage;
use App\Filament\Resources\ContactMessages\Pages\EditContactMessage;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Interaksi';

    protected static ?string $navigationLabel = 'Pesan Kontak';

    protected static ?string $modelLabel = 'Pesan Kontak';

    protected static ?string $pluralModelLabel = 'Pesan Kontak';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'xl' => 12,
                ])
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make('Identitas Pengirim')
                                    ->icon('heroicon-o-user-circle')
                                    ->description('Data ini berasal dari formulir kontak publik.')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nama')
                                            ->disabled()
                                            ->dehydrated(false),

                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->disabled()
                                            ->dehydrated(false),

                                        TextInput::make('phone')
                                            ->label('Nomor Telepon')
                                            ->disabled()
                                            ->dehydrated(false),
                                    ])
                                    ->columns(2),

                                Section::make('Isi Pesan')
                                    ->icon('heroicon-o-envelope')
                                    ->description('Isi pesan dari formulir kontak publik.')
                                    ->schema([
                                        TextInput::make('subject')
                                            ->label('Subjek')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->columnSpanFull(),

                                        Textarea::make('message')
                                            ->label('Pesan')
                                            ->rows(8)
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1),
                            ])
                            ->columnSpan(['xl' => 8])
                            ->extraAttributes(['class' => 'edulaw-admin-main-column']),

                        Group::make()
                            ->schema([
                                Section::make('Tindak Lanjut Admin')
                                    ->icon('heroicon-o-clipboard-document-check')
                                    ->description('Bagian ini digunakan untuk mencatat tindak lanjut pesan kontak.')
                                    ->schema([
                                        Select::make('status')
                                            ->label('Status')
                                            ->options([
                                                'new' => 'Baru',
                                                'read' => 'Dibaca',
                                                'replied' => 'Dibalas',
                                                'archived' => 'Diarsipkan',
                                            ])
                                            ->default('new')
                                            ->required(),

                                        Select::make('handled_by')
                                            ->label('Ditangani Oleh')
                                            ->relationship('handler', 'name')
                                            ->searchable()
                                            ->preload(),

                                        DateTimePicker::make('handled_at')
                                            ->label('Waktu Ditangani')
                                            ->seconds(false),

                                        Textarea::make('internal_notes')
                                            ->label('Catatan Internal')
                                            ->rows(7)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1),
                            ])
                            ->columnSpan(['xl' => 4])
                            ->extraAttributes(['class' => 'edulaw-admin-side-column']),
                    ])
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'edulaw-admin-edit-shell']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('subject')
                    ->label('Subjek')
                    ->searchable()
                    ->limit(45),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'new' => 'Baru',
                        'read' => 'Dibaca',
                        'replied' => 'Dibalas',
                        'archived' => 'Diarsipkan',
                        default => $state ? ucfirst($state) : '-',
                    }),

                TextColumn::make('handler.name')
                    ->label('Handler')
                    ->toggleable(),

                TextColumn::make('handled_at')
                    ->label('Ditangani')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Masuk')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'new' => 'Baru',
                        'read' => 'Dibaca',
                        'replied' => 'Dibalas',
                        'archived' => 'Diarsipkan',
                    ]),
            ])
            ->recordActions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactMessages::route('/'),
            'create' => CreateContactMessage::route('/create'),
            'edit' => EditContactMessage::route('/{record}/edit'),
        ];
    }
}
