<?php

namespace App\Filament\Resources\Multimedia;

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

class MultimediaResource extends Resource
{
    protected static ?string $model = Multimedia::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

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
                                                    ->afterStateUpdated(function ($get, $set, ?string $old, ?string $state): void {
                                                        $currentSlug = (string) ($get('slug') ?? '');
                                                        $oldSlug = Str::slug((string) $old);

                                                        if (filled($currentSlug) && $currentSlug !== $oldSlug) {
                                                            return;
                                                        }

                                                        $set('slug', Str::slug((string) $state));
                                                    }),

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

                                Section::make('2. Kanal dan Link')
                                    ->icon('heroicon-o-link')
                                    ->description('Pilih kanal konten, lalu isi URL utama dan metadata teknisnya.')
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
                                                            $set('display_section', 'topic_multimedia');

                                                            return;
                                                        }

                                                        $set('photo_count', null);
                                                        $set('display_section', in_array($type, ['shorts', 'reels'], true)
                                                            ? 'short_video'
                                                            : 'latest');
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

                                                TextInput::make('media_url')
                                                    ->label('URL Konten / Album')
                                                    ->required()
                                                    ->url()
                                                    ->maxLength(255)
                                                    ->placeholder('https://www.youtube.com/watch?v=...')
                                                    ->helperText('Isi URL YouTube, YouTube Shorts, Instagram Reels, TikTok, atau Google Photos Album.'),

                                                TextInput::make('duration')
                                                    ->label('Durasi')
                                                    ->placeholder('Contoh: 12:35 atau 1:02:15')
                                                    ->helperText('Opsional untuk video dan shorts. Kosongkan untuk album foto.')
                                                    ->visible(fn (Get $get): bool => static::isPlayableType($get('type')))
                                                    ->dehydrated(fn (Get $get): bool => static::isPlayableType($get('type'))),

                                                TextInput::make('embed_url')
                                                    ->label('Embed URL')
                                                    ->url()
                                                    ->maxLength(255)
                                                    ->placeholder('https://www.youtube.com/embed/...')
                                                    ->helperText(fn (Get $get): string => static::isGalleryType($get('type'))
                                                        ? 'Opsional. Untuk album foto biasanya dikosongkan.'
                                                        : 'Opsional. Untuk YouTube, isi URL embed jika tersedia. Jika kosong, website akan memakai URL konten utama.')
                                                    ->visible(fn (Get $get): bool => ! static::isGalleryType($get('type')))
                                                    ->dehydrated(fn (Get $get): bool => ! static::isGalleryType($get('type'))),

                                                TextInput::make('photo_count')
                                                    ->label('Jumlah Foto')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->suffix('foto')
                                                    ->placeholder('24')
                                                    ->helperText('Isi untuk Google Photos Album atau dokumentasi foto.')
                                                    ->visible(fn (Get $get): bool => static::isGalleryType($get('type')))
                                                    ->dehydrated(fn (Get $get): bool => static::isGalleryType($get('type'))),
                                            ])
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('3. Pengelompokan Publik')
                                    ->icon('heroicon-o-rectangle-stack')
                                    ->description('Atur seri, topik, dan area tampilan konten di halaman Multimedia.')
                                    ->schema([
                                        Grid::make([
                                            'default' => 1,
                                            'lg' => 3,
                                        ])
                                            ->schema([
                                                Select::make('display_section')
                                                    ->label('Area Tampil')
                                                    ->options(Multimedia::DISPLAY_SECTION_OPTIONS)
                                                    ->default('latest')
                                                    ->searchable()
                                                    ->required(),

                                                Select::make('serial')
                                                    ->label('Serial')
                                                    ->options(Multimedia::SERIAL_OPTIONS)
                                                    ->searchable()
                                                    ->placeholder('Pilih serial'),

                                                Select::make('topic')
                                                    ->label('Topik')
                                                    ->options(Multimedia::TOPIC_OPTIONS)
                                                    ->searchable()
                                                    ->placeholder('Pilih topik'),
                                            ])
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

                                Section::make('Media')
                                    ->icon('heroicon-o-photo')
                                    ->description('Unggah thumbnail atau cover agar kartu publik tidak kosong.')
                                    ->schema([
                                        FileUpload::make('thumbnail')
                                            ->label('Thumbnail / Cover')
                                            ->image()
                                            ->disk('public')
                                            ->directory('multimedia/thumbnails')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->imagePreviewHeight('180')
                                            ->downloadable()
                                            ->openable()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->maxSize(2048)
                                            ->helperText('Gunakan rasio 16:9 untuk video dan album. Gunakan rasio vertikal 4:5 atau 9:16 untuk Shorts/Reels. Maksimal 2 MB.')
                                            ->columnSpanFull(),
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
                ViewColumn::make('content')
                    ->label('Konten')
                    ->view('filament.tables.columns.resource-content', fn (Multimedia $record): array => [
                        'imageUrl' => $record->thumbnail_url,
                        'title' => $record->title,
                        'metadata' => [
                            $record->duration,
                            $record->display_platform,
                            $record->published_at?->locale('id')->translatedFormat('d M Y'),
                        ],
                    ])
                    ->searchable(['title', 'description', 'platform', 'type'])
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('title', $direction))
                    ->url(fn (Multimedia $record): ?string => static::canEdit($record) ? static::getUrl('edit', ['record' => $record]) : null)
                    ->extraHeaderAttributes(['class' => 'edulaw-resource-primary-header'])
                    ->extraCellAttributes(['class' => 'edulaw-resource-primary-cell']),

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
                    ->limit(20)
                    ->formatStateUsing(fn (?string $state): string => static::tableTypeOptions()[Multimedia::normalizeType($state)] ?? ($state ? Str::headline($state) : '—'))
                    ->visibleFrom('lg')
                    ->extraHeaderAttributes(['class' => 'edulaw-resource-classification-header'])
                    ->extraCellAttributes(['class' => 'edulaw-resource-classification-cell']),

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
                    ->formatStateUsing(fn (?string $state): string => Multimedia::PLATFORM_OPTIONS[$state] ?? ($state ? Str::headline($state) : '—'))
                    ->visibleFrom('lg')
                    ->extraHeaderAttributes(['class' => 'edulaw-resource-platform-header'])
                    ->extraCellAttributes(['class' => 'edulaw-resource-platform-cell']),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'published' => 'success',
                        'archived' => 'gray',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'published', 'terbit' => 'Published',
                        'archived' => 'Diarsipkan',
                        default => $state ? Str::headline($state) : '—',
                    })
                    ->extraHeaderAttributes(['class' => 'edulaw-resource-status-header'])
                    ->extraCellAttributes(['class' => 'edulaw-resource-status-cell']),

                TextColumn::make('published_at')
                    ->label('Terbit')
                    ->formatStateUsing(fn ($state): string => $state?->locale('id')->translatedFormat('d M Y') ?? '—')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable()
                    ->visibleFrom('xl')
                    ->extraHeaderAttributes(['class' => 'edulaw-resource-time-header'])
                    ->extraCellAttributes(['class' => 'edulaw-resource-time-cell']),

                IconColumn::make('featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('media_url')
                    ->label('URL')
                    ->searchable()
                    ->limit(36)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->formatStateUsing(fn ($state): string => $state->locale('id')->diffForHumans())
                    ->tooltip(fn (Multimedia $record): string => $record->updated_at->locale('id')->translatedFormat('d M Y, H:i'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort(fn (Builder $query): Builder => $query
                ->orderByDesc('published_at')
                ->orderByDesc('created_at'))
            ->searchPlaceholder('Cari judul, jenis, atau platform...')
            ->searchDebounce('500ms')
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
                        'archived' => 'Diarsipkan',
                    ]),

                TernaryFilter::make('featured')
                    ->label('Featured')
                    ->placeholder('Semua')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak'),

                Filter::make('published_at')
                    ->label('Rentang Tanggal Terbit')
                    ->schema([
                        DateTimePicker::make('from')->label('Dari tanggal')->native(false)->seconds(false),
                        DateTimePicker::make('until')->label('Sampai tanggal')->native(false)->seconds(false),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->where('published_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->where('published_at', '<=', $date)))
                    ->indicateUsing(fn (array $data): array => static::dateRangeIndicators($data, 'Terbit')),
            ])
            ->recordActions([
                Actions\ActionGroup::make([
                    Actions\EditAction::make()->label('Edit'),
                    Actions\ReplicateAction::make()
                        ->label('Duplikasi')
                        ->visible(fn (): bool => static::canCreate())
                        ->mutateRecordDataUsing(fn (array $data, Multimedia $record): array => [
                            ...$data,
                            'title' => Str::limit($record->title.' (Salinan)', 255, ''),
                            'slug' => static::uniqueDuplicateSlug($record),
                            'status' => 'draft',
                            'published_at' => null,
                            'featured' => false,
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                        ]),
                    Actions\Action::make('archive')
                        ->label('Arsipkan')
                        ->icon('heroicon-o-archive-box')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn (Multimedia $record): bool => $record->status !== 'archived'
                            && (Auth::user()?->can('archive multimedia') ?? false))
                        ->action(fn (Multimedia $record) => $record->update(['status' => 'archived', 'updated_by' => Auth::id()])),
                    Actions\DeleteAction::make()->label('Hapus')->requiresConfirmation(),
                ])->label('Aksi lainnya')->icon('heroicon-o-ellipsis-vertical')->tooltip('Aksi lainnya')->color('gray'),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\BulkAction::make('publish')
                        ->label('Publikasikan')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->authorizeIndividualRecords('update')
                        ->visible(fn (): bool => Auth::user()?->can('publish multimedia') ?? false)
                        ->action(function ($records): void {
                            $records->each(function (Multimedia $record): void {
                                $record->update([
                                    'status' => 'published',
                                    'published_at' => $record->published_at ?? now(),
                                ]);
                            });
                        }),

                    Actions\BulkAction::make('archive')
                        ->label('Arsipkan')
                        ->icon('heroicon-o-archive-box')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->authorizeIndividualRecords('update')
                        ->visible(fn (): bool => Auth::user()?->can('archive multimedia') ?? false)
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

    private static function dateRangeIndicators(array $data, string $label): array
    {
        return collect([
            ($data['from'] ?? null) ? Indicator::make($label.' mulai '.Carbon::parse($data['from'])->locale('id')->translatedFormat('d M Y'))->removeField('from') : null,
            ($data['until'] ?? null) ? Indicator::make($label.' sampai '.Carbon::parse($data['until'])->locale('id')->translatedFormat('d M Y'))->removeField('until') : null,
        ])->filter()->all();
    }

    public static function uniqueDuplicateSlug(Multimedia $record): string
    {
        $base = Str::limit(Str::slug($record->slug ?: $record->title).'-salinan', 240, '');
        $slug = $base;
        $suffix = 2;

        while (Multimedia::query()->where('slug', $slug)->exists()) {
            $slug = Str::limit($base, 240 - strlen((string) $suffix), '').'-'.$suffix++;
        }

        return $slug;
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
