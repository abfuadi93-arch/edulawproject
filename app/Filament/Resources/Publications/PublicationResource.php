<?php

namespace App\Filament\Resources\Publications;

use App\Filament\Forms\Components\TinyMceEditor;
use App\Filament\Resources\Publications\Pages\CreatePublication;
use App\Filament\Resources\Publications\Pages\EditPublication;
use App\Filament\Resources\Publications\Pages\ListPublications;
use App\Models\Author;
use App\Models\Publication;
use App\Models\PublicationType;
use App\Models\Tag;
use App\Support\EdulawSite;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
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
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PublicationResource extends Resource
{
    protected static ?string $model = Publication::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Publikasi & Program';

    protected static ?string $navigationLabel = 'Publikasi';

    protected static ?string $modelLabel = 'Publikasi';

    protected static ?string $pluralModelLabel = 'Publikasi';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'xl' => 12])
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make('Identitas Publikasi')
                                    ->icon('heroicon-o-document-text')
                                    ->description('Identitas, penulis, dan status publikasi yang tampil kepada pembaca.')
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Judul')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($get, $set, ?string $old, ?string $state): void {
                                                $currentSlug = (string) ($get('slug') ?? '');

                                                if (filled($currentSlug) && $currentSlug !== Str::slug((string) $old)) {
                                                    return;
                                                }

                                                $set('slug', Str::slug((string) $state));
                                            })
                                            ->columnSpanFull(),

                                        Grid::make(['default' => 1, 'lg' => 2])
                                            ->schema([
                                                Select::make('publication_type_id')
                                                    ->label('Tipe Publikasi')
                                                    ->relationship('type', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->createOptionForm([
                                                        TextInput::make('name')
                                                            ->label('Nama Tipe Publikasi')
                                                            ->required()
                                                            ->maxLength(255),
                                                        Textarea::make('description')
                                                            ->label('Deskripsi')
                                                            ->rows(3),
                                                    ])
                                                    ->createOptionUsing(function (array $data): int {
                                                        $type = PublicationType::query()->create([
                                                            'name' => trim((string) $data['name']),
                                                            'slug' => static::uniqueReferenceSlug(PublicationType::class, (string) $data['name'], 'tipe-publikasi'),
                                                            'description' => filled($data['description'] ?? null) ? trim((string) $data['description']) : null,
                                                            'is_active' => true,
                                                        ]);

                                                        return $type->getKey();
                                                    })
                                                    ->required(fn ($get): bool => $get('status') === 'published')
                                                    ->validationMessages([
                                                        'required' => 'Publikasi yang diterbitkan wajib memiliki tipe publikasi.',
                                                    ]),

                                                Select::make('authors')
                                                    ->label('Penulis')
                                                    ->relationship('authors', 'name')
                                                    ->multiple()
                                                    ->reorderable()
                                                    ->searchable()
                                                    ->preload()
                                                    ->createOptionForm([
                                                        TextInput::make('name')
                                                            ->label('Nama Penulis')
                                                            ->required()
                                                            ->maxLength(255),
                                                        TextInput::make('institution')
                                                            ->label('Institusi / Afiliasi')
                                                            ->maxLength(255),
                                                    ])
                                                    ->createOptionUsing(function (array $data): int {
                                                        $author = Author::query()->create([
                                                            'name' => trim((string) $data['name']),
                                                            'slug' => Author::uniqueSlugFor((string) $data['name']),
                                                            'institution' => filled($data['institution'] ?? null) ? trim((string) $data['institution']) : null,
                                                            'is_active' => true,
                                                        ]);

                                                        return $author->getKey();
                                                    })
                                                    ->saveRelationshipsUsing(function (Select $component): void {
                                                        $authorIds = collect((array) $component->getState())
                                                            ->filter()
                                                            ->unique()
                                                            ->values();

                                                        $component->getRelationship()->sync(
                                                            $authorIds->mapWithKeys(fn ($authorId, int $index): array => [
                                                                $authorId => [
                                                                    'author_order' => $index + 1,
                                                                    'role' => 'Penulis',
                                                                ],
                                                            ])->all(),
                                                        );

                                                        $component->getRecord()?->unsetRelation('authors');
                                                    })
                                                    ->required(fn ($get): bool => $get('status') === 'published')
                                                    ->placeholder('Pilih penulis')
                                                    ->helperText('Urutkan nama sesuai urutan penulis pada publikasi. Penulis baru dapat dibuat tanpa meninggalkan form ini.')
                                                    ->validationMessages([
                                                        'required' => 'Publikasi yang diterbitkan wajib memiliki minimal satu penulis.',
                                                    ]),

                                                Select::make('status')
                                                    ->label('Status')
                                                    ->options(static::statusOptions())
                                                    ->default('draft')
                                                    ->live()
                                                    ->required(),

                                                DatePicker::make('published_at')
                                                    ->label('Tanggal Publikasi Eksak')
                                                    ->native(false)
                                                    ->displayFormat('d/m/Y')
                                                    ->live()
                                                    ->required(fn ($get): bool => $get('status') === 'published' && blank($get('publication_date_text')))
                                                    ->helperText('Isi jika tanggal lengkap diketahui. Dipakai untuk pengurutan dan penjadwalan publikasi.')
                                                    ->validationMessages([
                                                        'required' => 'Isi tanggal eksak atau keterangan tahun/tanggal publikasi.',
                                                    ]),

                                                TextInput::make('publication_date_text')
                                                    ->label('Tahun / Tanggal Publikasi')
                                                    ->maxLength(255)
                                                    ->live(onBlur: true)
                                                    ->required(fn ($get): bool => $get('status') === 'published' && blank($get('published_at')))
                                                    ->placeholder('Contoh: Desember 2024')
                                                    ->helperText('Gunakan untuk tanggal parsial atau keterangan bibliografis, misalnya “2022” atau “2024 / tersedia daring 13 Januari 2025”.')
                                                    ->validationMessages([
                                                        'required' => 'Isi tahun/tanggal publikasi atau tanggal eksak.',
                                                    ]),

                                                Toggle::make('featured')
                                                    ->label('Publikasi Unggulan')
                                                    ->default(false),
                                            ])
                                            ->columnSpanFull(),

                                        TextInput::make('slug')
                                            ->label('Slug')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->helperText('Otomatis dari judul, boleh diedit sebelum terbit.')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Ringkasan dan Metadata')
                                    ->icon('heroicon-o-list-bullet')
                                    ->description('Ringkasan mandiri dan metadata bibliografis publikasi.')
                                    ->schema([
                                        Textarea::make('excerpt')
                                            ->label('Ringkasan / Excerpt')
                                            ->rows(4)
                                            ->maxLength(500)
                                            ->live(onBlur: true)
                                            ->required(fn ($get): bool => $get('status') === 'published')
                                            ->helperText('Tulis ringkasan singkat yang menjelaskan manfaat dan ruang lingkup publikasi.')
                                            ->validationMessages([
                                                'required' => 'Publikasi yang diterbitkan wajib memiliki ringkasan.',
                                            ])
                                            ->columnSpanFull(),

                                        TinyMceEditor::make('description')
                                            ->label('Deskripsi Lengkap')
                                            ->helperText('Opsional. Sasaran 200–400 kata untuk ringkasan mandiri yang menjelaskan masalah, konteks, pendekatan, dan hasil utama.')
                                            ->height(520)
                                            ->fileAttachmentsDisk('public')
                                            ->fileAttachmentsDirectory('publications/content-images')
                                            ->fileAttachmentsVisibility('public')
                                            ->fileAttachmentsAcceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                                            ->fileAttachmentsMaxSize(4096)
                                            ->editorConfig([
                                                'toolbar' => 'undo redo | blocks | bold italic underline strikethrough superscript subscript | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent blockquote | link unlink image table hr charmap | removeformat searchreplace code fullscreen',
                                            ])
                                            ->columnSpanFull(),

                                        Grid::make(['default' => 1, 'lg' => 2])
                                            ->schema([
                                                TextInput::make('page_count')
                                                    ->label('Jumlah Halaman')
                                                    ->numeric()
                                                    ->minValue(1)
                                                    ->nullable()
                                                    ->placeholder('Contoh: 15')
                                                    ->helperText('Masukkan jumlah halaman sebagai angka tanpa tanda atau satuan.')
                                                    ->suffix('halaman'),

                                                TextInput::make('source_name')
                                                    ->label('Sumber / Penerbit')
                                                    ->maxLength(255)
                                                    ->placeholder('Nama jurnal, prosiding, atau penerbit')
                                                    ->helperText('Dapat memuat nama jurnal/prosiding beserta volume dan nomor. Jika belum diketahui, biarkan kosong saat menyimpan draft.'),

                                                Select::make('language')
                                                    ->label('Bahasa')
                                                    ->options([
                                                        'id' => 'Indonesia',
                                                        'en' => 'English',
                                                    ])
                                                    ->default('id')
                                                    ->searchable(),

                                                Select::make('tags')
                                                    ->label('Tag / Kata Kunci')
                                                    ->relationship('tags', 'name')
                                                    ->multiple()
                                                    ->searchable()
                                                    ->preload()
                                                    ->createOptionForm([
                                                        TextInput::make('name')
                                                            ->label('Kata Kunci')
                                                            ->required()
                                                            ->maxLength(255),
                                                    ])
                                                    ->createOptionUsing(function (array $data): int {
                                                        $tag = Tag::query()->create([
                                                            'name' => trim((string) $data['name']),
                                                            'slug' => static::uniqueReferenceSlug(Tag::class, (string) $data['name'], 'kata-kunci'),
                                                        ]);

                                                        return $tag->getKey();
                                                    })
                                                    ->helperText('Pilih beberapa kata kunci atau buat kata kunci baru langsung dari form.'),
                                            ])
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Substansi Penelitian')
                                    ->icon('heroicon-o-magnifying-glass-circle')
                                    ->description('Opsional untuk data lama. Isi bagian yang tersedia agar halaman publikasi bernilai sebagai halaman HTML mandiri.')
                                    ->schema([
                                        Repeater::make('research_questions')
                                            ->label('Pertanyaan / Fokus Penelitian')
                                            ->schema([
                                                Textarea::make('item')->label('Fokus')->rows(2)->required(),
                                            ])
                                            ->defaultItems(0)
                                            ->maxItems(4)
                                            ->addActionLabel('Tambah fokus penelitian')
                                            ->columnSpanFull(),

                                        Repeater::make('key_findings')
                                            ->label('Temuan Utama')
                                            ->schema([
                                                Textarea::make('item')->label('Temuan')->rows(2)->required(),
                                            ])
                                            ->defaultItems(0)
                                            ->maxItems(6)
                                            ->addActionLabel('Tambah temuan')
                                            ->columnSpanFull(),

                                        Textarea::make('methodology')
                                            ->label('Metode')
                                            ->rows(5)
                                            ->columnSpanFull(),

                                        Textarea::make('contribution')
                                            ->label('Kontribusi Akademik / Praktis')
                                            ->rows(5)
                                            ->columnSpanFull(),

                                        Textarea::make('implications')
                                            ->label('Implikasi')
                                            ->rows(5)
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsible(),

                                Section::make('File dan Dokumen')
                                    ->icon('heroicon-o-paper-clip')
                                    ->description('Gunakan PDF internal atau tautan sumber eksternal yang valid.')
                                    ->schema([
                                        Grid::make(['default' => 1, 'lg' => 2])
                                            ->schema([
                                                FileUpload::make('pdf_file')
                                                    ->label('File PDF')
                                                    ->disk('public')
                                                    ->directory('publications/pdfs')
                                                    ->visibility('public')
                                                    ->acceptedFileTypes(['application/pdf'])
                                                    ->maxSize(20480)
                                                    ->downloadable()
                                                    ->openable()
                                                    ->previewable(false)
                                                    ->required(fn ($get): bool => $get('status') === 'published' && blank($get('external_url')))
                                                    ->helperText('Unggah PDF jika dokumen tersedia. Jika publikasi berasal dari sumber luar, gunakan External URL.')
                                                    ->validationMessages([
                                                        'required' => 'Publikasi yang diterbitkan wajib memiliki PDF atau External URL.',
                                                        'mimetypes' => 'File dokumen harus berformat PDF.',
                                                    ]),

                                                TextInput::make('external_url')
                                                    ->label('External URL')
                                                    ->url()
                                                    ->maxLength(255)
                                                    ->live(onBlur: true)
                                                    ->required(fn ($get): bool => $get('status') === 'published' && blank($get('pdf_file')))
                                                    ->placeholder('https://...')
                                                    ->helperText('Gunakan URL sumber resmi jika dokumen tidak diunggah ke Edulaw.')
                                                    ->validationMessages([
                                                        'required' => 'Publikasi yang diterbitkan wajib memiliki PDF atau External URL.',
                                                        'url' => 'External URL harus berupa alamat http atau https yang valid.',
                                                    ]),
                                            ])
                                            ->columnSpanFull(),

                                    ]),
                            ])
                            ->columnSpan(['xl' => 8])
                            ->extraAttributes(['class' => 'edulaw-admin-main-column']),

                        Group::make()
                            ->schema([
                                Section::make('Sitasi dan Share Preview')
                                    ->icon('heroicon-o-clipboard-document')
                                    ->description('Atur sitasi khusus dan teks yang digunakan saat publikasi dibagikan.')
                                    ->schema([
                                        Textarea::make('citation_text')
                                            ->label('Teks Sitasi')
                                            ->rows(5)
                                            ->helperText('Isi jika ingin menggunakan sitasi khusus. Jika kosong, sistem akan membuat sitasi otomatis dari metadata publikasi.'),

                                        TextInput::make('share_title')
                                            ->label('Judul Share')
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->placeholder('Fallback: Judul SEO lalu judul publikasi.'),

                                        Textarea::make('share_description')
                                            ->label('Deskripsi Share')
                                            ->rows(3)
                                            ->maxLength(500)
                                            ->live(onBlur: true)
                                            ->placeholder('Fallback: Deskripsi SEO, ringkasan, lalu deskripsi.'),

                                        Placeholder::make('share_preview')
                                            ->label('Pratinjau Share')
                                            ->content(fn ($get): HtmlString => static::sharePreviewHtml([
                                                'title' => $get('title'),
                                                'seo_title' => $get('seo_title'),
                                                'share_title' => $get('share_title'),
                                                'excerpt' => $get('excerpt'),
                                                'description' => $get('description'),
                                                'seo_description' => $get('seo_description'),
                                                'share_description' => $get('share_description'),
                                                'og_image' => $get('og_image'),
                                                'slug' => $get('slug'),
                                            ])),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),

                                Section::make('SEO Publikasi')
                                    ->icon('heroicon-o-magnifying-glass')
                                    ->description('Metadata mesin pencari dan gambar share publikasi.')
                                    ->schema([
                                        TextInput::make('seo_title')
                                            ->label('Judul SEO')
                                            ->maxLength(300)
                                            ->live(onBlur: true)
                                            ->placeholder(fn ($get): string => $get('title') ?: 'Otomatis dari judul')
                                            ->helperText('Target 45–65 karakter.'),

                                        Textarea::make('seo_description')
                                            ->label('Deskripsi SEO')
                                            ->rows(3)
                                            ->maxLength(180)
                                            ->live(onBlur: true)
                                            ->placeholder('Fallback dari ringkasan.')
                                            ->helperText('Target 120–160 karakter.'),

                                        FileUpload::make('og_image')
                                            ->label('Gambar OG')
                                            ->image()
                                            ->disk('public')
                                            ->directory('seo/og-images')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->maxSize(4096)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->downloadable()
                                            ->openable()
                                            ->helperText('Opsional. Digunakan khusus sebagai gambar pratinjau saat tautan dibagikan.'),
                                    ])
                                    ->columns(1)
                                    ->collapsible()
                                    ->collapsed(),
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
        if (($data['status'] ?? null) === 'published') {
            $messages = [];

            if (blank($data['publication_type_id'] ?? null)) {
                $messages['publication_type_id'] = 'Publikasi yang diterbitkan wajib memiliki tipe publikasi.';
            }

            if (array_key_exists('authors', $data) && count(array_filter((array) $data['authors'])) < 1) {
                $messages['authors'] = 'Publikasi yang diterbitkan wajib memiliki minimal satu penulis.';
            }

            if (blank($data['excerpt'] ?? null)) {
                $messages['excerpt'] = 'Publikasi yang diterbitkan wajib memiliki ringkasan.';
            }

            if (blank($data['published_at'] ?? null) && blank($data['publication_date_text'] ?? null)) {
                $messages['published_at'] = 'Isi tanggal eksak atau keterangan tahun/tanggal publikasi.';
            }

            if (blank($data['pdf_file'] ?? null) && blank($data['external_url'] ?? null)) {
                $messages['pdf_file'] = 'Publikasi yang diterbitkan wajib memiliki PDF atau External URL.';
            }

            if ($messages !== []) {
                throw ValidationException::withMessages($messages);
            }
        }

        if (blank($data['slug'] ?? null) && filled($data['title'] ?? null)) {
            $data['slug'] = Str::slug((string) $data['title']);
        }

        if (filled($data['slug'] ?? null)) {
            $data['slug'] = Str::slug((string) $data['slug']);
        }

        if (blank($data['excerpt'] ?? null)) {
            $data['excerpt'] = static::excerptFromDescription($data['description'] ?? null);
        } else {
            $data['excerpt'] = trim((string) $data['excerpt']);
        }

        $data['source_name'] = filled($data['source_name'] ?? null)
            ? trim((string) $data['source_name'])
            : null;
        $data['publication_date_text'] = filled($data['publication_date_text'] ?? null)
            ? trim((string) $data['publication_date_text'])
            : null;
        $data['language'] = filled($data['language'] ?? null) ? (string) $data['language'] : 'id';
        $data['external_url'] = filled($data['external_url'] ?? null)
            ? trim((string) $data['external_url'])
            : null;

        return $data;
    }

    public static function sharePreviewHtml(array $data): HtmlString
    {
        $title = trim((string) ($data['share_title'] ?: $data['seo_title'] ?: $data['title'] ?: 'Publikasi Edulaw Project'));
        $description = trim(preg_replace(
            '/\s+/',
            ' ',
            strip_tags((string) ($data['share_description'] ?: $data['seo_description'] ?: $data['excerpt'] ?: $data['description'] ?: 'Baca publikasi hukum, riset, dan kebijakan dari Edulaw Project.')),
        ) ?? '');
        $description = Str::limit($description, 180);
        $slug = trim((string) ($data['slug'] ?? ''));
        $url = $slug !== '' ? route('publications.show', Str::slug($slug)) : url('/riset-publikasi');
        $imageState = $data['og_image'] ?? null;
        $imageUrl = is_string($imageState) ? EdulawSite::assetUrl($imageState) : null;
        $image = $imageUrl
            ? '<img src="'.e($imageUrl).'" alt="" style="display:block;width:100%;height:7rem;object-fit:cover" onerror="this.remove()">'
            : '<div style="height:7rem;background:linear-gradient(135deg,#0f2a4a,#1f3c69 60%,#0f766e)"></div>';

        return new HtmlString(
            '<div style="overflow:hidden;border:1px solid #dbe2ea;border-radius:1rem;background:#fff;box-shadow:0 1px 3px rgba(15,42,74,.08)">'
            .$image
            .'<div style="padding:1rem">'
            .'<p style="margin:0;color:#0f2a4a;font-size:.9rem;font-weight:800;line-height:1.4">'.e($title).'</p>'
            .'<p style="margin:.45rem 0 0;color:#64748b;font-size:.75rem;line-height:1.5">'.e($description).'</p>'
            .'<p style="margin:.6rem 0 0;color:#0f766e;font-size:.68rem;font-weight:700;word-break:break-all">'.e($url).'</p>'
            .'</div></div>'
        );
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

    public static function statusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'reviewed' => 'Reviewed',
            'published' => 'Published',
        ];
    }

    public static function statusLabel(?string $status): string
    {
        return match ($status) {
            'draft' => 'Draft',
            'reviewed' => 'Reviewed',
            'published' => 'Published',
            'archived' => 'Archived',
            default => ucfirst((string) ($status ?: 'Draft')),
        };
    }

    public static function statusColor(?string $status): string
    {
        return match ($status) {
            'published' => 'success',
            'reviewed' => 'warning',
            'archived' => 'gray',
            default => 'primary',
        };
    }

    public static function normalizeStatusForForm(?string $status): string
    {
        return match ($status) {
            'reviewed', 'published' => $status,
            default => 'draft',
        };
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ViewColumn::make('publication')
                    ->label('Publikasi')
                    ->view('filament.tables.columns.resource-content', fn (Publication $record): array => [
                        'imageUrl' => $record->cover_image_url,
                        'isPortrait' => true,
                        'title' => $record->title,
                        'metadata' => [
                            $record->authors->pluck('name')->filter()->join(', ') ?: 'Edulaw Project',
                            $record->page_count ? $record->page_count.' halaman' : $record->source_name,
                        ],
                        'hasDocument' => filled($record->pdf_file),
                    ])
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->where(function (Builder $query) use ($search): void {
                            $query
                                ->where('title', 'like', "%{$search}%")
                                ->orWhere('source_name', 'like', "%{$search}%")
                                ->orWhereHas('authors', fn (Builder $query): Builder => $query->where('name', 'like', "%{$search}%"))
                                ->orWhereHas('type', fn (Builder $query): Builder => $query->where('name', 'like', "%{$search}%"));
                        }))
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('title', $direction))
                    ->url(fn (Publication $record): ?string => static::canEdit($record) ? static::getUrl('edit', ['record' => $record]) : null)
                    ->extraHeaderAttributes(['class' => 'edulaw-resource-primary-header'])
                    ->extraCellAttributes(['class' => 'edulaw-resource-primary-cell']),

                TextColumn::make('type.name')
                    ->label('Jenis')
                    ->badge()
                    ->color('primary')
                    ->limit(24)
                    ->tooltip(fn (?string $state): ?string => filled($state) && mb_strlen($state) > 24 ? $state : null)
                    ->sortable()
                    ->placeholder('—')
                    ->visibleFrom('lg')
                    ->extraHeaderAttributes(['class' => 'edulaw-resource-classification-header'])
                    ->extraCellAttributes(['class' => 'edulaw-resource-classification-cell']),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->color(fn (?string $state): string => static::statusColor($state))
                    ->formatStateUsing(fn (?string $state): string => $state === 'reviewed' ? 'Draft' : static::statusLabel($state))
                    ->extraHeaderAttributes(['class' => 'edulaw-resource-status-header'])
                    ->extraCellAttributes(['class' => 'edulaw-resource-status-cell']),

                TextColumn::make('published_at')
                    ->label('Terbit')
                    ->state(fn (Publication $record): string => $record->publication_date_display)
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable()
                    ->visibleFrom('xl')
                    ->extraHeaderAttributes(['class' => 'edulaw-resource-time-header'])
                    ->extraCellAttributes(['class' => 'edulaw-resource-time-cell']),

                IconColumn::make('pdf_file')
                    ->label('File PDF')
                    ->state(fn (Publication $record): bool => filled($record->pdf_file))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('source_name')
                    ->label('Sumber')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('featured')
                    ->label('Unggulan')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->formatStateUsing(fn ($state): string => $state->locale('id')->diffForHumans())
                    ->tooltip(fn (Publication $record): string => $record->updated_at->locale('id')->translatedFormat('d M Y, H:i'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort(fn (Builder $query): Builder => $query
                ->orderByDesc('published_at')
                ->orderByDesc('updated_at'))
            ->searchPlaceholder('Cari judul, penulis, atau jenis publikasi...')
            ->searchDebounce('500ms')
            ->paginationPageOptions([10, 25, 50])
            ->emptyStateHeading('Belum ada publikasi')
            ->emptyStateDescription('Publikasi riset dan dokumen yang diunggah akan tampil di sini.')
            ->filters([
                SelectFilter::make('publication_type_id')
                    ->label('Jenis Publikasi')
                    ->relationship('type', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Diarsipkan',
                    ])
                    ->query(function (Builder $query, array $data): void {
                        match ($data['value'] ?? null) {
                            'draft' => $query->whereIn('status', ['draft', 'reviewed']),
                            'published' => $query->where('status', 'published'),
                            'archived' => $query->where('status', 'archived'),
                            default => null,
                        };
                    }),

                SelectFilter::make('authors')
                    ->label('Penulis')
                    ->relationship('authors', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('featured')
                    ->label('Unggulan')
                    ->placeholder('Semua')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak'),

                TernaryFilter::make('has_pdf')
                    ->label('Ada PDF')
                    ->placeholder('Semua')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak')
                    ->queries(
                        true: fn (Builder $query): Builder => static::whereFilled($query, 'pdf_file'),
                        false: fn (Builder $query): Builder => static::whereBlank($query, 'pdf_file'),
                    ),

                Filter::make('published_at')
                    ->label('Rentang Tanggal Terbit')
                    ->schema([
                        DatePicker::make('from')->label('Dari tanggal')->native(false),
                        DatePicker::make('until')->label('Sampai tanggal')->native(false),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('published_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('published_at', '<=', $date)))
                    ->indicateUsing(fn (array $data): array => static::dateRangeIndicators($data, 'Terbit')),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view')
                        ->label('Lihat')
                        ->icon('heroicon-o-eye')
                        ->url(fn (Publication $record): string => route('publications.show', $record->slug))
                        ->openUrlInNewTab()
                        ->visible(fn (Publication $record): bool => filled($record->slug)
                            && $record->status === 'published'
                            && (! $record->published_at || $record->published_at->isPast())),
                    EditAction::make()->label('Edit'),
                    ReplicateAction::make()
                        ->label('Duplikasi')
                        ->visible(fn (): bool => static::canCreate())
                        ->mutateRecordDataUsing(fn (array $data, Publication $record): array => [
                            ...$data,
                            'title' => Str::limit($record->title.' (Salinan)', 255, ''),
                            'slug' => static::uniqueDuplicateSlug($record),
                            'status' => 'draft',
                            'published_at' => null,
                            'publication_date_text' => null,
                            'featured' => false,
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                        ])
                        ->after(function (Publication $record, Publication $replica): void {
                            $record->loadMissing(['authors', 'tags']);
                            $replica->authors()->sync($record->authors->mapWithKeys(fn ($author): array => [
                                $author->getKey() => [
                                    'author_order' => $author->pivot->author_order,
                                    'role' => $author->pivot->role,
                                ],
                            ])->all());
                            $replica->tags()->sync($record->tags->modelKeys());
                        }),
                    Action::make('archive')
                        ->label('Arsipkan')
                        ->icon('heroicon-o-archive-box')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn (Publication $record): bool => $record->status !== 'archived'
                            && (Auth::user()?->can('archive publications') ?? false))
                        ->action(fn (Publication $record) => $record->update(['status' => 'archived', 'updated_by' => Auth::id()])),
                    DeleteAction::make()->label('Hapus')->requiresConfirmation(),
                ])->label('Aksi lainnya')->icon('heroicon-o-ellipsis-vertical')->tooltip('Aksi lainnya')->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->label('Publikasikan')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->authorizeIndividualRecords('update')
                        ->visible(fn (): bool => Auth::user()?->can('publish publications') ?? false)
                        ->action(fn ($records) => $records->each->update(['status' => 'published', 'published_at' => now(), 'updated_by' => Auth::id()])),
                    BulkAction::make('archive')
                        ->label('Arsipkan')
                        ->icon('heroicon-o-archive-box')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->authorizeIndividualRecords('update')
                        ->visible(fn (): bool => Auth::user()?->can('archive publications') ?? false)
                        ->action(fn ($records) => $records->each->update(['status' => 'archived', 'updated_by' => Auth::id()])),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['authors:id,name', 'type:id,name']);
    }

    private static function dateRangeIndicators(array $data, string $label): array
    {
        return collect([
            ($data['from'] ?? null) ? Indicator::make($label.' mulai '.Carbon::parse($data['from'])->locale('id')->translatedFormat('d M Y'))->removeField('from') : null,
            ($data['until'] ?? null) ? Indicator::make($label.' sampai '.Carbon::parse($data['until'])->locale('id')->translatedFormat('d M Y'))->removeField('until') : null,
        ])->filter()->all();
    }

    public static function uniqueDuplicateSlug(Publication $record): string
    {
        $base = Str::limit(Str::slug($record->slug ?: $record->title).'-salinan', 240, '');
        $slug = $base;
        $suffix = 2;

        while (Publication::query()->where('slug', $slug)->exists()) {
            $slug = Str::limit($base, 240 - strlen((string) $suffix), '').'-'.$suffix++;
        }

        return $slug;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    public static function uniqueReferenceSlug(string $modelClass, string $value, string $fallback): string
    {
        $base = Str::slug($value) ?: $fallback;
        $slug = $base;
        $suffix = 2;

        while ($modelClass::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private static function whereBlank(Builder $query, string $column): Builder
    {
        return $query->where(fn (Builder $inner): Builder => $inner
            ->whereNull($column)
            ->orWhere($column, ''));
    }

    private static function whereFilled(Builder $query, string $column): Builder
    {
        return $query
            ->whereNotNull($column)
            ->where($column, '!=', '');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPublications::route('/'),
            'create' => CreatePublication::route('/create'),
            'edit' => EditPublication::route('/{record}/edit'),
        ];
    }
}
