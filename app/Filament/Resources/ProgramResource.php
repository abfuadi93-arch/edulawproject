<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasSlugFormBehavior;
use App\Filament\Resources\ProgramResource\Pages;
use App\Models\Program;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
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
                    'xl' => 12,
                ])
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make('1. Identitas Program')
                                    ->icon('heroicon-o-academic-cap')
                                    ->description('Isi klasifikasi, judul, dan ringkasan utama program Edulaw.')
                                    ->schema([
                                        Grid::make([
                                            'default' => 1,
                                            'lg' => 2,
                                        ])
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Judul Kegiatan')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder('Inspiring Lecture Hukum dan Kebijakan Publik')
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(static::syncSlugFrom())
                                                    ->columnSpanFull(),

                                                TextInput::make('slug')
                                                    ->label('Slug')
                                                    ->required()
                                                    ->unique(ignoreRecord: true)
                                                    ->maxLength(255)
                                                    ->helperText('Otomatis dari judul, tetapi boleh diedit manual.')
                                                    ->columnSpanFull(),

                                                Select::make('program_category_id')
                                                    ->label('Kategori Program')
                                                    ->relationship('programCategory', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->helperText('Lecturer untuk kuliah/pengayaan besar, Discussion untuk forum dialogis, Training untuk kelas atau pelatihan.'),

                                                Select::make('type')
                                                    ->label('Jenis Program')
                                                    ->options(static::typeOptions())
                                                    ->searchable()
                                                    ->required()
                                                    ->helperText('Pilih jenis paling dekat dengan karakter kegiatan.'),

                                                TextInput::make('short_title')
                                                    ->label('Judul Pendek')
                                                    ->maxLength(255)
                                                    ->helperText('Digunakan untuk card publik jika judul utama terlalu panjang.'),

                                                TextInput::make('subtitle')
                                                    ->label('Tema / Subjudul')
                                                    ->maxLength(255)
                                                    ->helperText('Digunakan sebagai subtitle program.'),
                                            ])
                                            ->columnSpanFull(),

                                        Textarea::make('short_description')
                                            ->label('Deskripsi Singkat')
                                            ->required()
                                            ->rows(4)
                                            ->maxLength(500)
                                            ->live()
                                            ->placeholder('Tulis ringkasan program secara singkat dan jelas...')
                                            ->helperText('Ringkasan untuk card dan hero halaman publik.')
                                            ->columnSpanFull(),
                                    ])
                                    ->extraAttributes(['class' => 'edulaw-admin-two-column-section']),

                                Section::make('2. Konten Halaman')
                                    ->icon('heroicon-o-pencil-square')
                                    ->description('Lengkapi narasi, poin belajar, dan catatan yang tampil pada detail program.')
                                    ->schema([
                                        RichEditor::make('description')
                                            ->label('Deskripsi Detail')
                                            ->columnSpanFull(),

                                        TagsInput::make('learning_points')
                                            ->label('Apa yang Dipelajari')
                                            ->placeholder('Ketik poin lalu tekan Enter...')
                                            ->splitKeys(['Enter'])
                                            ->reorderable()
                                            ->afterStateHydrated(static function (TagsInput $component): void {
                                                $state = $component->getState();

                                                if (blank($state)) {
                                                    $component->state([]);

                                                    return;
                                                }

                                                if (is_string($state)) {
                                                    $state = preg_split('/\r\n|\r|\n/', $state) ?: [];
                                                }

                                                if (! is_array($state)) {
                                                    $component->state([]);

                                                    return;
                                                }

                                                $component->state(
                                                    collect($state)
                                                        ->map(fn ($item): ?string => is_array($item)
                                                            ? ($item['point'] ?? $item['text'] ?? null)
                                                            : $item)
                                                        ->map(fn ($item): ?string => is_string($item) ? trim($item) : null)
                                                        ->filter(fn (?string $item): bool => filled($item))
                                                        ->values()
                                                        ->all()
                                                );
                                            })
                                            ->dehydrateStateUsing(fn (?array $state): array => collect($state ?? [])
                                                ->map(fn ($item): ?string => is_string($item) ? trim($item) : null)
                                                ->filter(fn (?string $item): bool => filled($item))
                                                ->values()
                                                ->all())
                                            ->helperText('Ketik satu poin lalu tekan Enter. Item dapat dihapus atau diurutkan ulang.')
                                            ->columnSpanFull(),

                                        Grid::make([
                                            'default' => 1,
                                            'lg' => 3,
                                        ])
                                            ->schema([
                                                Textarea::make('orientation')
                                                    ->label('Orientasi')
                                                    ->rows(4),

                                                Textarea::make('method')
                                                    ->label('Metode')
                                                    ->rows(4),

                                                Textarea::make('output')
                                                    ->label('Output')
                                                    ->rows(4),
                                            ])
                                            ->columnSpanFull(),

                                        Textarea::make('notes')
                                            ->label('Catatan')
                                            ->rows(4)
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('3. Narasumber')
                                    ->icon('heroicon-o-users')
                                    ->description('Tambahkan narasumber, fasilitator, dan moderator kegiatan.')
                                    ->schema([
                                        Repeater::make('speakers')
                                            ->label('Daftar Narasumber')
                                            ->schema([
                                                Grid::make([
                                                    'default' => 1,
                                                    'lg' => 2,
                                                ])
                                                    ->schema([
                                                        TextInput::make('name')
                                                            ->label('Nama')
                                                            ->required()
                                                            ->maxLength(255),

                                                        TextInput::make('title')
                                                            ->label('Jabatan / Afiliasi')
                                                            ->maxLength(255),
                                                    ])
                                                    ->columnSpanFull(),

                                                FileUpload::make('photo')
                                                    ->label('Foto')
                                                    ->image()
                                                    ->disk('public')
                                                    ->directory('programs/speakers')
                                                    ->visibility('public')
                                                    ->imageEditor()
                                                    ->downloadable()
                                                    ->openable()
                                                    ->maxSize(2048)
                                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                                    ->columnSpanFull(),

                                                Textarea::make('bio')
                                                    ->label('Bio')
                                                    ->rows(3)
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(1)
                                            ->itemLabel(fn (array $state): ?string => filled($state['name'] ?? null)
                                                ? $state['name']
                                                : 'Narasumber')
                                            ->addActionLabel('Tambah Narasumber')
                                            ->reorderable()
                                            ->collapsible()
                                            ->columnSpanFull(),

                                        Grid::make([
                                            'default' => 1,
                                            'lg' => 2,
                                        ])
                                            ->schema([
                                                TextInput::make('moderator_name')
                                                    ->label('Nama Moderator')
                                                    ->maxLength(255),

                                                TextInput::make('moderator_affiliation')
                                                    ->label('Afiliasi Moderator')
                                                    ->maxLength(255),
                                            ]),
                                    ]),

                                Section::make('Link dan CTA')
                                    ->icon('heroicon-o-link')
                                    ->description('Tambahkan tautan pendaftaran, dokumentasi, materi, dan tombol aksi.')
                                    ->schema([
                                        Grid::make([
                                            'default' => 1,
                                            'lg' => 2,
                                        ])
                                            ->schema([
                                                TextInput::make('registration_link')
                                                    ->label('Link Pendaftaran')
                                                    ->url()
                                                    ->maxLength(255)
                                                    ->placeholder('https://...'),

                                                TextInput::make('youtube_url')
                                                    ->label('Link Dokumentasi YouTube')
                                                    ->url()
                                                    ->maxLength(255)
                                                    ->placeholder('https://...'),

                                                TextInput::make('material_link')
                                                    ->label('Link Materi')
                                                    ->url()
                                                    ->maxLength(255)
                                                    ->placeholder('https://...'),
                                            ])
                                            ->columnSpanFull(),

                                        Grid::make([
                                            'default' => 1,
                                            'lg' => 2,
                                        ])
                                            ->schema([
                                                TextInput::make('primary_button_text')
                                                    ->label('Teks Tombol Utama')
                                                    ->default('Daftar Program')
                                                    ->maxLength(255),

                                                TextInput::make('primary_button_url')
                                                    ->label('Link Tombol Utama')
                                                    ->default('/program')
                                                    ->maxLength(255)
                                                    ->helperText('Boleh memakai path internal seperti /program.'),

                                                TextInput::make('secondary_button_text')
                                                    ->label('Teks Tombol Kedua')
                                                    ->default('Diskusikan Kolaborasi')
                                                    ->maxLength(255),

                                                TextInput::make('secondary_button_url')
                                                    ->label('Link Tombol Kedua')
                                                    ->default('/kolaborasi')
                                                    ->maxLength(255)
                                                    ->helperText('Boleh memakai path internal seperti /kolaborasi.'),
                                            ])
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('SEO & Pratinjau')
                                    ->icon('heroicon-o-magnifying-glass')
                                    ->description('Opsional untuk judul mesin pencari dan gambar share.')
                                    ->schema([
                                        TextInput::make('seo_title')
                                            ->label('SEO Title')
                                            ->maxLength(60),

                                        Textarea::make('seo_description')
                                            ->label('SEO Description')
                                            ->rows(3)
                                            ->maxLength(180)
                                            ->helperText('Maksimal 180 karakter termasuk spasi.'),

                                        FileUpload::make('og_image')
                                            ->label('OG Image')
                                            ->image()
                                            ->disk('public')
                                            ->directory('seo/og-images')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->downloadable()
                                            ->openable()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->maxSize(4096),
                                    ])
                                    ->columns(1)
                                    ->collapsible(),
                            ])
                            ->columnSpan(['xl' => 8])
                            ->extraAttributes(['class' => 'edulaw-admin-main-column']),

                        Group::make()
                            ->schema([
                                Section::make('Status Publikasi')
                                    ->icon('heroicon-o-paper-airplane')
                                    ->description('Atur status publikasi, urutan, dan penanda halaman.')
                                    ->schema([
                                        Select::make('publication_status')
                                            ->label('Status Publikasi')
                                            ->options(static::publicationStatusOptions())
                                            ->default('draft')
                                            ->required(),

                                        Select::make('status')
                                            ->label('Status Kegiatan')
                                            ->options(static::statusOptions())
                                            ->default('upcoming')
                                            ->live()
                                            ->required(),

                                        Toggle::make('featured')
                                            ->label('Featured')
                                            ->default(false),

                                        Toggle::make('show_on_homepage')
                                            ->label('Tampilkan di Beranda')
                                            ->default(false),

                                        TextInput::make('sort_order')
                                            ->label('Urutan Otomatis')
                                            ->numeric()
                                            ->default(fn (): int => ((int) Program::query()->max('sort_order')) + 1)
                                            ->helperText('Urutan tampil dibuat otomatis saat program disimpan.'),
                                    ])
                                    ->columns(1),

                                Section::make('Pelaksanaan')
                                    ->icon('heroicon-o-calendar-days')
                                    ->description('Atur waktu, format, lokasi, dan karakter kegiatan.')
                                    ->schema([
                                        DatePicker::make('event_date')
                                            ->label('Tanggal Mulai')
                                            ->required(fn (Get $get): bool => ! in_array($get('status'), ['portfolio', 'archived'], true)),

                                        DatePicker::make('end_date')
                                            ->label('Tanggal Selesai')
                                            ->helperText('Jika kosong, halaman publik boleh memakai tanggal mulai.'),

                                        Select::make('format')
                                            ->label('Format')
                                            ->options(static::formatOptions())
                                            ->required(),

                                        TextInput::make('duration')
                                            ->label('Durasi')
                                            ->maxLength(255)
                                            ->placeholder('1 Pertemuan, 2 Hari, 4 Sesi'),

                                        Select::make('level')
                                            ->label('Level')
                                            ->options(static::levelOptions())
                                            ->default('Umum')
                                            ->searchable()
                                            ->helperText('Umum untuk kegiatan terbuka, Intermediate untuk pembahasan dengan dasar hukum, Advanced untuk riset/disertasi atau kajian teoritis.'),

                                        TextInput::make('location')
                                            ->label('Lokasi')
                                            ->maxLength(255)
                                            ->placeholder('Online / Kampus / Kota / Hybrid'),

                                        TextInput::make('audience')
                                            ->label('Target Peserta')
                                            ->maxLength(255)
                                            ->placeholder('Mahasiswa, umum, peneliti, praktisi'),

                                        TextInput::make('price_type')
                                            ->label('Jenis Biaya')
                                            ->maxLength(255)
                                            ->placeholder('Gratis / Berbayar / Donasi'),

                                        Toggle::make('certificate_available')
                                            ->label('Sertifikat Tersedia')
                                            ->default(false),
                                    ])
                                    ->columns(1),

                                Section::make('Media')
                                    ->icon('heroicon-o-photo')
                                    ->description('Unggah poster dan hero image untuk halaman publik.')
                                    ->schema([
                                        FileUpload::make('image')
                                            ->label('Poster Kegiatan')
                                            ->image()
                                            ->disk('public')
                                            ->directory('programs/posters')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->imagePreviewHeight('220')
                                            ->downloadable()
                                            ->openable()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->maxSize(4096)
                                            ->helperText('Path disimpan relatif pada disk public. Rekomendasi ukuran 1200 x 1600 px, maks. 4 MB.'),

                                        FileUpload::make('hero_image')
                                            ->label('Gambar Hero')
                                            ->image()
                                            ->disk('public')
                                            ->directory('programs/heroes')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->imagePreviewHeight('220')
                                            ->downloadable()
                                            ->openable()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->maxSize(4096)
                                            ->helperText('Opsional. Jika kosong, halaman publik memakai poster kegiatan.'),
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
                ImageColumn::make('image')
                    ->label('Poster')
                    ->disk('public')
                    ->square()
                    ->size(44)
                    ->width(64)
                    ->grow(false)
                    ->defaultImageUrl(asset('images/logo/icon-bg.png')),

                TextColumn::make('name')
                    ->label('Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->limit(56)
                    ->wrap()
                    ->description(fn (Program $record): ?string => filled($record->subtitle)
                        ? $record->subtitle
                        : Str::limit((string) $record->short_description, 90)),

                TextColumn::make('programCategory.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn (?string $state): string => $state ?: '-')
                    ->toggleable(),

                TextColumn::make('format')
                    ->label('Format')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'online' => 'success',
                        'offline' => 'gray',
                        'hybrid' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => $state ? Str::headline($state) : '-')
                    ->sortable(),

                TextColumn::make('event_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status Kegiatan')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'upcoming' => 'primary',
                        'ongoing' => 'success',
                        'completed' => 'info',
                        'portfolio' => 'warning',
                        'archived' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => static::statusOptions()[$state] ?? ($state ? ucfirst($state) : '-')),

                TextColumn::make('publication_status')
                    ->label('Publikasi')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'published' => 'success',
                        'archived' => 'gray',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (?string $state): string => static::publicationStatusOptions()[$state] ?? ($state ? ucfirst($state) : '-')),

                IconColumn::make('featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('show_on_homepage')
                    ->label('Beranda')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->searchPlaceholder('Cari program...')
            ->paginationPageOptions([10, 25, 50])
            ->emptyStateHeading('Belum ada program')
            ->emptyStateDescription('Program yang dibuat dari panel admin akan tampil di sini.')
            ->filters([
                SelectFilter::make('program_category_id')
                    ->label('Kategori')
                    ->relationship('programCategory', 'name'),

                SelectFilter::make('format')
                    ->label('Format')
                    ->options(static::formatOptions()),

                SelectFilter::make('status')
                    ->label('Status Kegiatan')
                    ->options(static::statusOptions()),

                SelectFilter::make('publication_status')
                    ->label('Status Publikasi')
                    ->options(static::publicationStatusOptions()),

                SelectFilter::make('featured')
                    ->label('Featured')
                    ->options([
                        '1' => 'Featured',
                        '0' => 'Tidak Featured',
                    ]),

                SelectFilter::make('show_on_homepage')
                    ->label('Beranda')
                    ->options([
                        '1' => 'Tampil di Beranda',
                        '0' => 'Tidak Tampil',
                    ]),
            ])
            ->recordActions([
                Action::make('viewPublic')
                    ->label('Lihat Halaman Publik')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Program $record): string => route('programs.show', $record->slug))
                    ->openUrlInNewTab()
                    ->iconButton()
                    ->visible(fn (Program $record): bool => filled($record->slug)),

                EditAction::make()
                    ->iconButton(),

                DeleteAction::make()
                    ->iconButton()
                    ->visible(fn (): bool => (bool) auth()->user()?->hasRole('super_admin')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => (bool) auth()->user()?->hasRole('super_admin')),
                ]),
            ]);
    }

    private static function typeOptions(): array
    {
        return [
            'Inspiring Lecture' => 'Inspiring Lecture',
            'General Lecture' => 'General Lecture',
            'Public Lecture' => 'Public Lecture',
            'DIKSI' => 'DIKSI',
            'Webinar' => 'Webinar',
            'Workshop' => 'Workshop',
            'Training' => 'Training',
            'Short Course' => 'Short Course',
            'Bootcamp' => 'Bootcamp',
            'Community Class' => 'Community Class',
        ];
    }

    private static function levelOptions(): array
    {
        return [
            'Umum' => 'Umum',
            'Beginner' => 'Beginner',
            'Intermediate' => 'Intermediate',
            'Advanced' => 'Advanced',
            'Dasar' => 'Dasar',
            'Menengah' => 'Menengah',
            'Lanjutan' => 'Lanjutan',
        ];
    }

    private static function formatOptions(): array
    {
        return [
            'online' => 'Online',
            'offline' => 'Offline',
            'hybrid' => 'Hybrid',
        ];
    }

    private static function statusOptions(): array
    {
        return [
            'upcoming' => 'Upcoming',
            'ongoing' => 'Ongoing',
            'completed' => 'Completed',
            'portfolio' => 'Portfolio',
            'archived' => 'Archived',
        ];
    }

    private static function publicationStatusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'published' => 'Published',
            'archived' => 'Archived',
        ];
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
