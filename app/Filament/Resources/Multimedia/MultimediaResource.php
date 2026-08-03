<?php

namespace App\Filament\Resources\Multimedia;

use App\Filament\Resources\Multimedia\Pages\CreateMultimedia;
use App\Filament\Resources\Multimedia\Pages\EditMultimedia;
use App\Filament\Resources\Multimedia\Pages\ListMultimedia;
use App\Models\Multimedia;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
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
                                Section::make('1. Informasi Konten')
                                    ->icon('heroicon-o-play-circle')
                                    ->description('Masukkan informasi dasar dan tautan konten eksternal.')
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Judul Konten')
                                            ->required()
                                            ->maxLength(200)
                                            ->helperText('Gunakan judul singkat yang mudah dikenali pada halaman Multimedia.')
                                            ->columnSpanFull(),

                                        Textarea::make('description')
                                            ->label('Ringkasan')
                                            ->rows(3)
                                            ->maxLength(300)
                                            ->live(onBlur: true)
                                            ->helperText('Tampil sebagai deskripsi singkat pada kartu konten utama.')
                                            ->columnSpanFull(),

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
                                                        match ($state) {
                                                            'video' => $set('platform', 'youtube'),
                                                            'reels' => $set('platform', 'instagram'),
                                                            'gallery' => $set('platform', 'google_photos'),
                                                            default => null,
                                                        };

                                                        if ($state !== 'video') {
                                                            $set('featured', false);
                                                        }
                                                    })
                                                    ->required(),

                                                Select::make('platform')
                                                    ->label('Platform')
                                                    ->options(fn (Get $get): array => static::platformOptionsForType($get('type')))
                                                    ->default('youtube')
                                                    ->live()
                                                    ->required()
                                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $state !== 'youtube' ? $set('featured', false) : null),
                                            ])
                                            ->columnSpanFull(),

                                        TextInput::make('media_url')
                                            ->label('URL Eksternal')
                                            ->required()
                                            ->url()
                                            ->maxLength(255)
                                            ->rules([fn (Get $get) => function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                                $allowedHosts = match ($get('platform')) {
                                                    'youtube' => ['youtube.com', 'youtu.be'],
                                                    'instagram' => ['instagram.com'],
                                                    'google_photos' => ['photos.app.goo.gl', 'photos.google.com'],
                                                    default => [],
                                                };
                                                $host = strtolower((string) parse_url((string) $value, PHP_URL_HOST));

                                                if ($allowedHosts !== [] && ! collect($allowedHosts)->contains(fn (string $allowed): bool => $host === $allowed || Str::endsWith($host, '.'.$allowed))) {
                                                    $fail('URL tidak sesuai dengan platform yang dipilih.');
                                                }
                                            }])
                                            ->helperText(fn (Get $get): string => match ($get('platform')) {
                                                'instagram' => 'Masukkan URL Reel atau posting Instagram.',
                                                'google_photos' => 'Masukkan URL album Google Photos yang dapat diakses publik.',
                                                default => 'Masukkan URL video atau YouTube Shorts.',
                                            })
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columnSpan(['xl' => 8])
                            ->extraAttributes(['class' => 'edulaw-admin-main-column']),

                        Group::make()
                            ->schema([
                                Section::make('2. Tampilan Publik')
                                    ->icon('heroicon-o-paper-airplane')
                                    ->description('Atur status, urutan, dan prioritas konten pada halaman Multimedia.')
                                    ->schema([
                                        Select::make('status')
                                            ->label('Status')
                                            ->options([
                                                'draft' => 'Draft',
                                                'published' => 'Dipublikasikan',
                                                'archived' => 'Diarsipkan',
                                            ])
                                            ->default('draft')
                                            ->required(),

                                        DatePicker::make('published_at')
                                            ->label('Tanggal Publikasi')
                                            ->native(false)
                                            ->displayFormat('d M Y'),

                                        Toggle::make('featured')
                                            ->label('Jadikan Konten Utama')
                                            ->helperText('Konten utama ditampilkan dalam kartu besar pada bagian YouTube Video.')
                                            ->default(false)
                                            ->visible(fn (Get $get): bool => $get('type') === 'video')
                                            ->dehydrated(true),
                                    ])
                                    ->columns(1),

                                Section::make('3. Thumbnail')
                                    ->icon('heroicon-o-photo')
                                    ->description('Unggah gambar yang digunakan pada kartu Multimedia.')
                                    ->schema([
                                        FileUpload::make('thumbnail')
                                            ->label('Thumbnail')
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
                                            ->required(fn (Get $get): bool => $get('platform') !== 'youtube')
                                            ->helperText(fn (Get $get): string => match ($get('type')) {
                                                'reels' => 'Gunakan thumbnail vertikal rasio 4:5 atau 9:16.',
                                                'gallery' => 'Gunakan cover album landscape rasio 16:9 atau 4:3.',
                                                default => 'Gunakan thumbnail landscape rasio 16:9. Jika kosong, thumbnail YouTube digunakan otomatis.',
                                            })
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

    private static function adminTypeOptions(): array
    {
        return [
            'video' => 'YouTube Video',
            'reels' => 'Shorts / Reels',
            'gallery' => 'Photo Album',
        ];
    }

    private static function tableTypeOptions(): array
    {
        return static::adminTypeOptions() + [
            'shorts' => 'Shorts / Reels',
            'documentation' => 'Photo Album',
            'podcast' => 'Lainnya',
            'poster' => 'Lainnya',
            'webinar' => 'Lainnya',
        ];
    }

    private static function platformOptionsForType(?string $type): array
    {
        return match ($type) {
            'reels', 'shorts' => ['instagram' => 'Instagram', 'youtube' => 'YouTube'],
            'gallery', 'documentation' => ['google_photos' => 'Google Photos'],
            default => ['youtube' => 'YouTube'],
        };
    }

    private static function tablePlatformOptions(): array
    {
        return [
            'youtube' => 'YouTube',
            'instagram' => 'Instagram',
            'google_photos' => 'Google Photos',
            'website' => 'Google Photos',
            'other' => 'Google Photos',
            'tiktok' => 'Lainnya',
            'spotify' => 'Lainnya',
            'gallery' => 'Lainnya',
        ];
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
                            parse_url((string) $record->media_url, PHP_URL_HOST),
                            Str::limit((string) $record->description, 55),
                        ],
                    ])
                    ->searchable(['title', 'description', 'media_url'])
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('title', $direction))
                    ->url(fn (Multimedia $record): ?string => static::canEdit($record) ? static::getUrl('edit', ['record' => $record]) : null)
                    ->extraHeaderAttributes(['class' => 'edulaw-resource-primary-header'])
                    ->extraCellAttributes(['class' => 'edulaw-resource-primary-cell']),

                TextColumn::make('type')
                    ->label('Jenis')
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
                    ->formatStateUsing(fn (?string $state): string => static::tablePlatformOptions()[$state] ?? 'Lainnya')
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
                        'published', 'terbit' => 'Dipublikasikan',
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
                    ->label('Konten Utama')
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
            ->searchPlaceholder('Cari judul atau platform...')
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
                    ->label('Konten Utama')
                    ->placeholder('Semua')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak'),

                Filter::make('published_at')
                    ->label('Rentang Tanggal Terbit')
                    ->schema([
                        DatePicker::make('from')->label('Dari tanggal')->native(false),
                        DatePicker::make('until')->label('Sampai tanggal')->native(false),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->where('published_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->where('published_at', '<=', $date)))
                    ->indicateUsing(fn (array $data): array => static::dateRangeIndicators($data, 'Terbit')),
            ])
            ->recordActions([
                Actions\ActionGroup::make([
                    Actions\Action::make('viewOnPlatform')
                        ->label('Lihat di Platform')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(fn (Multimedia $record): ?string => $record->media_url)
                        ->openUrlInNewTab()
                        ->visible(fn (Multimedia $record): bool => filled($record->media_url)),
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
