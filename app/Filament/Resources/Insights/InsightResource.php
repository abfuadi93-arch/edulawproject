<?php

namespace App\Filament\Resources\Insights;

use App\Filament\Resources\Insights\InsightResource\Pages;
use App\Models\Insight;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InsightResource extends Resource
{
    protected static ?string $model = Insight::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Editorial';

    protected static ?string $modelLabel = 'Editorial';

    protected static ?string $pluralModelLabel = 'Editorial';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

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
                                Section::make('Konten Artikel')
                                    ->icon('heroicon-o-document-text')
                                    ->description('Fokus utama penulisan editorial: judul, kategori, isi, dan gambar utama.')
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Judul Editorial')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Pembakaran Buku Tidak Selalu Menggunakan Api')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($get, $set, ?string $old, ?string $state): void {
                                                $currentSlug = (string) ($get('slug') ?? '');

                                                if (filled($currentSlug) && $get('status') === 'published') {
                                                    return;
                                                }

                                                $oldSlug = Str::slug((string) $old);

                                                if (filled($currentSlug) && $currentSlug !== $oldSlug) {
                                                    return;
                                                }

                                                $set('slug', Str::slug((string) $state));
                                            })
                                            ->helperText('Gunakan judul yang jelas dan kuat.')
                                            ->columnSpanFull(),

                                        Grid::make([
                                            'default' => 1,
                                            'lg' => 2,
                                        ])
                                            ->schema([
                                                Select::make('insight_category_id')
                                                    ->label('Kategori')
                                                    ->relationship('category', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required(),

                                                Select::make('tags')
                                                    ->label('Topik')
                                                    ->relationship('tags', 'name')
                                                    ->multiple()
                                                    ->searchable()
                                                    ->preload(),
                                            ])
                                            ->columnSpanFull(),

                                        RichEditor::make('content')
                                            ->label('Isi Artikel')
                                            ->required()
                                            ->disableToolbarButtons(['h1'])
                                            ->rule(static function (): \Closure {
                                                return static function (string $attribute, mixed $value, \Closure $fail): void {
                                                    if (static::contentContainsH1(is_string($value) ? $value : null)) {
                                                        $fail('Isi artikel tidak boleh menggunakan H1. Gunakan H2 untuk bagian utama dan H3 untuk subbagian.');
                                                    }
                                                };
                                            })
                                            ->helperText('Gunakan H2 untuk bagian utama dan H3 untuk subbagian. H1 hanya untuk judul editorial.')
                                            ->columnSpanFull(),

                                        Textarea::make('excerpt')
                                            ->label('Ringkasan / Excerpt')
                                            ->rows(4)
                                            ->maxLength(500)
                                            ->required(fn ($get): bool => $get('status') === 'published')
                                            ->helperText('Wajib sebelum Published. Tulis ringkasan mandiri yang menjelaskan manfaat artikel.')
                                            ->columnSpanFull(),

                                        FileUpload::make('cover_image')
                                            ->label('Gambar Utama')
                                            ->image()
                                            ->disk('public')
                                            ->directory('insights')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->imagePreviewHeight('160')
                                            ->downloadable()
                                            ->openable()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->maxSize(4096)
                                            ->required(fn ($get): bool => $get('status') === 'published')
                                            ->helperText('Wajib sebelum Published. Rekomendasi rasio 16:9. Maks. 4 MB.')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('SEO & Pengaturan Lanjutan')
                                    ->icon('heroicon-o-magnifying-glass')
                                    ->description('Opsional. Jika kosong, sistem memakai judul, isi artikel, dan gambar utama.')
                                    ->schema([
                                        TextInput::make('slug')
                                            ->label('Slug')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->placeholder('otomatis-dari-judul')
                                            ->helperText('Alamat artikel. Otomatis dibuat dari judul, boleh disesuaikan sebelum terbit.')
                                            ->columnSpanFull(),

                                        TextInput::make('seo_title')
                                            ->label('SEO Title')
                                            ->maxLength(300)
                                            ->placeholder(fn ($get): string => $get('title') ?: 'Otomatis dari judul')
                                            ->helperText('Target 45–65 karakter. Gunakan judul natural; nama situs ditambahkan otomatis.'),

                                        Textarea::make('seo_description')
                                            ->label('Meta Description')
                                            ->rows(3)
                                            ->maxLength(180)
                                            ->placeholder('Otomatis dari awal isi artikel')
                                            ->helperText('Target 120–160 karakter. Jelaskan manfaat dan topik utama secara alami.'),

                                        FileUpload::make('og_image')
                                            ->label('Gambar OG')
                                            ->image()
                                            ->disk('public')
                                            ->directory('seo/og-images')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->maxSize(4096)
                                            ->downloadable()
                                            ->openable()
                                            ->helperText('Kosongkan untuk memakai gambar utama.'),
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
                                        Select::make('status')
                                            ->label('Status')
                                            ->options(fn (): array => static::statusOptionsForCurrentUser())
                                            ->default('draft')
                                            ->live()
                                            ->disabled(fn (string $operation): bool => $operation === 'create' && ! static::canManageEditorialWorkflow())
                                            ->required(),

                                        DateTimePicker::make('published_at')
                                            ->label('Tanggal Publikasi')
                                            ->seconds(false)
                                            ->disabled(fn (): bool => ! static::canManageEditorialWorkflow()),

                                        Select::make('authors')
                                            ->label('Penulis')
                                            ->relationship('authors', 'name')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->required(fn ($get): bool => $get('status') === 'published')
                                            ->placeholder('Pilih penulis')
                                            ->helperText('Wajib sebelum Published. Draft dapat disimpan tanpa penulis.'),

                                        Placeholder::make('reading_time_preview')
                                            ->label('Estimasi Baca')
                                            ->content(fn ($get): string => static::estimateReadingTime($get('content')).' menit baca')
                                            ->helperText('Dihitung otomatis saat artikel disimpan.'),

                                        Placeholder::make('public_preview')
                                            ->label('Pratinjau')
                                            ->content(function ($get): HtmlString {
                                                $slug = trim((string) ($get('slug') ?? ''));

                                                if ($slug === '') {
                                                    return new HtmlString('<span class="text-sm text-gray-500">Tersedia setelah judul dan slug terisi.</span>');
                                                }

                                                $url = route('insights.show', $slug);

                                                return new HtmlString(
                                                    '<a href="'.e($url).'" target="_blank" rel="noopener noreferrer" class="fi-btn fi-color-gray fi-size-sm">Buka pratinjau</a>'
                                                );
                                            }),
                                    ])
                                    ->columns(1),

                                Section::make('Penempatan Editorial')
                                    ->icon('heroicon-o-squares-2x2')
                                    ->description('Atur posisi artikel pada halaman Editorial. Angka lebih kecil mendapat prioritas lebih tinggi.')
                                    ->schema([
                                        Toggle::make('editor_pick')
                                            ->label('Pilihan Editor')
                                            ->helperText('Tampilkan pada bagian Pilihan Editor.')
                                            ->default(false)
                                            ->disabled(fn (): bool => ! static::canManageEditorialWorkflow()),

                                        Toggle::make('featured')
                                            ->label('Artikel Unggulan')
                                            ->helperText('Prioritaskan sebagai artikel utama atau hero editorial.')
                                            ->default(false)
                                            ->disabled(fn (): bool => ! static::canManageEditorialWorkflow()),

                                        TextInput::make('sort_order')
                                            ->label('Urutan Tampil')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0)
                                            ->disabled(fn (): bool => ! static::canManageEditorialWorkflow())
                                            ->helperText('Gunakan 0 untuk prioritas tertinggi.'),
                                    ])
                                    ->columns(1)
                                    ->collapsible(),
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
        if (static::contentContainsH1($data['content'] ?? null)) {
            throw ValidationException::withMessages([
                'content' => 'Isi artikel tidak boleh menggunakan H1. Gunakan H2 untuk bagian utama dan H3 untuk subbagian.',
            ]);
        }

        if (($data['status'] ?? null) === 'published') {
            $messages = [];

            if (blank($data['cover_image'] ?? null)) {
                $messages['cover_image'] = 'Gambar utama wajib diisi sebelum artikel diterbitkan.';
            }

            if (blank($data['excerpt'] ?? null)) {
                $messages['excerpt'] = 'Ringkasan wajib diisi sebelum artikel diterbitkan.';
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

        $data['reading_time'] = static::estimateReadingTime($data['content'] ?? null);

        if (blank($data['excerpt'] ?? null)) {
            $data['excerpt'] = static::excerptFromContent($data['content'] ?? null);
        } else {
            $data['excerpt'] = trim((string) $data['excerpt']);
        }

        if (blank($data['seo_title'] ?? null) && filled($data['title'] ?? null)) {
            $data['seo_title'] = (string) $data['title'];
        }

        if (blank($data['seo_description'] ?? null) && filled($data['excerpt'] ?? null)) {
            $data['seo_description'] = static::excerptFromContent((string) $data['excerpt'], 180);
        }

        if (blank($data['og_image'] ?? null) && filled($data['cover_image'] ?? null)) {
            $data['og_image'] = $data['cover_image'];
        }

        return $data;
    }

    public static function contentContainsH1(?string $html): bool
    {
        return preg_match('/<h1\b[^>]*>/i', (string) $html) === 1;
    }

    public static function publishReadinessIssues(Insight $insight): array
    {
        $authorsCount = array_key_exists('authors_count', $insight->getAttributes())
            ? (int) $insight->getAttribute('authors_count')
            : $insight->authors()->count();

        return collect([
            blank($insight->cover_image) ? 'gambar utama' : null,
            blank($insight->excerpt) ? 'ringkasan' : null,
            $authorsCount < 1 ? 'penulis' : null,
            static::contentContainsH1($insight->content) ? 'hapus H1 dari isi artikel' : null,
        ])->filter()->values()->all();
    }

    public static function isPublishReady(Insight $insight): bool
    {
        return static::publishReadinessIssues($insight) === [];
    }

    public static function excerptFromContent(?string $html, int $limit = 220): ?string
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

    public static function estimateReadingTime(?string $html): int
    {
        $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $html), ENT_QUOTES, 'UTF-8')) ?? '');

        if ($text === '') {
            return 1;
        }

        preg_match_all('/[\p{L}\p{N}]+(?:[\'’.-][\p{L}\p{N}]+)*/u', $text, $matches);

        return max(1, (int) ceil(count($matches[0] ?? []) / 200));
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->withCount('authors');
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if (
            Gate::forUser($user)->allows('update all insights')
            || Gate::forUser($user)->allows('review insights')
            || Gate::forUser($user)->allows('publish insights')
        ) {
            return $query;
        }

        return $query->where('created_by', $user->id);
    }

    public static function canManageEditorialWorkflow(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return Gate::forUser($user)->allows('update all insights')
            || Gate::forUser($user)->allows('review insights')
            || Gate::forUser($user)->allows('publish insights')
            || Gate::forUser($user)->allows('archive insights');
    }

    public static function statusOptionsForCurrentUser(): array
    {
        if (! static::canManageEditorialWorkflow()) {
            return [
                'draft' => 'Draft',
                'reviewed' => 'Reviewed',
            ];
        }

        return static::statusOptions();
    }

    public static function statusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'reviewed' => 'Reviewed',
            'published' => 'Published',
        ];
    }

    public static function normalizeStatusForDisplay(?string $status): string
    {
        return match ($status) {
            'submitted' => 'reviewed',
            'archived' => 'draft',
            'reviewed', 'published' => $status,
            default => 'draft',
        };
    }

    public static function statusLabel(?string $status): string
    {
        return static::statusOptions()[static::normalizeStatusForDisplay($status)] ?? 'Draft';
    }

    public static function statusColor(?string $status): string
    {
        return match (static::normalizeStatusForDisplay($status)) {
            'published' => 'success',
            'reviewed' => 'warning',
            default => 'primary',
        };
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Artikel')
                    ->searchable()
                    ->sortable()
                    ->limit(72)
                    ->wrap()
                    ->description(function (Insight $record): string {
                        $published = $record->published_at?->translatedFormat('d M Y');

                        return collect([
                            $record->display_author,
                            $published ? "Rilis {$published}" : null,
                        ])->filter()->join(' - ');
                    }),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('primary')
                    ->placeholder('Belum dikategorikan')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => static::statusColor($state))
                    ->formatStateUsing(fn (?string $state): string => static::statusLabel($state)),

                IconColumn::make('publish_ready')
                    ->label('Siap')
                    ->state(fn (Insight $record): bool => static::isPublishReady($record))
                    ->boolean()
                    ->tooltip(function (Insight $record): string {
                        $issues = static::publishReadinessIssues($record);

                        return $issues === []
                            ? 'Siap diterbitkan'
                            : 'Lengkapi: '.implode(', ', $issues);
                    }),

                IconColumn::make('featured')
                    ->label('Unggulan')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('editor_pick')
                    ->label('Pilihan')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('published_at')
                    ->label('Terbit')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Update')
                    ->since()
                    ->sinceTooltip()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->searchPlaceholder('Cari artikel...')
            ->paginationPageOptions([10, 25, 50])
            ->emptyStateHeading('Belum ada editorial')
            ->emptyStateDescription('Artikel editorial yang dibuat dari panel admin akan tampil di sini.')
            ->filters([
                SelectFilter::make('insight_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options(static::statusOptions())
                    ->query(function (Builder $query, array $data): void {
                        $status = $data['value'] ?? null;

                        match ($status) {
                            'draft' => $query->whereIn('status', ['draft', 'archived']),
                            'reviewed' => $query->whereIn('status', ['reviewed', 'submitted']),
                            'published' => $query->where('status', 'published'),
                            default => null,
                        };
                    }),

                SelectFilter::make('featured')
                    ->label('Unggulan')
                    ->options([
                        '1' => 'Unggulan',
                        '0' => 'Bukan Unggulan',
                    ]),

                SelectFilter::make('editor_pick')
                    ->label('Pilihan Editor')
                    ->options([
                        '1' => 'Pilihan Editor',
                        '0' => 'Bukan Pilihan Editor',
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
            'index' => Pages\ListInsights::route('/'),
            'create' => Pages\CreateInsight::route('/create'),
            'edit' => Pages\EditInsight::route('/{record}/edit'),
        ];
    }
}
