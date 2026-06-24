<?php

namespace App\Filament\Resources\Insights;

use App\Filament\Concerns\HasSlugFormBehavior;
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

class InsightResource extends Resource
{
    use HasSlugFormBehavior;

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
                        Grid::make([
                            'default' => 1,
                            'lg' => 2,
                        ])
                            ->schema([
                                Group::make()
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('1. Judul')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Pembakaran Buku Tidak Selalu Menggunakan Api')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(static::syncSlugFrom()),

                                        TextInput::make('slug')
                                            ->label('2. Slug')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->placeholder('pembakaran-buku-tidak-selalu-menggunakan-api')
                                            ->helperText('Slug digunakan sebagai alamat artikel di website.'),

                                        Select::make('insight_category_id')
                                            ->label('3. Kategori')
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->placeholder('Pilih kategori Insight')
                                            ->helperText('Pilih kategori Insight yang sesuai.'),

                                        Select::make('authors')
                                            ->label('4. Profil Terkait')
                                            ->relationship('authors', 'name')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->placeholder('Pilih profil')
                                            ->helperText('Pilih satu atau lebih profil yang berperan sebagai penulis atau kontributor artikel.'),
                                    ]),

                                Group::make()
                                    ->schema([
                                        Select::make('tags')
                                            ->label('5. Tag')
                                            ->relationship('tags', 'name')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Select an option')
                                            ->helperText('Pilih satu atau lebih tag yang relevan dengan artikel.'),

                                        Textarea::make('excerpt')
                                            ->label('6. Ringkasan')
                                            ->rows(9)
                                            ->maxLength(300)
                                            ->live()
                                            ->placeholder('Tulis ringkasan artikel secara singkat dan jelas...')
                                            ->helperText('Maksimal 300 karakter termasuk spasi.'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'edulaw-admin-two-column-section']),

                Section::make('Isi Artikel')
                    ->description('Tulis isi lengkap artikel Insight.')
                    ->schema([
                        RichEditor::make('content')
                            ->label('Isi Artikel')
                            ->required()
                            ->helperText('Tulis isi lengkap artikel Insight.')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('Media Artikel')
                    ->description('Unggah gambar sampul artikel.')
                    ->schema([
                        FileUpload::make('cover_image')
                            ->label('Gambar Sampul')
                            ->image()
                            ->disk('public')
                            ->directory('insights')
                            ->visibility('public')
                            ->imageEditor()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(4096)
                            ->helperText('Gambar akan ditampilkan sebagai sampul artikel.')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Grid::make([
                    'default' => 1,
                    'lg' => 2,
                ])
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
                                    ->label('Tanggal Terbit')
                                    ->seconds(false),

                                TextInput::make('reading_time')
                                    ->label('Reading Time')
                                    ->numeric()
                                    ->suffix('menit')
                                    ->default(3)
                                    ->placeholder('Contoh: 8')
                                    ->helperText('Estimasi waktu baca artikel dalam menit.'),

                                Toggle::make('featured')
                                    ->label('Tampilkan sebagai unggulan')
                                    ->helperText('Aktifkan untuk menampilkan artikel ini di beranda.')
                                    ->default(false),
                            ])
                            ->columns(1),

                        Section::make('SEO (Opsional)')
                            ->description('Metadata untuk kebutuhan mesin pencari dan preview media sosial.')
                            ->schema([
                                TextInput::make('seo_title')
                                    ->label('Judul SEO')
                                    ->maxLength(60)
                                    ->placeholder('Tulis judul SEO'),

                                Textarea::make('seo_description')
                                    ->label('Deskripsi SEO')
                                    ->rows(3)
                                    ->maxLength(180)
                                    ->placeholder('Tulis deskripsi SEO...')
                                    ->helperText('Maksimal 180 karakter termasuk spasi.'),

                                FileUpload::make('og_image')
                                    ->label('Gambar OG')
                                    ->image()
                                    ->disk('public')
                                    ->directory('seo/insights')
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(4096),
                            ])
                            ->columns(1)
                            ->collapsible()
                            ->collapsed(),
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

                IconColumn::make('featured')
                    ->label('Unggulan')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('published_at')
                    ->label('Terbit')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),

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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInsights::route('/'),
            'create' => Pages\CreateInsight::route('/create'),
            'edit' => Pages\EditInsight::route('/{record}/edit'),
        ];
    }
}
