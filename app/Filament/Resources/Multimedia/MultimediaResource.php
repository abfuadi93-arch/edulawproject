<?php

namespace App\Filament\Resources\Multimedia;

use App\Filament\Concerns\HasSlugFormBehavior;
use App\Filament\Resources\Multimedia\Pages\CreateMultimedia;
use App\Filament\Resources\Multimedia\Pages\EditMultimedia;
use App\Filament\Resources\Multimedia\Pages\ListMultimedia;
use App\Models\Multimedia;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class MultimediaResource extends Resource
{
    use HasSlugFormBehavior;

    protected static ?string $model = Multimedia::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Konten Website';

    protected static ?string $navigationLabel = 'Multimedia';

    protected static ?string $modelLabel = 'Multimedia';

    protected static ?string $pluralModelLabel = 'Multimedia';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-play-circle';

    protected static ?int $navigationSort = 5;

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
                                Section::make('Panduan Pengisian')
                                    ->icon('heroicon-o-information-circle')
                                    ->description('Gunakan form ini untuk mengelola tiga kanal Multimedia Edulaw: YouTube Video, Shorts/Reels, dan Google Photos Album. Pilih jenis konten yang sesuai, isi URL utama, lalu unggah thumbnail agar tampilan halaman publik tetap rapi.')
                                    ->schema([]),

                                Section::make('1. Identitas Konten')
                                    ->icon('heroicon-o-play-circle')
                                    ->description('Kelola judul, slug, dan ringkasan konten yang tampil di halaman Multimedia.')
                                    ->schema([
                                        Grid::make([
                                            'default' => 1,
                                            'lg' => 2,
                                        ])
                                            ->schema([
                                                TextInput::make('title')
                                                    ->label('Judul Konten')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->live(onBlur: true)
                                                    ->placeholder('Diskusi Literasi Konstitusi #1')
                                                    ->afterStateUpdated(static::syncSlugFrom()),

                                                TextInput::make('slug')
                                                    ->label('Slug')
                                                    ->required()
                                                    ->unique(ignoreRecord: true)
                                                    ->maxLength(255)
                                                    ->helperText('Slug otomatis dari judul, tetapi tetap bisa diedit.'),
                                            ])
                                            ->columnSpanFull(),

                                        Textarea::make('description')
                                            ->label('Deskripsi Singkat')
                                            ->rows(5)
                                            ->maxLength(300)
                                            ->required()
                                            ->live()
                                            ->placeholder('Tulis ringkasan singkat konten multimedia...')
                                            ->helperText('Tulis ringkasan 1-3 kalimat. Untuk card public, deskripsi akan dipotong otomatis.')
                                            ->columnSpanFull(),
                                    ])
                                    ->extraAttributes(['class' => 'edulaw-admin-two-column-section']),

                                Section::make('2. Kanal Multimedia')
                                    ->icon('heroicon-o-rectangle-stack')
                                    ->description('Pilih salah satu kanal utama halaman Multimedia publik.')
                                    ->schema([
                                        Grid::make([
                                            'default' => 1,
                                            'lg' => 2,
                                        ])
                                            ->schema([
                                                Select::make('type')
                                                    ->label('Jenis Konten')
                                                    ->options(static::adminTypeOptions())
                                                    ->default('video')
                                                    ->live()
                                                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                                                        $type = Multimedia::normalizeType($state) ?: 'video';

                                                        $set('type', $type);

                                                        match ($type) {
                                                            'video' => $set('platform', 'youtube'),
                                                            'shorts' => $set('platform', 'youtube'),
                                                            'reels' => $set('platform', 'instagram'),
                                                            'gallery', 'documentation' => $set('platform', 'website'),
                                                            default => null,
                                                        };

                                                        if (static::isGalleryType($type)) {
                                                            $set('duration', null);
                                                            $set('embed_url', null);
                                                        }
                                                    })
                                                    ->searchable()
                                                    ->required(),

                                                Select::make('platform')
                                                    ->label('Platform')
                                                    ->options(static::adminPlatformOptions())
                                                    ->default('youtube')
                                                    ->searchable()
                                                    ->required()
                                                    ->helperText('Untuk album Google Photos, pilih platform Website / Google Photos atau Lainnya.'),
                                            ]),
                                    ]),

                                Section::make('3. Link dan Media')
                                    ->icon('heroicon-o-photo')
                                    ->description('Isi URL utama dan unggah thumbnail agar card publik tidak kosong.')
                                    ->schema([
                                        Grid::make([
                                            'default' => 1,
                                            'lg' => 2,
                                        ])
                                            ->schema([

                                                TextInput::make('media_url')
                                                    ->label('URL Konten / Album')
                                                    ->required()
                                                    ->url()
                                                    ->maxLength(255)
                                                    ->placeholder('https://www.youtube.com/watch?v=...')
                                                    ->helperText('Isi URL YouTube, YouTube Shorts, Instagram Reels, TikTok, atau Google Photos Album.'),

                                                TextInput::make('embed_url')
                                                    ->label('Embed URL')
                                                    ->url()
                                                    ->maxLength(255)
                                                    ->placeholder('https://www.youtube.com/embed/...')
                                                    ->helperText(fn (Get $get): string => static::isGalleryType($get('type'))
                                                        ? 'Opsional. Untuk album foto biasanya dikosongkan.'
                                                        : 'Opsional. Untuk YouTube, isi URL embed jika tersedia. Jika kosong, website akan memakai URL konten utama.')
                                                    ->visible(fn (Get $get): bool => ! static::isGalleryType($get('type'))),

                                                TextInput::make('duration')
                                                    ->label('Durasi')
                                                    ->placeholder('Contoh: 12:35 atau 1:02:15')
                                                    ->helperText('Opsional untuk video dan shorts. Kosongkan untuk album foto.')
                                                    ->visible(fn (Get $get): bool => static::isPlayableType($get('type'))),
                                            ]),

                                        FileUpload::make('thumbnail')
                                            ->label('Thumbnail / Cover')
                                            ->image()
                                            ->disk('public')
                                            ->directory('multimedia/thumbnails')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->imagePreviewHeight('180')
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->maxSize(2048)
                                            ->helperText('Gunakan rasio 16:9 untuk YouTube Video dan Google Photos Album. Gunakan rasio vertikal 4:5 atau 9:16 untuk Shorts/Reels. Maksimal 2 MB.')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columnSpan(['xl' => 8])
                            ->extraAttributes(['class' => 'edulaw-admin-main-column']),

                        Group::make()
                            ->schema([
                                Section::make('Publikasi')
                                    ->icon('heroicon-o-paper-airplane')
                                    ->description('Atur status, tanggal publikasi, dan penanda konten unggulan.')
                                    ->schema([
                                        Select::make('status')
                                            ->label('Status')
                                            ->options([
                                                'draft' => 'Draft',
                                                'published' => 'Published',
                                                'archived' => 'Archived',
                                            ])
                                            ->default('draft')
                                            ->required(),

                                        DateTimePicker::make('published_at')
                                            ->label('Tanggal Publikasi')
                                            ->seconds(false)
                                            ->default(now()),

                                        Toggle::make('featured')
                                            ->label('Jadikan Konten Pilihan')
                                            ->helperText('Konten featured akan diprioritaskan sebagai video pilihan di halaman Multimedia.')
                                            ->default(false),
                                    ])
                                    ->columns(1),
                            ])
                            ->columnSpan(['xl' => 4])
                            ->extraAttributes(['class' => 'edulaw-admin-side-column']),
                    ])
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'edulaw-admin-edit-shell']),
            ]);
    }

    private static function isPlayableType(?string $type): bool
    {
        return in_array(Multimedia::normalizeType($type) ?: 'video', Multimedia::PLAYABLE_TYPES, true);
    }

    private static function adminTypeOptions(): array
    {
        return [
            'video' => 'YouTube Video',
            'shorts' => 'YouTube Shorts',
            'reels' => 'Reels / Video Pendek',
            'gallery' => 'Google Photos Album',
            'documentation' => 'Dokumentasi Foto/Kegiatan',
        ];
    }

    private static function tableTypeOptions(): array
    {
        return static::adminTypeOptions() + [
            'podcast' => 'Podcast',
            'poster' => 'Poster',
            'webinar' => 'Webinar',
        ];
    }

    private static function adminPlatformOptions(): array
    {
        return [
            'youtube' => 'YouTube',
            'instagram' => 'Instagram',
            'tiktok' => 'TikTok',
            'website' => 'Website / Google Photos',
            'other' => 'Lainnya',
        ];
    }

    private static function tablePlatformOptions(): array
    {
        return static::adminPlatformOptions() + [
            'spotify' => 'Spotify',
            'gallery' => 'Galeri',
        ];
    }

    private static function isGalleryType(?string $type): bool
    {
        return in_array(Multimedia::normalizeType($type) ?: 'video', Multimedia::GALLERY_TYPES, true);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Thumbnail')
                    ->disk('public')
                    ->square()
                    ->size(44)
                    ->width(64)
                    ->grow(false)
                    ->defaultImageUrl(asset('images/logo/icon-bg.png')),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(['title', 'description', 'media_url'])
                    ->sortable()
                    ->limit(44)
                    ->wrap()
                    ->width('22rem')
                    ->description(fn (Multimedia $record): ?string => filled($record->description)
                        ? Str::limit($record->description, 82)
                        : null),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (?string $state): string => match (Multimedia::normalizeType($state)) {
                        'video' => 'primary',
                        'shorts' => 'danger',
                        'reels' => 'warning',
                        'gallery' => 'info',
                        'documentation' => 'gray',
                        'podcast' => 'success',
                        'poster' => 'warning',
                        'webinar' => 'primary',
                        default => 'gray',
                    })
                    ->wrap()
                    ->formatStateUsing(fn (?string $state): string => static::tableTypeOptions()[Multimedia::normalizeType($state)] ?? ($state ? Str::headline($state) : '-')),

                TextColumn::make('platform')
                    ->label('Platform')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'youtube' => 'danger',
                        'instagram' => 'warning',
                        'tiktok' => 'gray',
                        'spotify' => 'success',
                        'website' => 'primary',
                        'gallery' => 'info',
                        'other' => 'gray',
                        default => 'gray',
                    })
                    ->grow(false)
                    ->formatStateUsing(fn (?string $state): string => Multimedia::PLATFORM_OPTIONS[$state] ?? ($state ? Str::headline($state) : '-')),

                TextColumn::make('published_at')
                    ->label('Tanggal Publikasi')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'published' => 'success',
                        'archived' => 'gray',
                        default => 'warning',
                    })
                    ->grow(false)
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'published', 'terbit' => 'Published',
                        'archived' => 'Archived',
                        default => $state ? Str::headline($state) : '-',
                    }),

                IconColumn::make('featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('media_url')
                    ->label('URL')
                    ->searchable()
                    ->limit(36)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort(fn (Builder $query): Builder => $query
                ->orderByDesc('published_at')
                ->orderByDesc('created_at'))
            ->searchPlaceholder('Cari multimedia...')
            ->paginationPageOptions([10, 25, 50])
            ->filters([
                SelectFilter::make('type')
                    ->label('Jenis Konten')
                    ->options(static::tableTypeOptions()),

                SelectFilter::make('platform')
                    ->label('Platform')
                    ->options(static::tablePlatformOptions()),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),

                SelectFilter::make('featured')
                    ->label('Featured')
                    ->options([
                        '1' => 'Featured',
                        '0' => 'Bukan Featured',
                    ]),
            ])
            ->recordActions([
                Actions\EditAction::make(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\BulkAction::make('publish')
                        ->label('Publish')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $records->each(function (Multimedia $record): void {
                                $record->update([
                                    'status' => 'published',
                                    'published_at' => $record->published_at ?? now(),
                                ]);
                            });
                        }),

                    Actions\BulkAction::make('archive')
                        ->label('Archive')
                        ->icon('heroicon-o-archive-box')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $records->each(function (Multimedia $record): void {
                                $record->update([
                                    'status' => 'archived',
                                ]);
                            });
                        }),

                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => ListMultimedia::route('/'),
            'create' => CreateMultimedia::route('/create'),
            'edit' => EditMultimedia::route('/{record}/edit'),
        ];
    }
}
