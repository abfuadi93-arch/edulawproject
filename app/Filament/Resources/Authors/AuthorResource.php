<?php

namespace App\Filament\Resources\Authors;

use App\Filament\Resources\Authors\Pages\CreateAuthor;
use App\Filament\Resources\Authors\Pages\EditAuthor;
use App\Filament\Resources\Authors\Pages\ListAuthors;
use App\Models\Author;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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
                                            ->maxSize(2048)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->imagePreviewHeight('180')
                                            ->helperText('JPG, PNG, WebP. Maks. 2 MB.')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Akun Admin dan Kontak (Opsional)')
                                    ->icon('heroicon-o-identification')
                                    ->description('Opsional untuk profil eksternal.')
                                    ->schema([
                                        Select::make('user_id')
                                            ->label('Akun Admin Terkait')
                                            ->relationship('user', 'name')
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
                                        Repeater::make('social_links')
                                            ->label('Tautan Sosial')
                                            ->schema([
                                                TextInput::make('platform')
                                                    ->label('Platform')
                                                    ->prefixIcon('heroicon-o-globe-alt')
                                                    ->placeholder('LinkedIn / Instagram / Website'),

                                                TextInput::make('url')
                                                    ->label('URL')
                                                    ->url()
                                                    ->placeholder('https://...'),
                                            ])
                                            ->table([
                                                TableColumn::make('Platform'),
                                                TableColumn::make('URL'),
                                            ])
                                            ->itemLabel(fn (array $state): ?string => collect([
                                                $state['platform'] ?? null,
                                                $state['url'] ?? null,
                                            ])->filter()->join(' - ') ?: 'Tautan sosial')
                                            ->addActionLabel('Tambah Tautan Sosial')
                                            ->reorderable()
                                            ->collapsed()
                                            ->columnSpanFull(),
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
                ImageColumn::make('photo')
                    ->label('Photo Profil')
                    ->disk('public')
                    ->circular()
                    ->size(44)
                    ->width(64)
                    ->grow(false)
                    ->defaultImageUrl(asset('images/logo/icon-bg.png')),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->limit(42)
                    ->wrap()
                    ->description(fn (Author $record): ?string => $record->profile_type_label),

                TextColumn::make('affiliation_label')
                    ->label('Afiliasi / Jabatan')
                    ->state(fn (Author $record): string => $record->affiliation_label)
                    ->searchable(['position', 'institution'])
                    ->limit(48)
                    ->wrap(),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('interests')
                    ->label('Minat')
                    ->searchable()
                    ->limit(54)
                    ->wrap()
                    ->placeholder('-'),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('show_in_organization')
                    ->label('Struktur')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function prepareFormDataForPersistence(array $data, ?int $ignoreId = null): array
    {
        if (blank($data['slug'] ?? null) && filled($data['name'] ?? null)) {
            $data['slug'] = Author::uniqueSlugFor((string) $data['name'], $ignoreId);
        }

        if (filled($data['slug'] ?? null)) {
            $data['slug'] = Str::slug((string) $data['slug']);
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
