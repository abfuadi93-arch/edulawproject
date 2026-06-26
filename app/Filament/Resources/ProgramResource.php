<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasSlugFormBehavior;
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
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProgramResource extends Resource
{
    use HasSlugFormBehavior;

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
                Grid::make([
                    'default' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        Section::make('Informasi Utama')
                            ->description('Informasi dasar program yang akan ditampilkan di website.')
                            ->schema([
                                TextInput::make('name')
                                    ->label('1. Nama Program')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Nama program Edulaw')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(static::syncSlugFrom())
                                    ->columnSpanFull(),

                                TextInput::make('slug')
                                    ->label('2. Slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->helperText('Slug digunakan sebagai alamat program di website.')
                                    ->columnSpanFull(),

                                Grid::make([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                    ->schema([
                                        Select::make('program_category_id')
                                            ->label('3. Kategori')
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->placeholder('Pilih kategori program'),

                                        Select::make('format')
                                            ->label('4. Format')
                                            ->options([
                                                'online' => 'Online',
                                                'offline' => 'Offline',
                                                'hybrid' => 'Hybrid',
                                            ])
                                            ->required(),
                                    ])
                                    ->columnSpanFull(),

                                Grid::make([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                    ->schema([
                                        TextInput::make('level')
                                            ->label('5. Level')
                                            ->placeholder('Beginner, Intermediate, Advanced'),

                                        TextInput::make('audience')
                                            ->label('6. Audiens')
                                            ->placeholder('Mahasiswa, umum, peneliti, praktisi'),
                                    ])
                                    ->columnSpanFull(),

                                Textarea::make('short_description')
                                    ->label('7. Ringkasan')
                                    ->rows(6)
                                    ->maxLength(300)
                                    ->live()
                                    ->placeholder('Tulis ringkasan program secara singkat dan jelas...')
                                    ->helperText('Maksimal 300 karakter termasuk spasi.')
                                    ->columnSpanFull(),

                                Select::make('status')
                                    ->label('8. Status')
                                    ->options([
                                        'upcoming' => 'Upcoming',
                                        'ongoing' => 'Ongoing',
                                        'archived' => 'Archived',
                                    ])
                                    ->default('upcoming')
                                    ->required()
                                    ->helperText('Program akan disimpan sesuai status dan ditampilkan mengikuti aturan publikasi website.')
                                    ->columnSpanFull(),
                            ])
                            ->extraAttributes(['class' => 'edulaw-admin-two-column-section']),

                        Section::make('Jadwal dan Lokasi')
                            ->description('Atur waktu, lokasi, dan akses pendaftaran program.')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                    ->schema([
                                        DateTimePicker::make('event_date')
                                            ->label('Tanggal Mulai')
                                            ->seconds(false)
                                            ->helperText('Pilih tanggal dan waktu mulai program.'),

                                        DateTimePicker::make('end_date')
                                            ->label('Tanggal Selesai')
                                            ->seconds(false)
                                            ->helperText('Pilih tanggal dan waktu selesai program.'),

                                        TextInput::make('location')
                                            ->label('Lokasi')
                                            ->maxLength(255)
                                            ->columnSpanFull(),

                                        TextInput::make('registration_link')
                                            ->label('Link Pendaftaran')
                                            ->url()
                                            ->maxLength(255)
                                            ->helperText('Link yang akan diarahkan ke peserta untuk mendaftar.')
                                            ->columnSpanFull(),

                                        TextInput::make('price_type')
                                            ->label('Jenis Biaya')
                                            ->placeholder('Gratis / Berbayar / Donasi'),

                                        Toggle::make('certificate_available')
                                            ->label('Sertifikat Tersedia')
                                            ->helperText('Peserta akan menerima sertifikat.')
                                            ->default(false),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'edulaw-admin-section-pair']),

                Section::make('Poin Pembelajaran (Learning Points)')
                    ->description('Tuliskan poin pembelajaran program secara ringkas dan jelas.')
                    ->schema([
                        Repeater::make('learning_points')
                            ->label('Learning Points')
                            ->schema([
                                TextInput::make('point')
                                    ->label('Poin Pembelajaran')
                                    ->required(),
                            ])
                            ->itemLabel(fn (array $state): ?string => filled($state['point'] ?? null)
                                ? $state['point']
                                : 'Poin pembelajaran')
                            ->addActionLabel('Tambah Poin Pembelajaran')
                            ->reorderable()
                            ->collapsed()
                            ->columnSpanFull()
                            ->defaultItems(3),
                    ])
                    ->columnSpanFull(),

                Grid::make([
                    'default' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        Section::make('Narasumber')
                            ->description('Tambahkan narasumber atau fasilitator program.')
                            ->schema([
                                Repeater::make('speakers')
                                    ->label('Narasumber')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nama')
                                            ->required(),

                                        TextInput::make('title')
                                            ->label('Jabatan/Afiliasi'),
                                    ])
                                    ->columns(2)
                                    ->itemLabel(fn (array $state): ?string => filled($state['name'] ?? null)
                                        ? $state['name']
                                        : 'Narasumber')
                                    ->addActionLabel('Tambah Narasumber')
                                    ->reorderable()
                                    ->collapsed()
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Media Program')
                            ->description('Unggah gambar utama atau poster program.')
                            ->schema([
                                FileUpload::make('image')
                                    ->label('Gambar Sampul')
                                    ->image()
                                    ->disk('public')
                                    ->directory('programs')
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->imagePreviewHeight('220')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(4096)
                                    ->helperText('Rekomendasi ukuran 1200 x 675 px. Format: JPG, PNG, WebP. Maks. 4 MB.')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'edulaw-admin-section-pair']),

                Grid::make([
                    'default' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        Section::make('Publikasi')
                            ->description('Atur penayangan program di website.')
                            ->schema([
                                Toggle::make('featured')
                                    ->label('Tampilkan sebagai unggulan')
                                    ->helperText('Aktifkan untuk menampilkan program sebagai pilihan utama.')
                                    ->default(false),
                            ])
                            ->columns(1),

                        Section::make('SEO (Opsional)')
                            ->description('Optimasi mesin pencari untuk program ini.')
                            ->schema([
                                TextInput::make('seo_title')
                                    ->label('Meta Title')
                                    ->maxLength(60)
                                    ->helperText('Maksimal 60 karakter.'),

                                Textarea::make('seo_description')
                                    ->label('Meta Description')
                                    ->rows(3)
                                    ->maxLength(180)
                                    ->helperText('Maksimal 180 karakter termasuk spasi.'),

                                FileUpload::make('og_image')
                                    ->label('Gambar OG')
                                    ->image()
                                    ->disk('public')
                                    ->directory('seo/og-images')
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(4096),
                            ])
                            ->columns(1),
                    ])
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'edulaw-admin-section-pair']),
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

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrograms::route('/'),
            'create' => Pages\CreateProgram::route('/create'),
            'edit' => Pages\EditProgram::route('/{record}/edit'),
        ];
    }
}
