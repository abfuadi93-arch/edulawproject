<?php

namespace App\Filament\Resources\Publications;

use App\Filament\Resources\Publications\Pages\CreatePublication;
use App\Filament\Resources\Publications\Pages\EditPublication;
use App\Filament\Resources\Publications\Pages\ListPublications;
use App\Models\Publication;
use App\Support\EdulawSite;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PublicationResource extends Resource
{
    protected static ?string $model = Publication::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Publications';

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
                                                    ->required(fn ($get): bool => $get('status') === 'published')
                                                    ->validationMessages([
                                                        'required' => 'Publikasi yang diterbitkan wajib memiliki tipe publikasi.',
                                                    ]),

                                                Select::make('authors')
                                                    ->label('Penulis')
                                                    ->relationship('authors', 'name')
                                                    ->multiple()
                                                    ->searchable()
                                                    ->preload()
                                                    ->required(fn ($get): bool => $get('status') === 'published')
                                                    ->placeholder('Pilih penulis')
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
                                                    ->label('Tanggal Publikasi')
                                                    ->required(fn ($get): bool => $get('status') === 'published')
                                                    ->validationMessages([
                                                        'required' => 'Publikasi yang diterbitkan wajib memiliki tanggal publikasi.',
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

                                        RichEditor::make('description')
                                            ->label('Deskripsi Lengkap')
                                            ->helperText('Opsional, tetapi disarankan untuk memberi konteks lebih lengkap.')
                                            ->columnSpanFull(),

                                        Grid::make(['default' => 1, 'lg' => 2])
                                            ->schema([
                                                TextInput::make('page_count')
                                                    ->label('Jumlah Halaman')
                                                    ->numeric()
                                                    ->minValue(1)
                                                    ->nullable()
                                                    ->suffix('halaman'),

                                                TextInput::make('source_name')
                                                    ->label('Sumber / Penerbit')
                                                    ->maxLength(255)
                                                    ->placeholder('Edulaw Project')
                                                    ->helperText('Jika kosong, frontend memakai “Edulaw Project”.'),

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
                                                    ->preload(),
                                            ])
                                            ->columnSpanFull(),
                                    ]),

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

                                        FileUpload::make('cover_image')
                                            ->label('Gambar Sampul')
                                            ->image()
                                            ->disk('public')
                                            ->directory('publications/covers')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->imagePreviewHeight('180')
                                            ->maxSize(4096)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->downloadable()
                                            ->openable()
                                            ->helperText('Gunakan cover rasio 16:9 atau A4 portrait yang rapi. Cover digunakan untuk card publikasi dan share preview.')
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
                                                'cover_image' => $get('cover_image'),
                                                'slug' => $get('slug'),
                                            ])),
                                    ]),

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
                                            ->helperText('Jika kosong, share preview memakai gambar sampul.'),
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

            if (blank($data['published_at'] ?? null)) {
                $messages['published_at'] = 'Publikasi yang diterbitkan wajib memiliki tanggal publikasi.';
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
        $imageState = $data['og_image'] ?: $data['cover_image'];
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
                ImageColumn::make('cover_image')
                    ->label('Cover')
                    ->disk('public')
                    ->imageSize(48)
                    ->square()
                    ->defaultImageUrl(asset('images/logo/icon-bg.png')),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(60)
                    ->wrap()
                    ->description(fn (Publication $record): ?string => $record->excerpt),

                TextColumn::make('type.name')
                    ->label('Tipe')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('authors.name')
                    ->label('Penulis')
                    ->badge()
                    ->color('gray')
                    ->separator(',')
                    ->limitList(2)
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->color(fn (?string $state): string => static::statusColor($state))
                    ->formatStateUsing(fn (?string $state): string => static::statusLabel($state)),

                IconColumn::make('featured')
                    ->label('Unggulan')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Tanggal Terbit')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('page_count')
                    ->label('Halaman')
                    ->suffix(' hlm')
                    ->sortable()
                    ->placeholder('-'),

                IconColumn::make('has_pdf')
                    ->label('PDF')
                    ->state(fn (Publication $record): bool => filled($record->pdf_file))
                    ->boolean(),

                IconColumn::make('has_external_url')
                    ->label('URL Eksternal')
                    ->state(fn (Publication $record): bool => filled($record->external_url))
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort(fn (Builder $query): Builder => $query
                ->orderByDesc('published_at')
                ->orderByDesc('updated_at'))
            ->searchPlaceholder('Cari publikasi...')
            ->paginationPageOptions([10, 25, 50])
            ->emptyStateHeading('Belum ada publikasi')
            ->emptyStateDescription('Publikasi riset dan dokumen yang diunggah akan tampil di sini.')
            ->filters([
                SelectFilter::make('publication_type_id')
                    ->label('Tipe Publikasi')
                    ->relationship('type', 'name'),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options(static::statusOptions()),

                TernaryFilter::make('featured')
                    ->label('Publikasi Unggulan'),

                TernaryFilter::make('without_pdf')
                    ->label('Ketersediaan PDF')
                    ->trueLabel('Tanpa PDF')
                    ->falseLabel('Dengan PDF')
                    ->queries(
                        true: fn (Builder $query): Builder => static::whereBlank($query, 'pdf_file'),
                        false: fn (Builder $query): Builder => static::whereFilled($query, 'pdf_file'),
                    ),

                TernaryFilter::make('without_cover')
                    ->label('Ketersediaan Cover')
                    ->trueLabel('Tanpa cover')
                    ->falseLabel('Dengan cover')
                    ->queries(
                        true: fn (Builder $query): Builder => static::whereBlank($query, 'cover_image'),
                        false: fn (Builder $query): Builder => static::whereFilled($query, 'cover_image'),
                    ),

                TernaryFilter::make('without_excerpt')
                    ->label('Ketersediaan Ringkasan')
                    ->trueLabel('Tanpa ringkasan')
                    ->falseLabel('Dengan ringkasan')
                    ->queries(
                        true: fn (Builder $query): Builder => static::whereBlank($query, 'excerpt'),
                        false: fn (Builder $query): Builder => static::whereFilled($query, 'excerpt'),
                    ),

                TernaryFilter::make('without_author')
                    ->label('Ketersediaan Penulis')
                    ->trueLabel('Tanpa penulis')
                    ->falseLabel('Dengan penulis')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereDoesntHave('authors'),
                        false: fn (Builder $query): Builder => $query->whereHas('authors'),
                    ),

                TernaryFilter::make('with_external_url')
                    ->label('External URL')
                    ->trueLabel('Dengan External URL')
                    ->falseLabel('Tanpa External URL')
                    ->queries(
                        true: fn (Builder $query): Builder => static::whereFilled($query, 'external_url'),
                        false: fn (Builder $query): Builder => static::whereBlank($query, 'external_url'),
                    ),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
