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
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class InsightResource extends Resource
{
    protected static ?string $model = Insight::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Konten Website';

    protected static ?string $navigationLabel = 'Edulaw Insight';

    protected static ?string $modelLabel = 'Insight';

    protected static ?string $pluralModelLabel = 'Insight';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama')
                    ->description('Isi identitas utama artikel Insight.')
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

                        Select::make('insight_category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('authors')
                            ->label('Penulis')
                            ->relationship('authors', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('tags')
                            ->label('Tag')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),

                        Textarea::make('excerpt')
                            ->label('Ringkasan')
                            ->rows(4)
                            ->maxLength(500),
                    ])
                    ->columns(2)
                    ->extraAttributes(['class' => 'edulaw-admin-two-column-section']),

                Section::make('Konten Artikel')
                    ->description('Tulis isi artikel utama.')
                    ->schema([
                        RichEditor::make('content')
                            ->label('Isi Artikel')
                            ->required()
                            ->columnSpanFull(),

                        FileUpload::make('cover_image')
                            ->label('Gambar Sampul')
                            ->image()
                            ->disk('public')
                            ->directory('insights')
                            ->visibility('public')
                            ->imageEditor()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(4096)
                            ->columnSpanFull(),
                    ]),

                Grid::make(2)
                    ->schema([
                        Section::make('Publikasi dan Editorial')
                            ->description('Atur status, waktu tayang, dan penanda artikel pilihan.')
                            ->schema([
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'submitted' => 'Submitted',
                                        'reviewed' => 'Reviewed',
                                        'published' => 'Published',
                                        'archived' => 'Archived',
                                    ])
                                    ->default('draft')
                                    ->required(),

                                DateTimePicker::make('published_at')
                                    ->label('Tanggal Tayang')
                                    ->seconds(false),

                                TextInput::make('reading_time')
                                    ->label('Estimasi Baca')
                                    ->numeric()
                                    ->suffix('menit')
                                    ->default(3),

                                Toggle::make('featured')
                                    ->label('Featured')
                                    ->default(false),
                            ])
                            ->columns(1),

                        Section::make('SEO')
                            ->description('Metadata untuk kebutuhan mesin pencari dan preview media sosial.')
                            ->schema([
                                TextInput::make('seo_title')
                                    ->label('SEO Title')
                                    ->maxLength(255),

                                Textarea::make('seo_description')
                                    ->label('SEO Description')
                                    ->rows(3)
                                    ->maxLength(500),

                                FileUpload::make('og_image')
                                    ->label('OG Image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('seo/insights')
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(4096),
                            ])
                            ->columns(1),
                    ])
                    ->extraAttributes(['class' => 'edulaw-admin-section-pair']),
            ]);
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
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'submitted', 'reviewed' => 'warning',
                        'archived' => 'gray',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'submitted' => 'Dalam Review',
                        'reviewed' => 'Siap Terbit',
                        'published' => 'Terbit',
                        'archived' => 'Arsip',
                        default => ucfirst($state),
                    }),

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
            ->emptyStateHeading('Belum ada insight')
            ->emptyStateDescription('Artikel insight yang dibuat dari panel admin akan tampil di sini.')
            ->filters([
                SelectFilter::make('insight_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'reviewed' => 'Reviewed',
                        'published' => 'Published',
                        'archived' => 'Archived',
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
