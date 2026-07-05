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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class InsightResource extends Resource
{
    protected static ?string $model = Insight::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Konten Website';

    protected static ?string $navigationLabel = 'Edulaw Editorial';

    protected static ?string $modelLabel = 'Editorial';

    protected static ?string $pluralModelLabel = 'Editorial';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

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
                                Section::make('1. Informasi Artikel')
                                    ->icon('heroicon-o-document-text')
                                    ->description('Data utama yang membentuk identitas artikel di halaman Edulaw Editorial.')
                                    ->schema([
                                        Grid::make([
                                            'default' => 1,
                                            'lg' => 2,
                                        ])
                                            ->schema([
                                                TextInput::make('title')
                                                    ->label('Judul Editorial')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder('Pembakaran Buku Tidak Selalu Menggunakan Api')
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function ($get, $set, ?string $old, ?string $state): void {
                                                        $currentSlug = (string) ($get('slug') ?? '');

                                                        if (filled($currentSlug) && $get('status') === 'published') {
                                                            return;
                                                        }

                                                        $oldSlug = Str::slug((string) $old);

                                                        if (filled($currentSlug) && $currentSlug !== $oldSlug) {
                                                            return;
                                                        }

                                                        $set('slug', Str::slug((string) $state));
                                                    })
                                                    ->helperText('Judul singkat dan kuat untuk halaman Editorial.')
                                                    ->columnSpanFull(),

                                                TextInput::make('slug')
                                                    ->label('Slug')
                                                    ->required()
                                                    ->unique(ignoreRecord: true)
                                                    ->maxLength(255)
                                                    ->placeholder('pembakaran-buku-tidak-selalu-menggunakan-api')
                                                    ->helperText('Otomatis dibuat dari judul, dapat disesuaikan.')
                                                    ->columnSpanFull(),

                                                Select::make('insight_category_id')
                                                    ->label('Kategori')
                                                    ->relationship('category', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->placeholder('Pilih kategori Editorial')
                                                    ->helperText('Pilih jenis konten, misalnya Editorial, Opini, Analisis, atau Review Putusan.'),

                                                Select::make('tags')
                                                    ->label('Topik')
                                                    ->relationship('tags', 'name')
                                                    ->multiple()
                                                    ->searchable()
                                                    ->preload()
                                                    ->placeholder('Select an option')
                                                    ->helperText('Pilih tema utama dari artikel.'),
                                            ]),

                                        Textarea::make('excerpt')
                                            ->label('Ringkasan')
                                            ->rows(4)
                                            ->maxLength(300)
                                            ->live()
                                            ->placeholder('Tulis ringkasan artikel secara singkat dan jelas...')
                                            ->helperText('Maksimal 300 karakter termasuk spasi.')
                                            ->columnSpanFull(),
                                    ])
                                    ->extraAttributes(['class' => 'edulaw-admin-two-column-section']),

                                Section::make('2. Isi Artikel')
                                    ->icon('heroicon-o-pencil-square')
                                    ->description('Tulis artikel utama. Gunakan heading, kutipan, daftar, tautan, dan gambar seperlunya.')
                                    ->schema([
                                        RichEditor::make('content')
                                            ->label('Body')
                                            ->required()
                                            ->helperText('Gunakan paragraf pendek, subjudul yang rapi, dan sisipkan gambar hanya jika membantu pembaca.')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('SEO & Pratinjau')
                                    ->icon('heroicon-o-magnifying-glass')
                                    ->description('Metadata untuk hasil pencarian dan preview saat artikel dibagikan.')
                                    ->schema([
                                        TextInput::make('seo_title')
                                            ->label('SEO Title')
                                            ->maxLength(60)
                                            ->placeholder('Jika kosong, judul artikel akan digunakan.')
                                            ->helperText('Ideal 50-60 karakter.'),

                                        Textarea::make('seo_description')
                                            ->label('Meta Description')
                                            ->rows(3)
                                            ->maxLength(180)
                                            ->placeholder('Deskripsi singkat untuk mesin pencari...')
                                            ->helperText('Maksimal 180 karakter termasuk spasi.'),

                                        FileUpload::make('og_image')
                                            ->label('Gambar OG')
                                            ->image()
                                            ->disk('public')
                                            ->directory('seo/og-images')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->maxSize(4096),
                                    ])
                                    ->collapsible(),
                            ])
                            ->columnSpan(['xl' => 8])
                            ->extraAttributes(['class' => 'edulaw-admin-main-column']),

                        Group::make()
                            ->schema([
                                Section::make('Status Publikasi')
                                    ->icon('heroicon-o-paper-airplane')
                                    ->description('Status dan waktu tayang artikel.')
                                    ->schema([
                                        Select::make('status')
                                            ->label('Status Editorial')
                                            ->options(fn (): array => static::statusOptionsForCurrentUser())
                                            ->default('draft')
                                            ->disabled(fn (string $operation): bool => $operation === 'create' && ! static::canManageEditorialWorkflow())
                                            ->required(),

                                        DateTimePicker::make('published_at')
                                            ->label('Tanggal Publikasi')
                                            ->seconds(false)
                                            ->disabled(fn (): bool => ! static::canManageEditorialWorkflow()),

                                        Toggle::make('featured')
                                            ->label('Artikel Unggulan')
                                            ->helperText('Aktifkan untuk menampilkan artikel ini di beranda.')
                                            ->default(false)
                                            ->disabled(fn (): bool => ! static::canManageEditorialWorkflow()),

                                        TextInput::make('reading_time')
                                            ->label('Reading Time')
                                            ->numeric()
                                            ->suffix('menit')
                                            ->default(3)
                                            ->placeholder('Contoh: 8')
                                            ->helperText('Estimasi waktu baca artikel dalam menit.'),
                                    ])
                                    ->columns(1),

                                Section::make('Penulis')
                                    ->icon('heroicon-o-user-circle')
                                    ->description('Identitas penulis utama dan kontributor artikel.')
                                    ->schema([
                                        Select::make('authors')
                                            ->label('Profil Terkait')
                                            ->relationship('authors', 'name')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->placeholder('Pilih profil')
                                            ->helperText('Pilih satu atau lebih profil yang berperan sebagai penulis atau kontributor artikel.'),
                                    ])
                                    ->columns(1),

                                Section::make('Media Artikel')
                                    ->icon('heroicon-o-photo')
                                    ->description('Gambar utama untuk listing dan detail artikel.')
                                    ->schema([
                                        FileUpload::make('cover_image')
                                            ->label('Gambar Utama')
                                            ->image()
                                            ->disk('public')
                                            ->directory('insights')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->maxSize(4096)
                                            ->helperText('Rekomendasi rasio 16:9, ukuran maksimal 4MB.')
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if (
            Gate::forUser($user)->allows('update all insights')
            || Gate::forUser($user)->allows('review insights')
            || Gate::forUser($user)->allows('publish insights')
        ) {
            return $query;
        }

        return $query->where('created_by', $user->id);
    }

    public static function canManageEditorialWorkflow(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return Gate::forUser($user)->allows('update all insights')
            || Gate::forUser($user)->allows('review insights')
            || Gate::forUser($user)->allows('publish insights')
            || Gate::forUser($user)->allows('archive insights');
    }

    public static function statusOptionsForCurrentUser(): array
    {
        if (! static::canManageEditorialWorkflow()) {
            return [
                'draft' => 'Draft',
                'submitted' => 'Submitted',
            ];
        }

        return [
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'reviewed' => 'Reviewed',
            'published' => 'Published',
            'archived' => 'Archived',
        ];
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
            ->emptyStateHeading('Belum ada editorial')
            ->emptyStateDescription('Artikel editorial yang dibuat dari panel admin akan tampil di sini.')
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
