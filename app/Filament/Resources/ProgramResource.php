<?php

namespace App\Filament\Resources;

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
use Filament\Forms\Components\Placeholder;
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
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

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
                                Section::make('Konten Program')
                                    ->icon('heroicon-o-academic-cap')
                                    ->description('Fokus utama halaman program: judul, kategori, narasi, poin belajar, poster, dan narasumber.')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Judul Kegiatan')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Diskusi Literasi Konstitusi Seri #13')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($get, $set, ?string $old, ?string $state): void {
                                                $currentSlug = (string) ($get('slug') ?? '');
                                                $oldSlug = Str::slug((string) $old);

                                                if (filled($currentSlug) && $currentSlug !== $oldSlug) {
                                                    return;
                                                }

                                                $set('slug', Str::slug((string) $state));
                                            })
                                            ->columnSpanFull(),

                                        Grid::make([
                                            'default' => 1,
                                            'lg' => 2,
                                        ])
                                            ->schema([
                                                Select::make('program_category_id')
                                                    ->label('Kategori Program')
                                                    ->relationship('programCategory', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required(),

                                                Select::make('type')
                                                    ->label('Jenis Program')
                                                    ->options(static::typeOptions())
                                                    ->searchable()
                                                    ->required(),
                                            ])
                                            ->columnSpanFull(),

                                        RichEditor::make('description')
                                            ->label('Deskripsi Detail')
                                            ->required()
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
                                            ->helperText('Satu poin per item.')
                                            ->columnSpanFull(),

                                        Grid::make([
                                            'default' => 1,
                                            'lg' => 2,
                                        ])
                                            ->schema([
                                                FileUpload::make('image')
                                                    ->label('Poster Kegiatan')
                                                    ->image()
                                                    ->disk('public')
                                                    ->directory('programs/posters')
                                                    ->visibility('public')
                                                    ->imageEditor()
                                                    ->imagePreviewHeight('180')
                                                    ->downloadable()
                                                    ->openable()
                                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                                    ->maxSize(4096),

                                                FileUpload::make('hero_image')
                                                    ->label('Gambar Hero')
                                                    ->image()
                                                    ->disk('public')
                                                    ->directory('programs/heroes')
                                                    ->visibility('public')
                                                    ->imageEditor()
                                                    ->imagePreviewHeight('180')
                                                    ->downloadable()
                                                    ->openable()
                                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                                    ->maxSize(4096)
                                                    ->helperText('Kosongkan untuk memakai poster.'),
                                            ])
                                            ->columnSpanFull(),

                                        Repeater::make('speakers')
                                            ->label('Narasumber')
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
                                                    ->imagePreviewHeight('120')
                                                    ->downloadable()
                                                    ->openable()
                                                    ->maxSize(2048)
                                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),

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
                                    ]),

                                Section::make('Detail Tambahan')
                                    ->icon('heroicon-o-clipboard-document-list')
                                    ->description('Opsional untuk memperkaya detail program.')
                                    ->schema([
                                        Grid::make([
                                            'default' => 1,
                                            'lg' => 3,
                                        ])
                                            ->schema([
                                                Textarea::make('orientation')
                                                    ->label('Orientasi')
                                                    ->rows(3),

                                                Textarea::make('method')
                                                    ->label('Metode')
                                                    ->rows(3),

                                                Textarea::make('output')
                                                    ->label('Output')
                                                    ->rows(3),
                                            ])
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

                                        Textarea::make('notes')
                                            ->label('Catatan')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1)
                                    ->collapsible()
                                    ->collapsed(),

                                Section::make('Pengaturan Lanjutan')
                                    ->icon('heroicon-o-cog-6-tooth')
                                    ->description('Opsional. Slug, teks singkat, klasifikasi lanjutan, tautan, dan CTA.')
                                    ->schema([
                                        TextInput::make('slug')
                                            ->label('Slug')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->helperText('Otomatis dari judul, boleh diedit sebelum terbit.')
                                            ->columnSpanFull(),

                                        Grid::make([
                                            'default' => 1,
                                            'lg' => 2,
                                        ])
                                            ->schema([
                                                TextInput::make('short_title')
                                                    ->label('Judul Pendek')
                                                    ->maxLength(255),

                                                TextInput::make('subtitle')
                                                    ->label('Tema / Subjudul')
                                                    ->maxLength(255),

                                                TextInput::make('duration')
                                                    ->label('Durasi')
                                                    ->maxLength(255)
                                                    ->placeholder('1 Pertemuan'),

                                                Select::make('level')
                                                    ->label('Level')
                                                    ->options(static::levelOptions())
                                                    ->default('Umum')
                                                    ->searchable(),

                                                TextInput::make('audience')
                                                    ->label('Target Peserta')
                                                    ->maxLength(255),

                                                TextInput::make('price_type')
                                                    ->label('Jenis Biaya')
                                                    ->maxLength(255)
                                                    ->placeholder('Gratis / Berbayar'),

                                                Toggle::make('certificate_available')
                                                    ->label('Sertifikat Tersedia')
                                                    ->default(false),
                                            ])
                                            ->columnSpanFull(),

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
                                                    ->maxLength(255)
                                                    ->helperText('Kosongkan untuk memakai link pendaftaran atau detail program.'),

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
                                    ])
                                    ->columns(1)
                                    ->collapsible()
                                    ->collapsed(),

                                Section::make('SEO & Pratinjau')
                                    ->icon('heroicon-o-magnifying-glass')
                                    ->description('Opsional. Jika kosong, sistem memakai judul, deskripsi, dan poster.')
                                    ->schema([
                                        TextInput::make('seo_title')
                                            ->label('SEO Title')
                                            ->maxLength(300)
                                            ->placeholder(fn ($get): string => $get('name') ?: 'Otomatis dari judul')
                                            ->helperText('Target 45–65 karakter. Gunakan judul natural; nama situs ditambahkan otomatis.'),

                                        Textarea::make('seo_description')
                                            ->label('SEO Description')
                                            ->rows(3)
                                            ->maxLength(180)
                                            ->placeholder('Otomatis dari deskripsi detail')
                                            ->helperText('Target 120–160 karakter. Jelaskan manfaat dan topik utama secara alami.'),

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
                                            ->maxSize(4096)
                                            ->helperText('Kosongkan untuk memakai gambar hero atau poster.'),
                                    ])
                                    ->columns(1)
                                    ->collapsible()
                                    ->collapsed(),
                            ])
                            ->columnSpan(['xl' => 8])
                            ->extraAttributes(['class' => 'edulaw-admin-main-column']),

                        Group::make()
                            ->schema([
                                Section::make('Publikasi')
                                    ->icon('heroicon-o-paper-airplane')
                                    ->schema([
                                        Select::make('publication_status')
                                            ->label('Status Publikasi')
                                            ->options(static::publicationStatusOptions())
                                            ->default('draft')
                                            ->required(),

                                        Toggle::make('featured')
                                            ->label('Featured')
                                            ->default(false),

                                        Toggle::make('show_on_homepage')
                                            ->label('Tampilkan di Beranda')
                                            ->default(false),

                                        TextInput::make('sort_order')
                                            ->label('Urutan')
                                            ->numeric()
                                            ->default(fn (): int => ((int) Program::query()->max('sort_order')) + 1),

                                        Placeholder::make('public_preview')
                                            ->label('Pratinjau')
                                            ->content(function ($get): HtmlString {
                                                $slug = trim((string) ($get('slug') ?? ''));

                                                if ($slug === '') {
                                                    return new HtmlString('<span class="text-sm text-gray-500">Tersedia setelah judul dan slug terisi.</span>');
                                                }

                                                $url = route('programs.show', $slug);

                                                return new HtmlString(
                                                    '<a href="'.e($url).'" target="_blank" rel="noopener noreferrer" class="fi-btn fi-color-gray fi-size-sm">Buka pratinjau</a>'
                                                );
                                            }),
                                    ])
                                    ->columns(1),

                                Section::make('Pelaksanaan')
                                    ->icon('heroicon-o-calendar-days')
                                    ->schema([
                                        Select::make('status')
                                            ->label('Status Kegiatan')
                                            ->options(static::statusOptions())
                                            ->default('upcoming')
                                            ->live()
                                            ->required(),

                                        DatePicker::make('event_date')
                                            ->label('Tanggal Mulai')
                                            ->required(fn (Get $get): bool => $get('status') !== 'archived'),

                                        DatePicker::make('end_date')
                                            ->label('Tanggal Selesai'),

                                        Select::make('format')
                                            ->label('Format')
                                            ->options(static::formatOptions())
                                            ->required(),

                                        TextInput::make('location')
                                            ->label('Lokasi')
                                            ->maxLength(255)
                                            ->placeholder('Online / Kampus / Kota / Hybrid'),
                                    ])
                                    ->columns(1),
                            ])
                            ->columnSpan(['xl' => 4])
                            ->extraAttributes(['class' => 'edulaw-admin-side-column edulaw-admin-sticky-column']),
                    ])
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'edulaw-admin-edit-shell']),
            ]);
    }

    public static function prepareFormDataForPersistence(array $data): array
    {
        if (blank($data['slug'] ?? null) && filled($data['name'] ?? null)) {
            $data['slug'] = Str::slug((string) $data['name']);
        }

        if (filled($data['slug'] ?? null)) {
            $data['slug'] = Str::slug((string) $data['slug']);
        }

        $data['publication_status'] = static::normalizePublicationStatusForForm($data['publication_status'] ?? null);
        $data['status'] = static::normalizeStatusForForm($data['status'] ?? null);
        $data['short_description'] = static::excerptFromDescription($data['description'] ?? null);

        if (blank($data['primary_button_text'] ?? null)) {
            $data['primary_button_text'] = 'Daftar Program';
        }

        if (blank($data['primary_button_url'] ?? null)) {
            $data['primary_button_url'] = filled($data['registration_link'] ?? null)
                ? $data['registration_link']
                : (filled($data['slug'] ?? null) ? route('programs.show', $data['slug'], false) : '/program');
        }

        if (blank($data['secondary_button_text'] ?? null)) {
            $data['secondary_button_text'] = 'Diskusikan Kolaborasi';
        }

        if (blank($data['secondary_button_url'] ?? null)) {
            $data['secondary_button_url'] = '/kolaborasi';
        }

        if (blank($data['seo_title'] ?? null) && filled($data['name'] ?? null)) {
            $data['seo_title'] = (string) $data['name'];
        }

        if (blank($data['seo_description'] ?? null) && filled($data['short_description'] ?? null)) {
            $data['seo_description'] = static::excerptFromDescription((string) $data['short_description'], 180);
        }

        if (blank($data['og_image'] ?? null)) {
            $data['og_image'] = $data['hero_image'] ?? $data['image'] ?? null;
        }

        return $data;
    }

    public static function excerptFromDescription(?string $html, int $limit = 220): ?string
    {
        $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $html), ENT_QUOTES, 'UTF-8')) ?? '');

        if ($text === '') {
            return null;
        }

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        $excerpt = rtrim(mb_substr($text, 0, max(0, $limit - 3)));
        $lastSpace = mb_strrpos($excerpt, ' ');

        if ($lastSpace !== false && $lastSpace >= 120) {
            $excerpt = rtrim(mb_substr($excerpt, 0, $lastSpace));
        }

        return $excerpt.'...';
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
                    ->color(fn (?string $state): string => static::statusColor($state))
                    ->formatStateUsing(fn (?string $state): string => static::statusLabel($state)),

                TextColumn::make('publication_status')
                    ->label('Publikasi')
                    ->badge()
                    ->color(fn (?string $state): string => static::publicationStatusColor($state))
                    ->formatStateUsing(fn (?string $state): string => static::publicationStatusLabel($state)),

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
                    ->options(static::statusOptions())
                    ->query(function ($query, array $data): void {
                        $status = $data['value'] ?? null;

                        match ($status) {
                            'upcoming', 'ongoing' => $query->where('status', $status),
                            'archived' => $query->whereIn('status', ['archived', 'completed', 'portfolio']),
                            default => null,
                        };
                    }),

                SelectFilter::make('publication_status')
                    ->label('Status Publikasi')
                    ->options(static::publicationStatusOptions())
                    ->query(function ($query, array $data): void {
                        $status = $data['value'] ?? null;

                        match ($status) {
                            'draft' => $query->whereIn('publication_status', ['draft', 'archived']),
                            'reviewed', 'published' => $query->where('publication_status', $status),
                            default => null,
                        };
                    }),

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

    public static function statusOptions(): array
    {
        return [
            'upcoming' => 'Upcoming',
            'ongoing' => 'Ongoing',
            'archived' => 'Archived',
        ];
    }

    public static function statusLabel(?string $status): string
    {
        return match ($status) {
            'upcoming' => 'Upcoming',
            'ongoing' => 'Ongoing',
            'archived', 'completed', 'portfolio' => 'Archived',
            default => ucfirst((string) ($status ?: 'Upcoming')),
        };
    }

    public static function statusColor(?string $status): string
    {
        return match ($status) {
            'upcoming' => 'primary',
            'ongoing' => 'success',
            'archived', 'completed', 'portfolio' => 'gray',
            default => 'gray',
        };
    }

    public static function normalizeStatusForForm(?string $status): string
    {
        return match ($status) {
            'ongoing', 'archived' => $status,
            'completed', 'portfolio' => 'archived',
            default => 'upcoming',
        };
    }

    public static function publicationStatusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'reviewed' => 'Reviewed',
            'published' => 'Published',
        ];
    }

    public static function publicationStatusLabel(?string $status): string
    {
        return match ($status) {
            'draft' => 'Draft',
            'reviewed' => 'Reviewed',
            'published' => 'Published',
            'archived' => 'Archived',
            default => ucfirst((string) ($status ?: 'Draft')),
        };
    }

    public static function publicationStatusColor(?string $status): string
    {
        return match ($status) {
            'published' => 'success',
            'reviewed' => 'warning',
            'archived' => 'gray',
            default => 'primary',
        };
    }

    public static function normalizePublicationStatusForForm(?string $status): string
    {
        return match ($status) {
            'reviewed', 'published' => $status,
            default => 'draft',
        };
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrograms::route('/'),
            'create' => Pages\CreateProgram::route('/create'),
            'edit' => Pages\EditProgram::route('/{record}/edit'),
        ];
    }
}
