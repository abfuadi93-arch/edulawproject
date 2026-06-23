<?php

namespace App\Filament\Resources\Publications;

use App\Filament\Resources\Publications\Pages\CreatePublication;
use App\Filament\Resources\Publications\Pages\EditPublication;
use App\Filament\Resources\Publications\Pages\ListPublications;
use App\Models\Publication;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PublicationResource extends Resource
{
    protected static ?string $model = Publication::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Konten Website';

    protected static ?string $navigationLabel = 'Publikasi';

    protected static ?string $modelLabel = 'Publikasi';

    protected static ?string $pluralModelLabel = 'Publikasi';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Publikasi')
                    ->description('Isi identitas utama publikasi Edulaw.')
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

                        Select::make('publication_type_id')
                            ->label('Tipe Publikasi')
                            ->relationship('type', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('authors')
                            ->label('Penulis')
                            ->relationship('authors', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),

                        Select::make('tags')
                            ->label('Tag')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),

                        Textarea::make('excerpt')
                            ->label('Ringkasan')
                            ->rows(4)
                            ->maxLength(600)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Deskripsi dan Berkas')
                    ->description('Tambahkan uraian, cover, dan file publikasi.')
                    ->schema([
                        RichEditor::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),

                        FileUpload::make('cover_image')
                            ->label('Cover Publikasi')
                            ->image()
                            ->disk('public')
                            ->directory('publications/covers')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(4096)
                            ->downloadable()
                            ->openable(),

                       FileUpload::make('pdf_file')
                            ->label('File PDF')
                            ->disk('public')
                            ->directory('publications/files')
                            ->visibility('public')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)
                            ->downloadable()
                            ->openable()
                            ->previewable(false),

                        TextInput::make('external_url')
                            ->label('External URL')
                            ->url()
                            ->maxLength(255),

                        TextInput::make('source_name')
                            ->label('Nama Sumber')
                            ->maxLength(255),
                    ])
                    ->columns(1),

                Section::make('Status dan Metadata')
                    ->schema([
                        DatePicker::make('published_at')
                            ->label('Tanggal Terbit'),

                        TextInput::make('page_count')
                            ->label('Jumlah Halaman')
                            ->numeric(),

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

                Section::make('SEO')
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
                            ->directory('publications/og-images')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(4096)
                            ->downloadable()
                            ->openable(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(70)
                    ->wrap()
                    ->description(fn (Publication $record): ?string => $record->excerpt),

                TextColumn::make('type.name')
                    ->label('Tipe')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('authors.name')
                    ->label('Penulis')
                    ->badge()
                    ->color('gray')
                    ->separator(',')
                    ->limitList(2),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'archived' => 'gray',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                        default => ucfirst($state),
                    }),

                TextColumn::make('published_at')
                    ->label('Tanggal Terbit')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('page_count')
                    ->label('Halaman')
                    ->suffix(' hlm')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('published_at', 'desc')
            ->searchPlaceholder('Cari publikasi...')
            ->paginationPageOptions([10, 25, 50])
            ->emptyStateHeading('Belum ada publikasi')
            ->emptyStateDescription('Publikasi riset dan dokumen yang diunggah akan tampil di sini.')
            ->filters([
                SelectFilter::make('publication_type_id')
                    ->label('Tipe Publikasi')
                    ->relationship('type', 'name'),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPublications::route('/'),
            'create' => CreatePublication::route('/create'),
            'edit' => EditPublication::route('/{record}/edit'),
        ];
    }
}
