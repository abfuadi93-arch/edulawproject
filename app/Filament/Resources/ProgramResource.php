<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramResource\Pages;
use App\Models\Program;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Konten Website';

    protected static ?string $navigationLabel = 'Program';

    protected static ?string $modelLabel = 'Program';

    protected static ?string $pluralModelLabel = 'Program';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Program')
                    ->description('Isi identitas utama program Edulaw.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Program')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, $state) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Select::make('program_category_id')
                            ->label('Kategori Program')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Textarea::make('short_description')
                            ->label('Deskripsi Singkat')
                            ->rows(4)
                            ->maxLength(600)
                            ->columnSpanFull(),

                        FileUpload::make('image')
                            ->label('Gambar / Poster Program')
                            ->image()
                            ->disk('public')
                            ->directory('programs')
                            ->visibility('public')
                            ->imageEditor()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(4096)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Detail Pelaksanaan')
                    ->schema([
                        Select::make('format')
                            ->label('Format')
                            ->options([
                                'online' => 'Online',
                                'offline' => 'Offline',
                                'hybrid' => 'Hybrid',
                            ])
                            ->required(),

                        TextInput::make('level')
                            ->label('Level')
                            ->placeholder('Beginner, Intermediate, Advanced'),

                        TextInput::make('audience')
                            ->label('Audiens')
                            ->placeholder('Mahasiswa, umum, peneliti, praktisi'),

                        DateTimePicker::make('event_date')
                            ->label('Tanggal Mulai')
                            ->seconds(false),

                        DateTimePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->seconds(false),

                        TextInput::make('location')
                            ->label('Lokasi')
                            ->maxLength(255),

                        TextInput::make('registration_link')
                            ->label('Link Pendaftaran')
                            ->url()
                            ->maxLength(255),

                        TextInput::make('price_type')
                            ->label('Jenis Biaya')
                            ->placeholder('Gratis / Berbayar / Donasi'),

                        Toggle::make('certificate_available')
                            ->label('Sertifikat Tersedia')
                            ->default(false),
                    ])
                    ->columns(1),

                Section::make('Materi dan Narasumber')
                    ->schema([
                        Repeater::make('learning_points')
                            ->label('Learning Points')
                            ->schema([
                                TextInput::make('point')
                                    ->label('Poin Pembelajaran')
                                    ->required(),
                            ])
                            ->columnSpanFull()
                            ->defaultItems(3),

                        Repeater::make('speakers')
                            ->label('Narasumber')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama')
                                    ->required(),

                                TextInput::make('title')
                                    ->label('Jabatan/Afiliasi'),
                            ])
                            ->columns(1)
                            ->columnSpanFull(),
                    ]),

                Section::make('Status dan SEO')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'upcoming' => 'Upcoming',
                                'ongoing' => 'Ongoing',
                                'archived' => 'Archived',
                            ])
                            ->default('upcoming')
                            ->required(),

                        Toggle::make('featured')
                            ->label('Featured')
                            ->default(false),

                        TextInput::make('seo_title')
                            ->label('SEO Title')
                            ->maxLength(255),

                        Textarea::make('seo_description')
                            ->label('SEO Description')
                            ->rows(3)
                            ->maxLength(500),

                        FileUpload::make('og_image')
                            ->label('OG Image')
                            ->image()
                            ->disk('public')
                            ->directory('seo/programs')
                            ->visibility('public')
                            ->imageEditor(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->limit(70)
                    ->wrap()
                    ->description(fn (Program $record): ?string => $record->short_description ?: $record->display_category),

                TextColumn::make('category.name')
                    ->label('Klasifikasi')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('format')
                    ->label('Pelaksanaan')
                    ->state(fn (Program $record): string => collect([
                        $record->format ? Str::headline($record->format) : null,
                        $record->level,
                    ])->filter()->join(' / ') ?: '-')
                    ->description(fn (Program $record): string => $record->event_date?->translatedFormat('d M Y') ?? 'Tanggal belum diatur'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'upcoming' => 'primary',
                        'ongoing' => 'success',
                        'archived' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'upcoming' => 'Upcoming',
                        'ongoing' => 'Ongoing',
                        'archived' => 'Archived',
                        default => $state ? ucfirst($state) : '-',
                    }),

                IconColumn::make('featured')
                    ->label('Publikasi')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-minus')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('event_date', 'desc')
            ->searchPlaceholder('Cari program...')
            ->paginationPageOptions([10, 25, 50])
            ->emptyStateHeading('Belum ada program')
            ->emptyStateDescription('Program yang dibuat dari panel admin akan tampil di sini.')
            ->filters([
                SelectFilter::make('program_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),

                SelectFilter::make('format')
                    ->label('Format')
                    ->options([
                        'online' => 'Online',
                        'offline' => 'Offline',
                        'hybrid' => 'Hybrid',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'upcoming' => 'Upcoming',
                        'ongoing' => 'Ongoing',
                        'archived' => 'Archived',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton(),
                DeleteAction::make()
                    ->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public static function canViewAny(): bool
    {
        return self::canAccess();
    }

    public static function canView(Model $record): bool
    {
        return self::canAccess();
    }

    public static function canCreate(): bool
    {
        return self::canAccess();
    }

    public static function canEdit(Model $record): bool
    {
        return self::canAccess();
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->check();
    }

    public static function canDeleteAny(): bool
    {
        return auth()->check();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrograms::route('/'),
            'create' => Pages\CreateProgram::route('/create'),
            'edit' => Pages\EditProgram::route('/{record}/edit'),
        ];
    }
}
