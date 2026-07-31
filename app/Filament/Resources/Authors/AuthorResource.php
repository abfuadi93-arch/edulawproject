<?php

namespace App\Filament\Resources\Authors;

use App\Filament\Resources\Authors\Pages\CreateAuthor;
use App\Filament\Resources\Authors\Pages\EditAuthor;
use App\Filament\Resources\Authors\Pages\ListAuthors;
use App\Models\Author;
use BackedEnum;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class AuthorResource extends Resource
{
    protected static ?string $model = Author::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Reference';

    protected static ?string $navigationLabel = 'Profil';

    protected static ?string $modelLabel = 'Profil';

    protected static ?string $pluralModelLabel = 'Profil';

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
                                                    ->placeholder('Founder / Manager Editorial / Writer'),

                                                TextInput::make('institution')
                                                    ->label('Afiliasi')
                                                    ->maxLength(255)
                                                    ->placeholder('Edulaw Project / Institusi asal'),

                                                TextInput::make('location')
                                                    ->label('Lokasi')
                                                    ->maxLength(255)
                                                    ->placeholder('Jakarta, Indonesia'),

                                                Select::make('profile_type')
                                                    ->label('Peran Profil')
                                                    ->options(Author::PROFILE_TYPES)
                                                    ->searchable()
                                                    ->default('team')
                                                    ->required()
                                                    ->placeholder('Pilih peran profil'),

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
                                            ->default(0)
                                            ->required()
                                            ->minValue(0)
                                            ->placeholder('Contoh: 10')
                                            ->helperText('Angka kecil tampil lebih dulu.')
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
            ->columns([
                TextColumn::make('photo')
                    ->label('Foto / Avatar')
                    ->state(fn (Author $record): HtmlString => static::avatarHtml($record))
                    ->html()
                    ->width(64)
                    ->grow(false),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->limit(42)
                    ->wrap()
                    ->description(fn (Author $record): ?string => $record->profile_type_label),

                TextColumn::make('position')
                    ->label('Jabatan')
                    ->searchable()
                    ->limit(36)
                    ->wrap()
                    ->placeholder('-'),

                TextColumn::make('institution')
                    ->label('Institusi')
                    ->searchable()
                    ->limit(36)
                    ->wrap()
                    ->placeholder('-'),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('show_in_contributor_section')
                    ->label('Kontributor Editorial')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('show_in_organization')
                    ->label('Struktur')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Akun Admin')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('profile_type')
                    ->label('Peran Profil')
                    ->options(Author::PROFILE_TYPES),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),

                TernaryFilter::make('show_in_contributor_section')
                    ->label('Tampil di Kontributor Editorial'),

                TernaryFilter::make('without_photo')
                    ->label('Foto Profil')
                    ->placeholder('Semua profil')
                    ->trueLabel('Tanpa foto')
                    ->falseLabel('Dengan foto')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->where(
                            fn (Builder $photoQuery): Builder => $photoQuery
                                ->whereNull('photo')
                                ->orWhere('photo', '')
                        ),
                        false: fn (Builder $query): Builder => $query
                            ->whereNotNull('photo')
                            ->where('photo', '!=', ''),
                    ),
            ])
            ->defaultSort(fn (Builder $query): Builder => $query
                ->orderBy('sort_order')
                ->orderBy('name'))
            ->recordActions([
                EditAction::make(),
            ]);
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
        if (blank($data['slug'] ?? null) && filled($data['name'] ?? null)) {
            $data['slug'] = Author::uniqueSlugFor((string) $data['name'], $ignoreId);
        }

        if (filled($data['slug'] ?? null)) {
            $data['slug'] = Str::slug((string) $data['slug']);
        }

        $data['sort_order'] = max(0, (int) ($data['sort_order'] ?? 0));

        if (isset($data['social_links']) && is_array($data['social_links'])) {
            $data['social_links'] = (new Author(['social_links' => $data['social_links']]))->socialLinksMap();
        }

        return $data;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
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
