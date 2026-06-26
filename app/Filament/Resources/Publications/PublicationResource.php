<?php

namespace App\Filament\Resources\Publications;

use App\Filament\Concerns\HasSlugFormBehavior;
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
use Filament\Tables\Table;

class PublicationResource extends Resource
{
    use HasSlugFormBehavior;

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
                Section::make('Informasi Utama')
                    ->description('Isi identitas utama publikasi Edulaw.')
                    ->schema([
                        TextInput::make('title')
                            ->label('1. Judul')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Judul publikasi Edulaw')
                            ->live(onBlur: true)
                            ->afterStateUpdated(static::syncSlugFrom())
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->label('2. Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Slug digunakan sebagai alamat publikasi di website.')
                            ->columnSpanFull(),

                        Grid::make([
                            'default' => 1,
                            'lg' => 2,
                        ])
                            ->schema([
                                Group::make()
                                    ->schema([
                                        Select::make('publication_type_id')
                                            ->label('3. Tipe Publikasi')
                                            ->relationship('type', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->placeholder('Pilih tipe publikasi'),

                                        Select::make('authors')
                                            ->label('4. Profil Terkait')
                                            ->relationship('authors', 'name')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Pilih profil'),

                                        TextInput::make('source_name')
                                            ->label('5. Nama Sumber')
                                            ->maxLength(255)
                                            ->placeholder('Edulaw Project'),

                                        TextInput::make('external_url')
                                            ->label('6. URL Eksternal')
                                            ->url()
                                            ->maxLength(255)
                                            ->placeholder('https://...'),
                                    ]),

                                Group::make()
                                    ->schema([
                                        Select::make('tags')
                                            ->label('7. Tag')
                                            ->relationship('tags', 'name')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Select an option'),

                                        Textarea::make('excerpt')
                                            ->label('8. Ringkasan')
                                            ->rows(8)
                                            ->maxLength(300)
                                            ->live()
                                            ->placeholder('Tulis ringkasan publikasi secara singkat dan jelas...')
                                            ->helperText('Maksimal 300 karakter termasuk spasi.'),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'edulaw-admin-two-column-section']),

                Section::make('Deskripsi Publikasi')
                    ->description('Tambahkan uraian lengkap publikasi.')
                    ->schema([
                        RichEditor::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Grid::make([
                    'default' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make('Dokumen dan Cover')
                                    ->description('Unggah cover dan file publikasi.')
                                    ->schema([
                                        FileUpload::make('cover_image')
                                            ->label('Gambar Sampul')
                                            ->helperText('Jika file PDF diisi, cover akan otomatis dibuat dari halaman pertama PDF saat publikasi disimpan.')
                                            ->image()
                                            ->disk('public')
                                            ->directory('publications/covers')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->maxSize(4096)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->downloadable()
                                            ->openable(),

                                        FileUpload::make('pdf_file')
                                            ->label('File PDF')
                                            ->helperText('Halaman pertama PDF akan dipakai sebagai cover publikasi.')
                                            ->disk('public')
                                            ->directory('publications/pdfs')
                                            ->visibility('public')
                                            ->acceptedFileTypes(['application/pdf'])
                                            ->maxSize(20480)
                                            ->downloadable()
                                            ->openable()
                                            ->previewable(false),

                                        TextInput::make('page_count')
                                            ->label('Jumlah Halaman')
                                            ->numeric()
                                            ->suffix('halaman'),
                                    ])
                                    ->columns(1),
                            ]),

                        Group::make()
                            ->schema([
                                Section::make('Publikasi')
                                    ->description('Atur status dan waktu tayang publikasi.')
                                    ->schema([
                                        DatePicker::make('published_at')
                                            ->label('Tanggal Terbit'),

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
                                            ->label('Tampilkan sebagai unggulan')
                                            ->default(false),
                                    ])
                                    ->columns(1),

                                Section::make('SEO (Opsional)')
                                    ->description('Optimasi mesin pencari untuk publikasi ini.')
                                    ->schema([
                                        TextInput::make('seo_title')
                                            ->label('Judul SEO')
                                            ->maxLength(60),

                                        Textarea::make('seo_description')
                                            ->label('Deskripsi SEO')
                                            ->rows(3)
                                            ->maxLength(180)
                                            ->helperText('Maksimal 180 karakter termasuk spasi.'),

                                        FileUpload::make('og_image')
                                            ->label('Gambar OG')
                                            ->image()
                                            ->disk('public')
                                            ->directory('seo/og-images')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->maxSize(4096)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->downloadable()
                                            ->openable(),
                                    ])
                                    ->columns(1)
                                    ->collapsible()
                                    ->collapsed(),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'edulaw-admin-section-pair']),
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
                    ->label('Profil Terkait')
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

                IconColumn::make('featured')
                    ->label('Unggulan')
                    ->boolean()
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

                SelectFilter::make('featured')
                    ->label('Unggulan')
                    ->options([
                        '1' => 'Unggulan',
                        '0' => 'Bukan Unggulan',
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
