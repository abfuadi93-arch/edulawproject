<?php

namespace App\Filament\Resources\InsightCategories;

use App\Filament\Resources\InsightCategories\Pages\CreateInsightCategory;
use App\Filament\Resources\InsightCategories\Pages\EditInsightCategory;
use App\Filament\Resources\InsightCategories\Pages\ListInsightCategories;
use App\Models\InsightCategory;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class InsightCategoryResource extends Resource
{
    protected static ?string $model = InsightCategory::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Referensi';

    protected static ?string $navigationLabel = 'Kategori Editorial';

    protected static ?string $modelLabel = 'Kategori Editorial';

    protected static ?string $pluralModelLabel = 'Kategori Editorial';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->extraAttributes(['class' => 'edulaw-admin-reference-form'])
            ->components([
                Section::make('Informasi')
                    ->icon('heroicon-o-rectangle-stack')
                    ->description('Kategori untuk artikel, opini hukum, regulatory update, dan edukasi hukum.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nama kategori Editorial')
                            ->live(onBlur: true)
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
                            ->helperText('Slug digunakan sebagai identitas kategori di website.'),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->maxLength(120)
                            ->helperText('Maksimal 120 karakter. Akan tampil pada card kategori Editorial.')
                            ->columnSpanFull(),

                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required()
                            ->helperText('Angka lebih kecil tampil lebih dahulu.'),

                        Toggle::make('show_on_editorial_index')
                            ->label('Tampilkan di Halaman Editorial')
                            ->default(true)
                            ->helperText('Nonaktifkan untuk menyembunyikan kategori dari section Editorial tanpa menghapusnya.'),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])
                    ->columns([
                        'default' => 1,
                        'lg' => 2,
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->extraAttributes(['class' => 'edulaw-reference-table edulaw-insight-category-table'])
            ->columns([
                ViewColumn::make('category')
                    ->label('Kategori')
                    ->view('filament.tables.columns.reference-name', fn (InsightCategory $record): array => [
                        'name' => $record->name,
                        'slug' => $record->slug,
                    ])
                    ->searchable(['name', 'slug', 'description'])
                    ->sortable(['name'])
                    ->extraHeaderAttributes(['class' => 'edulaw-insight-category-name-header'])
                    ->extraCellAttributes(['class' => 'edulaw-insight-category-name-cell']),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->sortable()
                    ->extraHeaderAttributes(['class' => 'edulaw-insight-category-status-header'])
                    ->extraCellAttributes(['class' => 'edulaw-insight-category-status-cell']),

                IconColumn::make('show_on_editorial_index')
                    ->label('Indeks')
                    ->boolean()
                    ->alignCenter()
                    ->sortable()
                    ->visibleFrom('lg')
                    ->extraHeaderAttributes(['class' => 'edulaw-insight-category-index-header'])
                    ->extraCellAttributes(['class' => 'edulaw-insight-category-index-cell']),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->badge()
                    ->color('gray')
                    ->alignCenter()
                    ->sortable()
                    ->visibleFrom('lg')
                    ->extraHeaderAttributes(['class' => 'edulaw-insight-category-order-header'])
                    ->extraCellAttributes(['class' => 'edulaw-insight-category-order-cell']),

                TextColumn::make('insights_count')
                    ->label('Artikel')
                    ->numeric()
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'primary' : 'gray')
                    ->alignCenter()
                    ->sortable()
                    ->visibleFrom('md')
                    ->extraHeaderAttributes(['class' => 'edulaw-insight-category-count-header'])
                    ->extraCellAttributes(['class' => 'edulaw-insight-category-count-cell']),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort(fn (Builder $query): Builder => $query
                ->orderBy('sort_order')
                ->orderBy('name'))
            ->searchPlaceholder('Cari kategori...')
            ->emptyStateIcon('heroicon-o-rectangle-stack')
            ->emptyStateHeading('Belum ada kategori editorial')
            ->emptyStateDescription('Buat kategori untuk mengelompokkan artikel Edulaw.')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),

                TernaryFilter::make('show_on_editorial_index')
                    ->label('Tampil di Halaman Editorial'),

                TernaryFilter::make('used')
                    ->label('Penggunaan')
                    ->placeholder('Semua')
                    ->trueLabel('Digunakan')
                    ->falseLabel('Belum digunakan')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->has('insights'),
                        false: fn (Builder $query): Builder => $query->doesntHave('insights'),
                    ),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->label('Edit'),
                    Action::make('toggle_active')
                        ->label(fn (InsightCategory $record): string => $record->is_active ? 'Nonaktifkan' : 'Aktifkan')
                        ->icon(fn (InsightCategory $record): string => $record->is_active ? 'heroicon-o-no-symbol' : 'heroicon-o-check-circle')
                        ->color(fn (InsightCategory $record): string => $record->is_active ? 'warning' : 'success')
                        ->authorize('update')
                        ->requiresConfirmation()
                        ->action(fn (InsightCategory $record) => $record->update(['is_active' => ! $record->is_active])),
                    DeleteAction::make()
                        ->label('Hapus')
                        ->requiresConfirmation()
                        ->visible(fn (InsightCategory $record): bool => $record->insights_count === 0),
                ])->label('Aksi lainnya')->icon('heroicon-o-ellipsis-vertical')->tooltip('Aksi lainnya')->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Aktifkan')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->authorizeIndividualRecords('update')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),
                    BulkAction::make('deactivate')
                        ->label('Nonaktifkan')
                        ->icon('heroicon-o-no-symbol')
                        ->color('warning')
                        ->authorizeIndividualRecords('update')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('insights');
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
            'index' => ListInsightCategories::route('/'),
            'create' => CreateInsightCategory::route('/create'),
            'edit' => EditInsightCategory::route('/{record}/edit'),
        ];
    }
}
