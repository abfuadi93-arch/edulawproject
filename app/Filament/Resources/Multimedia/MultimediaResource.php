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
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class MultimediaResource extends Resource
{
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
                Section::make('Informasi Multimedia')
                    ->description('Kelola video, podcast, poster, dokumentasi, reels, shorts, dan konten visual.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, $state) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Select::make('type')
                            ->label('Tipe Konten')
                            ->options([
                                'video' => 'Video',
                                'podcast' => 'Podcast',
                                'poster' => 'Poster',
                                'gallery' => 'Galeri',
                                'documentation' => 'Dokumentasi',
                                'reels' => 'Reels',
                                'shorts' => 'Shorts',
                                'webinar' => 'Webinar',
                            ])
                            ->default('video')
                            ->required(),

                        Select::make('platform')
                            ->label('Platform')
                            ->options([
                                'youtube' => 'YouTube',
                                'instagram' => 'Instagram',
                                'tiktok' => 'TikTok',
                                'spotify' => 'Spotify',
                                'website' => 'Website',
                                'other' => 'Lainnya',
                            ])
                            ->default('website')
                            ->required(),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->columnSpanFull(),

                        FileUpload::make('thumbnail')
                            ->label('Thumbnail')
                            ->image()
                            ->directory('multimedia')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Media')
                    ->schema([
                        TextInput::make('media_url')
                            ->label('Media URL')
                            ->url()
                            ->maxLength(255),

                        TextInput::make('embed_url')
                            ->label('Embed URL')
                            ->url()
                            ->maxLength(255),

                        TextInput::make('duration')
                            ->label('Durasi')
                            ->placeholder('Contoh: 12:35'),

                        DatePicker::make('published_at')
                            ->label('Tanggal Publikasi'),
                    ])
                    ->columns(1),

                Section::make('Status')
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

                        Toggle::make('featured')
                            ->label('Featured')
                            ->default(false),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Thumbnail')
                    ->square(),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(45)
                    ->description(fn (Multimedia $record): ?string => $record->description),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'video' => 'Video',
                        'podcast' => 'Podcast',
                        'poster' => 'Poster',
                        'gallery' => 'Galeri',
                        'documentation' => 'Dokumentasi',
                        'reels' => 'Reels',
                        'shorts' => 'Shorts',
                        'webinar' => 'Webinar',
                        default => $state ? ucfirst($state) : '-',
                    }),

                TextColumn::make('platform')
                    ->label('Platform')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'youtube' => 'YouTube',
                        'instagram' => 'Instagram',
                        'tiktok' => 'TikTok',
                        'spotify' => 'Spotify',
                        'website' => 'Website',
                        'other' => 'Lainnya',
                        default => $state ? ucfirst($state) : '-',
                    }),

                TextColumn::make('duration')
                    ->label('Durasi')
                    ->toggleable(),

                TextColumn::make('published_at')
                    ->label('Tanggal Publikasi')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                        default => $state ? ucfirst($state) : '-',
                    }),

                IconColumn::make('featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'video' => 'Video',
                        'podcast' => 'Podcast',
                        'poster' => 'Poster',
                        'gallery' => 'Galeri',
                        'documentation' => 'Dokumentasi',
                        'reels' => 'Reels',
                        'shorts' => 'Shorts',
                        'webinar' => 'Webinar',
                    ]),

                SelectFilter::make('platform')
                    ->label('Platform')
                    ->options([
                        'youtube' => 'YouTube',
                        'instagram' => 'Instagram',
                        'tiktok' => 'TikTok',
                        'spotify' => 'Spotify',
                        'website' => 'Website',
                        'other' => 'Lainnya',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),
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
