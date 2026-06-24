<?php

namespace App\Filament\Resources\Multimedia;

use App\Filament\Concerns\HasSlugFormBehavior;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
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
                Section::make('Informasi Utama')
                    ->description('Kelola informasi ringkas konten yang tampil di halaman Multimedia.')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'lg' => 2,
                        ])
                            ->schema([
                                Group::make()
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Judul')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(static::syncSlugFrom()),

                                        TextInput::make('slug')
                                            ->label('Slug')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->helperText('Slug digunakan sebagai alamat multimedia di website.'),

                                        Select::make('type')
                                            ->label('Tipe Konten')
                                            ->options(Multimedia::TYPE_OPTIONS)
                                            ->default('video')
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                                $type = Multimedia::normalizeType($state) ?: 'video';

                                                $set('type', $type);

                                                if (static::isPlayableType($type)) {
                                                    $set('photo_count', null);

                                                    return;
                                                }

                                                $set('duration', null);
                                                $set('platform', 'website');

                                                if (static::isPosterType($type)) {
                                                    $set('photo_count', null);
                                                }
                                            })
                                            ->searchable()
                                            ->required(),
                                    ]),

                                Group::make()
                                    ->schema([
                                        Textarea::make('description')
                                            ->label('Ringkasan')
                                            ->rows(7)
                                            ->maxLength(300)
                                            ->required()
                                            ->live()
                                            ->placeholder('Tulis deskripsi singkat konten multimedia...')
                                            ->helperText('Maksimal 300 karakter termasuk spasi.'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'edulaw-admin-two-column-section']),

                Grid::make([
                    'default' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        Section::make('Klasifikasi Halaman Publik')
                            ->description('Tentukan serial, topik, dan posisi konten di halaman Multimedia.')
                            ->schema([
                                Select::make('serial')
                                    ->label('Serial Multimedia')
                                    ->options(Multimedia::SERIAL_OPTIONS)
                                    ->searchable()
                                    ->placeholder('Pilih serial multimedia'),

                                Select::make('topic')
                                    ->label('Topik Multimedia')
                                    ->options(Multimedia::TOPIC_OPTIONS)
                                    ->searchable()
                                    ->placeholder('Pilih topik multimedia'),

                                Select::make('display_section')
                                    ->label('Section Tampilan')
                                    ->options(Multimedia::DISPLAY_SECTION_OPTIONS)
                                    ->default('latest')
                                    ->required()
                                    ->helperText('Pilih area utama tempat konten ini ditonjolkan di halaman publik.'),
                            ]),

                        Section::make('Publikasi')
                            ->description('Atur status, tanggal publikasi, dan penanda konten unggulan.')
                            ->schema([
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'published' => 'Terbit',
                                        'archived' => 'Arsip',
                                    ])
                                    ->default('draft')
                                    ->required(),

                                DatePicker::make('published_at')
                                    ->label('Tanggal Publikasi'),

                                Toggle::make('featured')
                                    ->label('Tampilkan sebagai unggulan')
                                    ->default(false),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'edulaw-admin-section-pair']),

                Section::make('Media dan Tautan')
                    ->description('Field media akan menyesuaikan tipe konten yang dipilih.')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'lg' => 2,
                        ])
                            ->schema([
                                Select::make('platform')
                                    ->label('Platform')
                                    ->options(Multimedia::PLATFORM_OPTIONS)
                                    ->default('website')
                                    ->searchable()
                                    ->required(fn (Get $get): bool => static::isPlayableType($get('type')))
                                    ->visible(fn (Get $get): bool => static::isPlayableType($get('type'))),

                                TextInput::make('media_url')
                                    ->label(fn (Get $get): string => static::isPlayableType($get('type')) ? 'Link Media' : 'Link Opsional')
                                    ->url()
                                    ->maxLength(255)
                                    ->placeholder('https://...')
                                    ->helperText(fn (Get $get): string => static::isPlayableType($get('type'))
                                        ? 'Tautan video, podcast, shorts, atau webinar.'
                                        : 'Opsional. Gunakan jika konten punya tautan eksternal.'),

                                TextInput::make('duration')
                                    ->label('Durasi')
                                    ->placeholder('Contoh: 12:35')
                                    ->visible(fn (Get $get): bool => static::isPlayableType($get('type'))),

                                TextInput::make('photo_count')
                                    ->label('Jumlah Foto')
                                    ->numeric()
                                    ->minValue(0)
                                    ->placeholder('Contoh: 24')
                                    ->visible(fn (Get $get): bool => static::isGalleryType($get('type'))),
                            ]),

                        FileUpload::make('thumbnail')
                            ->label(fn (Get $get): string => static::isPosterType($get('type')) ? 'Upload Poster / Thumbnail' : 'Thumbnail')
                            ->image()
                            ->disk('public')
                            ->directory('multimedia')
                            ->visibility('public')
                            ->imageEditor()
                            ->imagePreviewHeight('180')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120)
                            ->helperText('Rekomendasi format JPG, PNG, atau WebP. Maksimal 5 MB.')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
            ]);
    }

    private static function isPlayableType(?string $type): bool
    {
        return in_array(Multimedia::normalizeType($type) ?: 'video', Multimedia::PLAYABLE_TYPES, true);
    }

    private static function isGalleryType(?string $type): bool
    {
        return in_array(Multimedia::normalizeType($type) ?: 'video', Multimedia::GALLERY_TYPES, true);
    }

    private static function isPosterType(?string $type): bool
    {
        return (Multimedia::normalizeType($type) ?: 'video') === 'poster';
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
                    ->searchable()
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
                        'podcast' => 'success',
                        'poster' => 'warning',
                        'gallery' => 'info',
                        'documentation' => 'gray',
                        'shorts' => 'danger',
                        'webinar' => 'primary',
                        default => 'gray',
                    })
                    ->wrap()
                    ->formatStateUsing(fn (?string $state): string => Multimedia::TYPE_OPTIONS[Multimedia::normalizeType($state)] ?? ($state ? Str::headline($state) : '-')),

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

                TextColumn::make('serial')
                    ->label('Serial')
                    ->formatStateUsing(fn (?string $state): string => Multimedia::SERIAL_OPTIONS[$state] ?? ($state ? Str::headline($state) : '-'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('topic')
                    ->label('Topik')
                    ->formatStateUsing(fn (?string $state): string => Multimedia::TOPIC_OPTIONS[$state] ?? ($state ? Str::headline($state) : '-'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('display_section')
                    ->label('Section')
                    ->formatStateUsing(fn (?string $state): string => Multimedia::DISPLAY_SECTION_OPTIONS[$state] ?? ($state ? Str::headline($state) : '-'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('duration')
                    ->label('Durasi')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('photo_count')
                    ->label('Jumlah Foto')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('published_at')
                    ->label('Tanggal Publikasi')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                        'published', 'terbit' => 'Terbit',
                        'archived' => 'Arsip',
                        default => $state ? Str::headline($state) : '-',
                    }),

                IconColumn::make('featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('published_at', 'desc')
            ->searchPlaceholder('Cari multimedia...')
            ->paginationPageOptions([10, 25, 50])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options(Multimedia::TYPE_OPTIONS),

                SelectFilter::make('platform')
                    ->label('Platform')
                    ->options(Multimedia::PLATFORM_OPTIONS),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Terbit',
                        'archived' => 'Arsip',
                    ]),

                SelectFilter::make('display_section')
                    ->label('Section Tampilan')
                    ->options(Multimedia::DISPLAY_SECTION_OPTIONS),
            ])
            ->recordActions([
                Actions\EditAction::make(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
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
