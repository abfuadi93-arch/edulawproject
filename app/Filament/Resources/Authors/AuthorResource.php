<?php

namespace App\Filament\Resources\Authors;

use App\Filament\Concerns\HasSlugFormBehavior;
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
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AuthorResource extends Resource
{
    use HasSlugFormBehavior;

    protected static ?string $model = Author::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Referensi Konten';

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
                        Section::make('Informasi Profil')
                            ->description('Kelola identitas publik, penulis, narasumber, moderator, founder, co-founder, dan tim Edulaw.')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(static::syncSlugFrom())
                                    ->columnSpanFull(),

                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->helperText('Digunakan sebagai identitas unik profil di website.')
                                    ->columnSpanFull(),

                                Grid::make([
                                    'default' => 1,
                                    'lg' => 2,
                                ])
                                    ->schema([
                                        Group::make()
                                            ->schema([
                                                TextInput::make('position')
                                                    ->label('Jabatan')
                                                    ->maxLength(255)
                                                    ->placeholder('Founder / Narasumber / Tim Edulaw'),

                                                TextInput::make('institution')
                                                    ->label('Afiliasi')
                                                    ->maxLength(255)
                                                    ->placeholder('Edulaw Project / Institusi asal'),

                                                Select::make('profile_type')
                                                    ->label('Jenis Profil')
                                                    ->options(Author::PROFILE_TYPES)
                                                    ->searchable()
                                                    ->placeholder('Pilih jenis profil'),
                                            ]),

                                        Group::make()
                                            ->schema([
                                                Textarea::make('bio')
                                                    ->label('Bio Singkat')
                                                    ->rows(5)
                                                    ->maxLength(500)
                                                    ->live()
                                                    ->helperText(fn (?string $state): string => sprintf('%d/500 karakter', mb_strlen((string) $state)))
                                                    ->placeholder('Tulis bio singkat yang dapat ditampilkan pada website.'),

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
                                                    ]),

                                                Toggle::make('is_active')
                                                    ->label('Status Aktif')
                                                    ->helperText('Profil ini ditampilkan di website.')
                                                    ->default(true),
                                            ]),
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan([
                                'default' => 1,
                                'lg' => 3,
                            ])
                            ->extraAttributes(['class' => 'edulaw-admin-profile-info']),

                        Group::make()
                            ->schema([
                                Section::make('Foto Profil')
                                    ->description('Unggah foto profil untuk ditampilkan di website.')
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
                                            ->helperText('Tarik & letakkan foto atau pilih berkas. Rekomendasi ukuran 400 x 400 px. Format: JPG, PNG, WebP. Maks. 2 MB.')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Akun Admin dan Kontak (Opsional)')
                                    ->description('Profil boleh tidak punya akun admin. Akun admin wajib memiliki profil yang terhubung.')
                                    ->schema([
                                        Select::make('user_id')
                                            ->label('Akun Admin Terkait')
                                            ->relationship('user', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Profil publik tanpa akun admin')
                                            ->helperText('Opsional. Tidak semua profil adalah akun admin.'),

                                        TextInput::make('email')
                                            ->label('Email Kontak')
                                            ->email()
                                            ->maxLength(255)
                                            ->helperText('Email ini akan digunakan untuk komunikasi resmi.'),
                                    ])
                                    ->columns(1),

                                Section::make('Tautan Sosial')
                                    ->description('Kelola tautan sosial profil secara ringkas.')
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
                                    ]),
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
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
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
