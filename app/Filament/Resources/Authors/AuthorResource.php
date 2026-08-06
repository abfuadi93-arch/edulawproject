<?php

namespace App\Filament\Resources\Authors;

use App\Filament\Resources\Authors\Pages\CreateAuthor;
use App\Filament\Resources\Authors\Pages\EditAuthor;
use App\Filament\Resources\Authors\Pages\ListAuthors;
use App\Filament\Resources\Insights\InsightResource;
use App\Filament\Resources\Publications\PublicationResource;
use App\Models\Author;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class AuthorResource extends Resource
{
    protected static ?string $model = Author::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Referensi';

    protected static ?string $navigationLabel = 'Profil Kontributor';

    protected static ?string $modelLabel = 'Profil Kontributor';

    protected static ?string $pluralModelLabel = 'Profil Kontributor';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->extraAttributes(['class' => 'edulaw-admin-profile-form'])
            ->components([
                Grid::make([
                    'default' => 1,
                    'lg' => 5,
                ])
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make('Informasi Profil')
                                    ->icon('heroicon-o-user-circle')
                                    ->description('Identitas publik yang tampil di laman tentang dan konten terkait.')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nama')
                                            ->required()
                                            ->maxLength(255)
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

                                        TextInput::make('title')
                                            ->label('Gelar')
                                            ->maxLength(100)
                                            ->placeholder('S.H., M.H.')
                                            ->columnSpanFull(),

                                        Grid::make([
                                            'default' => 1,
                                            'lg' => 2,
                                        ])
                                            ->schema([
                                                TextInput::make('position')
                                                    ->label('Jabatan')
                                                    ->maxLength(255)
                                                    ->placeholder('Director / Manager Editorial / Writer'),

                                                TextInput::make('institution')
                                                    ->label('Afiliasi')
                                                    ->maxLength(255)
                                                    ->placeholder('Edulaw Project / Institusi asal'),

                                                TextInput::make('location')
                                                    ->label('Lokasi')
                                                    ->maxLength(255)
                                                    ->placeholder('Jakarta, Indonesia'),

                                                Select::make('profile_type')
                                                    ->label('Peran Publik')
                                                    ->options(Author::PROFILE_TYPES)
                                                    ->searchable()
                                                    ->default('team')
                                                    ->required()
                                                    ->placeholder('Pilih tingkat peran')
                                                    ->helperText('Founder dan Co-Founder ditetapkan secara statis pada halaman Tentang.'),

                                                Toggle::make('show_in_organization')
                                                    ->label('Tampilkan di Struktur Organisasi')
                                                    ->default(true)
                                                    ->helperText('Matikan untuk profil publik yang tidak masuk struktur organisasi.'),

                                                Toggle::make('show_in_contributor_section')
                                                    ->label('Tampilkan di Kontributor Editorial')
                                                    ->default(false)
                                                    ->helperText('Hanya profil aktif dengan toggle ini yang tampil pada bagian Kontributor Editorial.'),

                                                Toggle::make('is_active')
                                                    ->label('Status Aktif')
                                                    ->default(true),
                                            ])
                                            ->columnSpanFull(),

                                        Textarea::make('bio')
                                            ->label('Bio Profil')
                                            ->rows(6)
                                            ->maxLength(1000)
                                            ->live()
                                            ->helperText('Maksimal 1000 karakter.')
                                            ->hint(fn (?string $state): string => sprintf('%d/1000 karakter', mb_strlen((string) $state)))
                                            ->placeholder('Tulis bio profil yang dapat ditampilkan pada website.')
                                            ->columnSpanFull(),

                                        TagsInput::make('interests')
                                            ->label('Minat / Keahlian')
                                            ->separator(',')
                                            ->splitKeys([',', 'Enter'])
                                            ->placeholder('Tambah minat atau keahlian...')
                                            ->helperText('Pisahkan tiap minat dengan enter atau koma.')
                                            ->suggestions([
                                                'Hukum Tata Negara',
                                                'Pemilu',
                                                'Kebijakan Publik',
                                                'Hukum Administrasi',
                                                'Advokasi',
                                                'Riset Hukum',
                                            ])
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Pengaturan Lanjutan')
                                    ->icon('heroicon-o-cog-6-tooth')
                                    ->description('Opsional. Slug dan urutan hanya perlu dibuka bila ingin disesuaikan.')
                                    ->schema([
                                        TextInput::make('slug')
                                            ->label('Slug')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->helperText('Otomatis dari nama.')
                                            ->columnSpanFull(),

                                        TextInput::make('sort_order')
                                            ->label('Urutan Tampil')
                                            ->numeric()
                                            ->minValue(0)
                                            ->placeholder('Opsional, contoh: 10')
                                            ->helperText('Opsional. Kontributor Editorial diurutkan otomatis berdasarkan jumlah tulisan; angka ini hanya digunakan saat jumlahnya sama.')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1)
                                    ->collapsible()
                                    ->collapsed(),
                            ])
                            ->columnSpan([
                                'default' => 1,
                                'lg' => 3,
                            ])
                            ->extraAttributes(['class' => 'edulaw-admin-profile-info']),

                        Group::make()
                            ->schema([
                                Section::make('Foto Profil')
                                    ->icon('heroicon-o-photo')
                                    ->description('Rekomendasi ukuran 400 x 400 px.')
                                    ->schema([
                                        FileUpload::make('photo')
                                            ->label('Photo')
                                            ->image()
                                            ->disk('public')
                                            ->directory('authors')
                                            ->visibility('public')
                                            ->avatar()
                                            ->imageEditor()
                                            ->imageCropAspectRatio('1:1')
                                            ->imageEditorAspectRatios(['1:1'])
                                            ->maxSize(4096)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->imagePreviewHeight('180')
                                            ->helperText('Gunakan foto rasio 1:1, minimal 400 × 400 px. Jika kosong, frontend akan memakai avatar inisial.')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Akun Admin dan Kontak (Opsional)')
                                    ->icon('heroicon-o-identification')
                                    ->description('Opsional untuk profil eksternal.')
                                    ->schema([
                                        Select::make('user_id')
                                            ->label('Akun Admin Terkait')
                                            ->relationship('user', 'name')
                                            ->unique(Author::class, 'user_id', ignoreRecord: true)
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Profil publik tanpa akun admin'),

                                        TextInput::make('email')
                                            ->label('Email Kontak')
                                            ->email()
                                            ->maxLength(255),

                                    ])
                                    ->columns(1),

                                Section::make('Tautan Sosial')
                                    ->icon('heroicon-o-link')
                                    ->schema([
                                        TextInput::make('social_links.website')->label('Website')->url()->maxLength(500),
                                        TextInput::make('social_links.linkedin')->label('LinkedIn')->url()->maxLength(500),
                                        TextInput::make('social_links.google_scholar')->label('Google Scholar')->url()->maxLength(500),
                                        TextInput::make('social_links.orcid')->label('ORCID')->maxLength(100),
                                        TextInput::make('social_links.scopus')->label('Scopus ID')->maxLength(100),
                                        TextInput::make('social_links.instagram')->label('Instagram')->url()->maxLength(500),
                                        TextInput::make('social_links.twitter')->label('Twitter / X')->url()->maxLength(500),
                                        TextInput::make('social_links.youtube')->label('YouTube')->url()->maxLength(500),
                                        TextInput::make('social_links.researchgate')->label('ResearchGate')->url()->maxLength(500),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),

                                Section::make('SEO Profil')
                                    ->icon('heroicon-o-magnifying-glass')
                                    ->schema([
                                        TextInput::make('seo_title')
                                            ->label('SEO Title')
                                            ->maxLength(300)
                                            ->helperText('Target 45–65 karakter. Gunakan judul natural; nama situs ditambahkan otomatis.'),

                                        Textarea::make('meta_description')
                                            ->label('Meta Description')
                                            ->rows(4)
                                            ->maxLength(180)
                                            ->helperText('Target 120–160 karakter. Jelaskan profil dan bidang kontribusi secara alami.'),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),
                            ])
                            ->columnSpan([
                                'default' => 1,
                                'lg' => 2,
                            ]),
                    ])
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'edulaw-admin-profile-form']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->extraAttributes(['class' => 'edulaw-profile-table edulaw-author-table'])
            ->columns([
                ViewColumn::make('profile')
                    ->label('Profil')
                    ->view('filament.tables.columns.profile-identity', fn (Author $record): array => [
                        'avatar' => static::avatarHtml($record),
                        'name' => $record->name,
                        'position' => $record->position,
                        'email' => $record->email,
                        'institution' => $record->institution,
                        'insightsCount' => (int) $record->insights_count,
                        'publicationsCount' => (int) $record->publications_count,
                    ])
                    ->searchable(['name', 'email', 'institution', 'position'])
                    ->sortable()
                    ->extraHeaderAttributes(['class' => 'edulaw-profile-primary-header'])
                    ->extraCellAttributes(['class' => 'edulaw-profile-primary-cell']),

                TextColumn::make('institution')
                    ->label('Institusi')
                    ->tooltip(fn (?string $state): ?string => filled($state) ? $state : null)
                    ->placeholder('—')
                    ->visibleFrom('xl')
                    ->extraHeaderAttributes(['class' => 'edulaw-author-institution-header'])
                    ->extraCellAttributes(['class' => 'edulaw-author-institution-cell']),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->sortable()
                    ->extraHeaderAttributes(['class' => 'edulaw-author-status-header'])
                    ->extraCellAttributes(['class' => 'edulaw-author-status-cell']),

                TextColumn::make('insights_count')
                    ->label('Artikel')
                    ->numeric()
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'primary' : 'gray')
                    ->sortable()
                    ->visibleFrom('md')
                    ->alignment('center')
                    ->extraHeaderAttributes(['class' => 'edulaw-author-count-header'])
                    ->extraCellAttributes(['class' => 'edulaw-author-count-cell']),

                TextColumn::make('publications_count')
                    ->label('Publikasi')
                    ->numeric()
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'info' : 'gray')
                    ->sortable()
                    ->visibleFrom('md')
                    ->alignment('center')
                    ->extraHeaderAttributes(['class' => 'edulaw-author-count-header'])
                    ->extraCellAttributes(['class' => 'edulaw-author-count-cell']),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->date('d M Y')
                    ->sortable()
                    ->visibleFrom('xl')
                    ->tooltip(fn (Author $record): string => $record->updated_at->format('d M Y, H:i'))
                    ->extraHeaderAttributes(['class' => 'edulaw-author-updated-header'])
                    ->extraCellAttributes(['class' => 'edulaw-author-updated-cell']),

                TextColumn::make('email')
                    ->label('Email')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('user.name')
                    ->label('User Terhubung')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('show_in_contributor_section')
                    ->label('Tampil di Kontributor')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->searchPlaceholder('Cari nama, institusi, atau jabatan...')
            ->paginationPageOptions([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateHeading('Belum ada profil kontributor')
            ->emptyStateDescription('Tambahkan profil penulis atau kontributor internal dan eksternal.')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),

                SelectFilter::make('institution')
                    ->label('Institusi')
                    ->options(fn (): array => Author::query()->whereNotNull('institution')->where('institution', '!=', '')->distinct()->orderBy('institution')->pluck('institution', 'institution')->all())
                    ->searchable(),

                TernaryFilter::make('has_user')
                    ->label('Akun User')
                    ->placeholder('Semua')
                    ->trueLabel('Memiliki akun user')
                    ->falseLabel('Tanpa akun user')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('user_id'),
                        false: fn (Builder $query): Builder => $query->whereNull('user_id'),
                    ),

                TernaryFilter::make('show_in_contributor_section')
                    ->label('Tampil di Kontributor Editorial'),

                TernaryFilter::make('has_insights')
                    ->label('Artikel')
                    ->trueLabel('Memiliki artikel')
                    ->falseLabel('Tanpa artikel')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->has('insights'),
                        false: fn (Builder $query): Builder => $query->doesntHave('insights'),
                    ),

                TernaryFilter::make('has_publications')
                    ->label('Publikasi')
                    ->trueLabel('Memiliki publikasi')
                    ->falseLabel('Tanpa publikasi')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->has('publications'),
                        false: fn (Builder $query): Builder => $query->doesntHave('publications'),
                    ),
            ])
            ->defaultSort(fn (Builder $query): Builder => $query
                ->orderBy('sort_order')
                ->orderBy('name'))
            ->recordActions([
                ActionGroup::make([
                    Action::make('view_public')
                        ->label('Lihat Profil Publik')
                        ->icon('heroicon-o-eye')
                        ->url(fn (Author $record): string => route('profiles.show', $record->slug))
                        ->openUrlInNewTab()
                        ->visible(fn (Author $record): bool => $record->is_active && filled($record->slug)),
                    EditAction::make()->label('Edit'),
                    Action::make('view_insights')
                        ->label('Lihat Artikel')
                        ->icon('heroicon-o-newspaper')
                        ->url(fn (Author $record): string => InsightResource::getUrl('index', ['tableSearch' => $record->name])),
                    Action::make('view_publications')
                        ->label('Lihat Publikasi')
                        ->icon('heroicon-o-book-open')
                        ->url(fn (Author $record): string => PublicationResource::getUrl('index', ['tableSearch' => $record->name])),
                    Action::make('toggle_active')
                        ->label(fn (Author $record): string => $record->is_active ? 'Nonaktifkan' : 'Aktifkan')
                        ->icon(fn (Author $record): string => $record->is_active ? 'heroicon-o-no-symbol' : 'heroicon-o-check-circle')
                        ->color(fn (Author $record): string => $record->is_active ? 'warning' : 'success')
                        ->authorize('update')
                        ->requiresConfirmation()
                        ->action(fn (Author $record) => $record->update(['is_active' => ! $record->is_active])),
                    DeleteAction::make()
                        ->label('Hapus')
                        ->requiresConfirmation()
                        ->visible(fn (Author $record): bool => blank($record->user_id) && $record->insights_count === 0 && $record->publications_count === 0),
                ])->label('Aksi lainnya')->icon('heroicon-o-ellipsis-vertical')->tooltip('Aksi lainnya')->color('gray'),
            ])
            ->recordActionsAlignment('center')
            ->recordActionsColumnLabel('');
    }

    public static function avatarHtml(Author $author): HtmlString
    {
        $name = e($author->name);
        $initials = e($author->initials);
        $photo = $author->photo_url;
        $image = filled($photo)
            ? sprintf(
                '<img src="%s" alt="Foto profil %s" style="position:absolute;inset:0;width:100%%;height:100%%;object-fit:cover" onerror="this.remove()">',
                e((string) $photo),
                $name,
            )
            : '';

        return new HtmlString(sprintf(
            '<span aria-label="Avatar %s" style="position:relative;display:inline-flex;width:2.75rem;height:2.75rem;flex:none;align-items:center;justify-content:center;overflow:hidden;border-radius:9999px;background:#0f2a4a;color:#fff;font-size:.75rem;font-weight:800;letter-spacing:.04em;box-shadow:0 0 0 1px rgba(15,42,74,.12)">%s%s</span>',
            $name,
            $initials,
            $image,
        ));
    }

    public static function prepareFormDataForPersistence(array $data, ?int $ignoreId = null): array
    {
        if (array_key_exists('profile_type', $data)) {
            $data['profile_type'] = Author::canonicalProfileType($data['profile_type']) ?? 'team';
        }

        if (blank($data['slug'] ?? null) && filled($data['name'] ?? null)) {
            $data['slug'] = Author::uniqueSlugFor((string) $data['name'], $ignoreId);
        }

        if (filled($data['slug'] ?? null)) {
            $data['slug'] = Str::slug((string) $data['slug']);
        }

        if (array_key_exists('sort_order', $data)) {
            $data['sort_order'] = filled($data['sort_order'])
                ? max(0, (int) $data['sort_order'])
                : null;
        }

        if (isset($data['social_links']) && is_array($data['social_links'])) {
            $data['social_links'] = (new Author(['social_links' => $data['social_links']]))->socialLinksMap();
        }

        return $data;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('user:id,name')->withCount(['insights', 'publications']);
        $user = auth()->user();

        if (! $user || $user->can('update authors')) {
            return $query;
        }

        return $query->where('user_id', $user->id);
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
            'index' => ListAuthors::route('/'),
            'create' => CreateAuthor::route('/create'),
            'edit' => EditAuthor::route('/{record}/edit'),
        ];
    }
}
