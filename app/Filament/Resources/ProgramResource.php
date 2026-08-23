<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\TinyMceEditor;
use App\Filament\Resources\ProgramResource\Pages;
use App\Models\Program;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Publikasi & Program';

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

                                        Select::make('program_category_id')
                                            ->label('Kategori Program')
                                            ->relationship('programCategory', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->columnSpanFull(),

                                        TinyMceEditor::make('description')
                                            ->label('Deskripsi Detail')
                                            ->required()
                                            ->height(520)
                                            ->fileAttachmentsDisk('public')
                                            ->fileAttachmentsDirectory('programs/content-images')
                                            ->fileAttachmentsVisibility('public')
                                            ->fileAttachmentsAcceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                                            ->fileAttachmentsMaxSize(4096)
                                            ->editorConfig([
                                                'toolbar' => 'undo redo | blocks | bold italic underline strikethrough superscript subscript | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent blockquote | link unlink image table hr charmap | removeformat searchreplace code fullscreen',
                                            ])
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
                                Section::make('Tampilan')
                                    ->icon('heroicon-o-eye')
                                    ->schema([
                                        Toggle::make('featured')
                                            ->label('Featured')
                                            ->default(false),

                                        Toggle::make('show_on_homepage')
                                            ->label('Tampilkan di Beranda')
                                            ->default(false),

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
                                    ->description('Status kegiatan ditentukan otomatis dari tanggal mulai dan tanggal selesai.')
                                    ->schema([
                                        DatePicker::make('event_date')
                                            ->label('Tanggal Mulai')
                                            ->required(),

                                        DatePicker::make('end_date')
                                            ->label('Tanggal Selesai')
                                            ->minDate(fn (Get $get) => $get('event_date')),

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

        $data['publication_status'] = 'published';
        $data['status'] = Program::statusFromDates(
            $data['event_date'] ?? null,
            $data['end_date'] ?? null,
            $data['status'] ?? null,
        );
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
                ViewColumn::make('program')
                    ->label('Program')
                    ->view('filament.tables.columns.resource-content', fn (Program $record): array => [
                        'imageUrl' => edulaw_file_url($record->image),
                        'title' => $record->name,
                        'metadata' => [
                            $record->display_format,
                            $record->display_level ?: $record->audience,
                        ],
                    ])
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->where(function (Builder $query) use ($search): void {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('speakers', 'like', "%{$search}%")
                                ->orWhere('location', 'like', "%{$search}%")
                                ->orWhereHas('programCategory', fn (Builder $query): Builder => $query->where('name', 'like', "%{$search}%"));
                        }))
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('name', $direction))
                    ->url(fn (Program $record): ?string => static::canEdit($record) ? static::getUrl('edit', ['record' => $record]) : null)
                    ->extraHeaderAttributes(['class' => 'edulaw-resource-primary-header'])
                    ->extraCellAttributes(['class' => 'edulaw-resource-primary-cell']),

                TextColumn::make('programCategory.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('warning')
                    ->limit(24)
                    ->tooltip(fn (?string $state): ?string => filled($state) && mb_strlen($state) > 24 ? $state : null)
                    ->sortable()
                    ->visibleFrom('lg')
                    ->extraHeaderAttributes(['class' => 'edulaw-resource-classification-header'])
                    ->extraCellAttributes(['class' => 'edulaw-resource-classification-cell']),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state, Program $record): string => static::statusColor($record->status))
                    ->formatStateUsing(fn (?string $state, Program $record): string => static::statusLabel($record->status))
                    ->extraHeaderAttributes(['class' => 'edulaw-resource-status-header'])
                    ->extraCellAttributes(['class' => 'edulaw-resource-status-cell']),

                TextColumn::make('event_date')
                    ->label('Jadwal')
                    ->formatStateUsing(fn (Program $record): string => static::scheduleLabel($record))
                    ->sortable()
                    ->toggleable()
                    ->visibleFrom('xl')
                    ->extraHeaderAttributes(['class' => 'edulaw-resource-time-header'])
                    ->extraCellAttributes(['class' => 'edulaw-resource-time-cell']),

                TextColumn::make('location')
                    ->label('Lokasi')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('format')
                    ->label('Format')
                    ->formatStateUsing(fn (?string $state): string => $state ? Str::headline($state) : '—')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->formatStateUsing(fn ($state): string => $state->locale('id')->diffForHumans())
                    ->tooltip(fn (Program $record): string => $record->updated_at->locale('id')->translatedFormat('d M Y, H:i'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->searchPlaceholder('Cari program, kategori, atau narasumber...')
            ->searchDebounce('500ms')
            ->paginationPageOptions([10, 25, 50])
            ->emptyStateHeading('Belum ada program')
            ->emptyStateDescription('Program yang dibuat dari panel admin akan tampil di sini.')
            ->filters([
                SelectFilter::make('program_category_id')
                    ->label('Kategori')
                    ->relationship('programCategory', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('format')
                    ->label('Format')
                    ->options(static::formatOptions()),

                SelectFilter::make('status')
                    ->label('Status Kegiatan')
                    ->options(static::statusOptions())
                    ->query(function ($query, array $data): void {
                        $status = $data['value'] ?? null;

                        match ($status) {
                            'upcoming' => $query->upcoming(),
                            'ongoing' => $query->ongoing(),
                            'archived' => $query->archived(),
                            default => null,
                        };
                    }),

                SelectFilter::make('level')
                    ->label('Level')
                    ->options(static::levelOptions()),

                TernaryFilter::make('featured')
                    ->label('Featured')
                    ->placeholder('Semua')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak'),

                Filter::make('event_date')
                    ->label('Rentang Tanggal Acara')
                    ->schema([
                        DatePicker::make('from')->label('Dari tanggal')->native(false),
                        DatePicker::make('until')->label('Sampai tanggal')->native(false),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('event_date', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('event_date', '<=', $date)))
                    ->indicateUsing(fn (array $data): array => static::dateRangeIndicators($data, 'Acara')),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view')
                        ->label('Lihat')
                        ->icon('heroicon-o-eye')
                        ->url(fn (Program $record): string => route('programs.show', $record->slug))
                        ->openUrlInNewTab()
                        ->visible(fn (Program $record): bool => filled($record->slug)
                            && $record->publication_status === 'published'
                            && in_array($record->status, ['upcoming', 'ongoing', 'completed', 'portfolio', 'archived'], true)),
                    EditAction::make()->label('Edit'),
                    ReplicateAction::make()
                        ->label('Duplikasi')
                        ->visible(fn (): bool => static::canCreate())
                        ->mutateRecordDataUsing(fn (array $data, Program $record): array => [
                            ...$data,
                            'name' => Str::limit($record->name.' (Salinan)', 255, ''),
                            'slug' => static::uniqueDuplicateSlug($record),
                            'publication_status' => 'published',
                            'sort_order' => static::nextSortOrder(),
                            'featured' => false,
                            'show_on_homepage' => false,
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                        ]),
                    DeleteAction::make()->label('Hapus')->requiresConfirmation(),
                ])->label('Aksi lainnya')->icon('heroicon-o-ellipsis-vertical')->tooltip('Aksi lainnya')->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('programCategory:id,name');
    }

    public static function scheduleLabel(Program $record): string
    {
        if (! $record->event_date) {
            return 'Belum dijadwalkan';
        }

        $start = $record->event_date->locale('id')->translatedFormat('d M Y');
        $end = $record->end_date?->locale('id')->translatedFormat('d M Y');

        return $end && $end !== $start ? $start.' – '.$end : $start;
    }

    private static function dateRangeIndicators(array $data, string $label): array
    {
        return collect([
            ($data['from'] ?? null) ? Indicator::make($label.' mulai '.Carbon::parse($data['from'])->locale('id')->translatedFormat('d M Y'))->removeField('from') : null,
            ($data['until'] ?? null) ? Indicator::make($label.' sampai '.Carbon::parse($data['until'])->locale('id')->translatedFormat('d M Y'))->removeField('until') : null,
        ])->filter()->all();
    }

    public static function uniqueDuplicateSlug(Program $record): string
    {
        $base = Str::limit(Str::slug($record->slug ?: $record->name).'-salinan', 240, '');
        $slug = $base;
        $suffix = 2;

        while (Program::query()->where('slug', $slug)->exists()) {
            $slug = Str::limit($base, 240 - strlen((string) $suffix), '').'-'.$suffix++;
        }

        return $slug;
    }

    public static function nextSortOrder(): int
    {
        return ((int) Program::query()->max('sort_order')) + 1;
    }

    public static function statusOptions(): array
    {
        return [
            'upcoming' => 'Akan Datang',
            'ongoing' => 'Berlangsung',
            'archived' => 'Diarsipkan',
        ];
    }

    public static function statusLabel(?string $status): string
    {
        return match ($status) {
            'upcoming' => 'Akan Datang',
            'ongoing' => 'Berlangsung',
            'archived', 'completed', 'portfolio' => 'Diarsipkan',
            default => 'Akan Datang',
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
