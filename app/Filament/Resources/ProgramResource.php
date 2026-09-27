<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\TinyMceEditor;
use App\Filament\Resources\ProgramResource\Pages;
use App\Models\Program;
use App\Models\ProgramCategory;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
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
        return $schema->components([
            Select::make('program_template')->label('Mulai dari template')->options(['general' => 'Program Umum', 'diksi' => 'DIKSI', 'training' => 'Pelatihan', 'internship' => 'Internship'])->default('general')->dehydrated(false)->visibleOn('create')->live()->afterStateUpdated(function ($state, $get, $set): void {
                if ($state === 'general') {
                    return;
                }
                $category = ProgramCategory::query()->where('is_active', true)->where('name', 'like', match ($state) {
                    'diksi' => '%Diskusi%', 'training' => '%Pelatihan%', default => '%Internship%'
                })->first();
                if (blank($get('program_category_id')) && $category) {
                    $set('program_category_id', $category->id);
                }
                if (blank($get('organizer_name'))) {
                    $set('organizer_name', 'Edulaw Project');
                }
                if ($state === 'diksi') {
                    $set('format', 'online');
                    if (blank($get('platform'))) {
                        $set('platform', 'Zoom / YouTube');
                    }
                    $set('price_type', 'Gratis');
                }
                if (blank($get('description'))) {
                    $set('description', '<h2>Gambaran Umum</h2><p></p>');
                }
            })->helperText('Pilih di awal. Teks yang sudah Anda tulis tetap dipertahankan.')->columnSpanFull(),
            Section::make('Informasi Program')->schema([
                TextInput::make('name')
                    ->label('Nama Program')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Virtual Internship Edulaw Project')
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
                Textarea::make('short_description')
                    ->label('Ringkasan')
                    ->required()
                    ->rows(3)
                    ->maxLength(500)
                    ->helperText('Gunakan 1–2 kalimat yang langsung menjelaskan program.')
                    ->columnSpanFull(),
                Select::make('program_category_id')
                    ->label('Kategori Program')
                    ->relationship('programCategory', 'name', modifyQueryUsing: fn (Builder $query): Builder => $query->whereIn('slug', ['diskusi', 'pelatihan', 'internship', 'workshop-webinar'])->orderBy('sort_order'))
                    ->searchable()
                    ->preload()
                    ->required(),
                TagsInput::make('tags')
                    ->label('Tag / Kata Kunci')
                    ->placeholder('Ketik kata kunci lalu tekan Enter')
                    ->helperText('Contoh: DIKSI, hukum tata negara, legal writing.')
                    ->splitKeys(['Enter', ','])
                    ->nestedRecursiveRules(['string', 'max:100'])
                    ->columnSpanFull(),
                Select::make('publication_status')->label('Status Publikasi')->options(['draft' => 'Draft', 'published' => 'Published'])->default('draft')->required()->live(),
            ])->columns(['default' => 1, 'md' => 2])->columnSpanFull(),
            Section::make('Jadwal & Pelaksanaan')->schema([
                DatePicker::make('event_date')
                    ->label('Tanggal Mulai')
                    ->required(fn (Get $get): bool => $get('publication_status') === 'published')->helperText('Wajib saat dipublikasikan; draft boleh belum memiliki jadwal.')
                    ->live(),
                DatePicker::make('end_date')->label('Tanggal Selesai')->live()
                    ->requiredWith('end_time')->afterOrEqual('event_date')
                    ->helperText('Kosongkan untuk acara satu hari atau jika belum diketahui.'),
                Select::make('format')->label('Format')->options(static::formatOptions())->default('online')->required()->live(),
                TextInput::make('location')->label('Lokasi')->maxLength(255)->visible(fn (Get $get): bool => in_array($get('format'), ['offline', 'hybrid'], true))->dehydratedWhenHidden(),
                TextInput::make('platform')->label('Platform')->placeholder('Zoom / YouTube')->maxLength(255)->visible(fn (Get $get): bool => in_array($get('format'), ['online', 'hybrid'], true))->dehydratedWhenHidden(),
                TextInput::make('online_url')->label('URL Acara Daring (Publik)')->url()->rules(['regex:~^https?://~'])
                    ->maxLength(255)->visible(fn (Get $get): bool => in_array($get('format'), ['online', 'hybrid'], true))->dehydratedWhenHidden()->columnSpanFull()
                    ->helperText('Jangan gunakan URL rapat privat atau link pendaftaran.'),
            ])->columns(['default' => 1, 'md' => 2])->columnSpanFull(),
            Section::make('Deskripsi Program')->schema([
                TinyMceEditor::make('description')
                    ->label('Deskripsi Program')
                    ->height(360)
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('programs/content-images')
                    ->fileAttachmentsVisibility('public')
                    ->fileAttachmentsAcceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                    ->fileAttachmentsMaxSize(4096)
                    ->editorConfig([
                        'toolbar' => 'undo redo | blocks | bold italic underline strikethrough superscript subscript | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent blockquote | link unlink image table hr charmap | removeformat searchreplace code fullscreen',
                    ])
                    ->columnSpanFull(),
            ])->columns(['default' => 1, 'md' => 2])->columnSpanFull(),
            Section::make('Penyelenggara & Narasumber')->schema([
                TextInput::make('organizer_name')->label('Nama Penyelenggara')->maxLength(255)
                    ->required()->default('Edulaw Project')
                    ->placeholder(config('edulaw.site.name', 'Edulaw Project')),
                Repeater::make('collaborators')->label('Mitra / Kolaborator')->schema([TextInput::make('name')->label('Nama Mitra')->required()->maxLength(255)])->defaultItems(0)->addActionLabel('Tambah Mitra')->collapsible()->itemLabel(fn (array $state): ?string => $state['name'] ?? null)->columnSpanFull(),
                Repeater::make('speakers')
                    ->label('Pembicara & Fasilitator')
                    ->helperText('Tambahkan individu, tim, atau organisasi yang memfasilitasi program.')
                    ->defaultItems(0)
                    ->schema([
                        Select::make('type')
                            ->label('Jenis Fasilitator')
                            ->options(['Person' => 'Individu', 'PerformingGroup' => 'Tim atau Organisasi'])
                            ->default('Person'),
                        Grid::make([
                            'default' => 1,
                            'lg' => 2,
                        ])
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Narasumber / Fasilitator')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('role')->label('Peran')->placeholder('Narasumber / Fasilitator')->maxLength(100),
                                TextInput::make('title')
                                    ->label('Institusi / Afiliasi')
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
                            ->label('Keterangan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->itemLabel(fn (array $state): ?string => filled($state['name'] ?? null)
                        ? $state['name']
                        : 'Narasumber')
                    ->addActionLabel('Tambah Orang')
                    ->reorderable()
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull(),
            ])->columns(['default' => 1, 'md' => 2])->columnSpanFull(),
            Section::make('Media')->schema([
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
                    ->maxSize(4096)->required(fn (Get $get): bool => $get('publication_status') === 'published')->columnSpanFull(),
            ])->columns(['default' => 1, 'md' => 2])->columnSpanFull(),
            Section::make('Pendaftaran')->schema([
                Toggle::make('requires_registration')->label('Memerlukan pendaftaran?')->default(false)->live()->afterStateHydrated(function ($component, $state, ?Program $record): void {
                    if ($state === null) {
                        $component->state(filled($record?->registration_link));
                    }
                }),
                Select::make('registration_status')->label('Status Pendaftaran')->options(['soon' => 'Segera Dibuka', 'open' => 'Dibuka', 'closed' => 'Ditutup'])->default('soon')->required(fn (Get $get): bool => (bool) $get('requires_registration'))->visible(fn (Get $get): bool => (bool) $get('requires_registration'))->dehydratedWhenHidden(),
                TextInput::make('registration_link')->label('Link Pendaftaran')->url()->rules(['regex:~^https?://~'])
                    ->maxLength(255)->placeholder('https://...')
                    ->helperText('Opsional. Kosongkan jika pendaftaran belum dibuka.')->visible(fn (Get $get): bool => (bool) $get('requires_registration'))->dehydratedWhenHidden(),
                Select::make('price_type')->label('Biaya')->options(['Gratis' => 'Gratis', 'Berbayar' => 'Berbayar'])->default('Gratis')->live()->visible(fn (Get $get): bool => (bool) $get('requires_registration'))->dehydratedWhenHidden(),
                TextInput::make('ticket_price')->label('Harga Tiket')->numeric()->minValue(0)->maxValue(9999999999.99)->step(0.01)
                    ->required(fn (Get $get): bool => (bool) $get('requires_registration') && $get('price_type') === 'Berbayar')
                    ->rules(['nullable', 'decimal:0,2'])
                    ->helperText('Isi 0 untuk gratis; kosongkan jika belum diketahui.')->visible(fn (Get $get): bool => (bool) $get('requires_registration') && $get('price_type') === 'Berbayar')->dehydratedWhenHidden(),
                Select::make('ticket_currency')->label('Mata Uang')->default('IDR')
                    ->options(['IDR' => 'IDR — Rupiah', 'USD' => 'USD — Dolar AS', 'EUR' => 'EUR — Euro', 'GBP' => 'GBP — Pound', 'SGD' => 'SGD — Dolar Singapura', 'MYR' => 'MYR — Ringgit', 'AUD' => 'AUD — Dolar Australia'])->visible(fn (Get $get): bool => (bool) $get('requires_registration') && $get('price_type') === 'Berbayar')->dehydratedWhenHidden(),
                TextInput::make('ticket_provider')->label('Provider Tiket')->maxLength(255)->visible(fn (Get $get): bool => (bool) $get('requires_registration') && $get('price_type') === 'Berbayar')->dehydratedWhenHidden(),
            ])->columns(['default' => 1, 'md' => 2])->columnSpanFull(),
            Section::make('Tampilan Publik')->schema([
                Toggle::make('featured')
                    ->label('Unggulan')
                    ->default(false),
            ])->columns(['default' => 1, 'md' => 2])->columnSpanFull(),
            Section::make('Pengaturan Lanjutan — opsional')->schema([
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText('Dibuat otomatis dari nama program.')
                    ->columnSpanFull(),
                Select::make('level')
                    ->label('Level')
                    ->options(static::levelOptions())
                    ->multiple()
                    ->searchable()
                    ->afterStateHydrated(static function (Select $component, mixed $state): void {
                        if (is_string($state)) {
                            $component->state(
                                collect(explode(',', $state))
                                    ->map(fn (string $level): string => trim($level))
                                    ->filter()
                                    ->values()
                                    ->all()
                            );
                        }
                    })
                    ->dehydrateStateUsing(fn (mixed $state): ?string => collect(is_array($state) ? $state : [])
                        ->filter()
                        ->implode(', ') ?: null)
                    ->helperText('Boleh memilih lebih dari satu level.'),
                TextInput::make('audience')
                    ->label('Audiens')
                    ->maxLength(255)
                    ->placeholder('Mahasiswa hukum, fresh graduate, peneliti muda'),
                TagsInput::make('learning_points')
                    ->label('Poin Pembelajaran')
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
                TimePicker::make('event_time')->label('Jam Mulai')->seconds(false)->format('H:i')
                    ->rules(['nullable', 'date_format:H:i'])->live(),
                TimePicker::make('end_time')->label('Jam Selesai')->seconds(false)->format('H:i')
                    ->rules(fn (Get $get): array => ['nullable', 'date_format:H:i', function (string $attribute, $value, Closure $fail) use ($get): void {
                        $schedule = [$get('event_date'), $get('end_date'), $get('event_time'), $value];
                        if (validator($schedule, [0 => 'required|date_format:Y-m-d', 1 => 'required|date_format:Y-m-d', 2 => 'required|date_format:H:i', 3 => 'required|date_format:H:i'])->fails()) {
                            return;
                        }

                        if (Carbon::parse($schedule[1].' '.$schedule[3])->lessThan(Carbon::parse($schedule[0].' '.$schedule[2]))) {
                            $fail('Waktu selesai tidak boleh sebelum waktu mulai. Untuk acara melewati tengah malam, sesuaikan tanggal selesai.');
                        }
                    }]),
                Select::make('event_timezone')->label('Zona Waktu')
                    ->options(collect(timezone_identifiers_list())->mapWithKeys(fn ($zone) => [$zone => $zone])->all())
                    ->default(config('edulaw.timezone', 'Asia/Jakarta'))->searchable(),
                Select::make('event_status')->label('Status Penyelenggaraan')
                    ->options([
                        'EventScheduled' => 'Terjadwal',
                        'EventCancelled' => 'Dibatalkan',
                        'EventPostponed' => 'Ditunda',
                        'EventRescheduled' => 'Dijadwalkan ulang',
                    ])->default('EventScheduled'),
                Toggle::make('is_manually_archived')->label('Arsipkan Program')->default(false)->helperText('Keluarkan dari program aktif tanpa mengubah tanggal.'),
                Select::make('status')->label('Status jika belum ada jadwal')->options(static::statusOptions())->default('upcoming')->helperText('Jika tanggal tersedia, status dihitung otomatis dari jadwal.'),
                Textarea::make('venue_address')->label('Alamat Jalan')->rows(2)->maxLength(255)->columnSpanFull(),
                TextInput::make('venue_city')->label('Kota / Kabupaten')->maxLength(255),
                TextInput::make('venue_region')->label('Provinsi / Wilayah')->maxLength(255),
                TextInput::make('venue_postal_code')->label('Kode Pos')->maxLength(20),
                TextInput::make('venue_country')->label('Kode Negara')->length(2)->placeholder('ID')
                    ->rules(['nullable', 'regex:/^[A-Za-z]{2}$/'])->dehydrateStateUsing(fn ($state) => filled($state) ? strtoupper($state) : null)
                    ->helperText('Kode dua huruf, misalnya ID.'),
                Select::make('organizer_type')->label('Jenis Penyelenggara')
                    ->options(['Organization' => 'Organisasi', 'Person' => 'Individu'])->default('Organization')->live(),
                TextInput::make('organizer_url')->label('Website Penyelenggara')->url()->rules(['regex:~^https?://~'])
                    ->maxLength(255)->columnSpanFull()->placeholder('https://...'),
                Toggle::make('certificate_available')
                    ->label('Sertifikat Tersedia')
                    ->default(false)
                    ->columnSpanFull(),
                Select::make('ticket_availability')->label('Ketersediaan Pendaftaran')
                    ->placeholder('Belum dikonfirmasi')
                    ->options(['InStock' => 'Dibuka / kuota tersedia', 'SoldOut' => 'Kuota habis', 'PreOrder' => 'Prapendaftaran']),
                DateTimePicker::make('registration_opens_at')->label('Pendaftaran Dibuka Pada')->seconds(false)
                    ->columnSpanFull(),
                Textarea::make('orientation')
                    ->label('Orientasi')
                    ->rows(3),
                Textarea::make('method')
                    ->label('Metode')
                    ->rows(3),
                Textarea::make('output')
                    ->label('Output')
                    ->rows(3),
                TextInput::make('moderator_name')
                    ->label('Nama Moderator')
                    ->maxLength(255),
                TextInput::make('moderator_affiliation')
                    ->label('Afiliasi Moderator')
                    ->maxLength(255),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(3)
                    ->columnSpanFull(),
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
                FileUpload::make('gallery_images')
                    ->label('Galeri Acara')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->maxFiles(6)
                    ->maxSize(4096)
                    ->disk('public')
                    ->directory('programs/gallery')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->helperText('Maksimal 6 gambar dokumentasi.')
                    ->columnSpanFull(),
                TextInput::make('seo_title')
                    ->label('Meta Title')
                    ->maxLength(300)
                    ->placeholder(fn ($get): string => $get('name') ?: 'Otomatis dari judul')
                    ->helperText('Target 45–65 karakter. Gunakan judul natural; nama situs ditambahkan otomatis.'),
                Textarea::make('seo_description')
                    ->label('Meta Description')
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
                Toggle::make('show_on_homepage')
                    ->label('Tampilkan di Beranda')
                    ->default(false),
            ])->columns(['default' => 1, 'md' => 2])->columnSpanFull()->collapsible()->collapsed(),
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

        $data['publication_status'] = $data['publication_status'] ?? 'published';
        $data['event_timezone'] = $data['event_timezone'] ?? 'Asia/Jakarta';
        if (($data['requires_registration'] ?? null) && ($data['price_type'] ?? null) === 'Gratis') {
            $data['ticket_price'] = 0;
        }
        $data['status'] = Program::statusFromDates(
            $data['event_date'] ?? null,
            $data['end_date'] ?? null,
            $data['status'] ?? null,
        );
        $data['short_description'] = filled($data['short_description'] ?? null)
            ? static::excerptFromDescription((string) $data['short_description'], 500)
            : static::excerptFromDescription($data['description'] ?? null);

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
                    ->relationship('programCategory', 'name', modifyQueryUsing: fn (Builder $query): Builder => $query->whereIn('slug', ['diskusi', 'pelatihan', 'internship', 'workshop-webinar'])->orderBy('sort_order'))
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
