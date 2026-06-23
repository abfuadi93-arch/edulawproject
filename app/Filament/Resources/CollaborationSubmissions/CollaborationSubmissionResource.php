<?php

namespace App\Filament\Resources\CollaborationSubmissions;

use App\Filament\Resources\CollaborationSubmissions\Pages\CreateCollaborationSubmission;
use App\Filament\Resources\CollaborationSubmissions\Pages\EditCollaborationSubmission;
use App\Filament\Resources\CollaborationSubmissions\Pages\ListCollaborationSubmissions;
use App\Models\CollaborationSubmission;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CollaborationSubmissionResource extends Resource
{
    protected static ?string $model = CollaborationSubmission::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Interaksi';

    protected static ?string $navigationLabel = 'Pengajuan Kolaborasi';

    protected static ?string $modelLabel = 'Pengajuan Kolaborasi';

    protected static ?string $pluralModelLabel = 'Pengajuan Kolaborasi';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-hand-raised';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Pengirim')
                    ->description('Data ini berasal dari formulir pengajuan kolaborasi.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->disabled(),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->disabled(),

                        TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->disabled(),

                        TextInput::make('institution')
                            ->label('Institusi')
                            ->disabled(),

                        TextInput::make('position')
                            ->label('Posisi')
                            ->disabled(),

                        TextInput::make('collaboration_type')
                            ->label('Jenis Kolaborasi')
                            ->disabled(),
                    ])
                    ->columns(1),

                Section::make('Isi Pengajuan')
                    ->schema([
                        TextInput::make('subject')
                            ->label('Subjek')
                            ->disabled()
                            ->columnSpanFull(),

                        Textarea::make('message')
                            ->label('Pesan')
                            ->rows(8)
                            ->disabled()
                            ->columnSpanFull(),

                        FileUpload::make('attachment')
                            ->label('Lampiran')
                            ->directory('collaboration-submissions')
                            ->disabled()
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Tindak Lanjut Admin')
                    ->description('Bagian ini digunakan untuk mencatat proses tindak lanjut internal.')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'new' => 'Baru',
                                'reviewed' => 'Ditinjau',
                                'followed_up' => 'Ditindaklanjuti',
                                'accepted' => 'Diterima',
                                'rejected' => 'Ditolak',
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
                            ->rows(5)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->description(fn (CollaborationSubmission $record): ?string => $record->institution),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('collaboration_type')
                    ->label('Jenis')
                    ->badge()
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
                        'reviewed' => 'Ditinjau',
                        'followed_up' => 'Ditindaklanjuti',
                        'accepted' => 'Diterima',
                        'rejected' => 'Ditolak',
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
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'new' => 'Baru',
                        'reviewed' => 'Ditinjau',
                        'followed_up' => 'Ditindaklanjuti',
                        'accepted' => 'Diterima',
                        'rejected' => 'Ditolak',
                        'archived' => 'Diarsipkan',
                    ]),

                SelectFilter::make('collaboration_type')
                    ->label('Jenis Kolaborasi')
                    ->options([
                        'program' => 'Program',
                        'research' => 'Riset',
                        'publication' => 'Publikasi',
                        'media' => 'Media',
                        'partnership' => 'Kemitraan',
                        'other' => 'Lainnya',
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
            'index' => ListCollaborationSubmissions::route('/'),
            'create' => CreateCollaborationSubmission::route('/create'),
            'edit' => EditCollaborationSubmission::route('/{record}/edit'),
        ];
    }
}
