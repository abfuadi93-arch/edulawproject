<?php

namespace App\Filament\Resources\ProgramCategories;

use App\Filament\Resources\ProgramCategories\Pages\CreateProgramCategory;
use App\Filament\Resources\ProgramCategories\Pages\EditProgramCategory;
use App\Filament\Resources\ProgramCategories\Pages\ListProgramCategories;
use App\Models\ProgramCategory;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProgramCategoryResource extends Resource
{
    protected static ?string $model = ProgramCategory::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Referensi';

    protected static ?string $navigationLabel = 'Kategori Program';

    protected static ?string $modelLabel = 'Kategori Program';

    protected static ?string $pluralModelLabel = 'Kategori Program';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->extraAttributes(['class' => 'edulaw-admin-reference-form'])
            ->components([
                Section::make('Informasi')
                    ->icon('heroicon-o-folder')
                    ->description('Kategori untuk kelas, diskusi, webinar, workshop, pelatihan, dan short course.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nama kategori program')
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
                            ->helperText('Slug digunakan sebagai identitas kategori program.'),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->columnSpanFull(),

                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),

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
            ->extraAttributes(['class' => 'edulaw-reference-table edulaw-program-category-table'])
            ->columns([
                ViewColumn::make('category')
                    ->label('Kategori')
                    ->view('filament.tables.columns.reference-name', fn (ProgramCategory $record): array => [
                        'name' => $record->name,
                        'slug' => $record->slug,
                    ])
                    ->searchable(['name', 'slug', 'description'])
                    ->sortable(['name'])
                    ->extraHeaderAttributes(['class' => 'edulaw-program-category-name-header'])
                    ->extraCellAttributes(['class' => 'edulaw-program-category-name-cell']),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->sortable()
                    ->extraHeaderAttributes(['class' => 'edulaw-program-category-status-header'])
                    ->extraCellAttributes(['class' => 'edulaw-program-category-status-cell']),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->badge()
                    ->color('gray')
                    ->alignCenter()
                    ->sortable()
                    ->visibleFrom('lg')
                    ->extraHeaderAttributes(['class' => 'edulaw-program-category-order-header'])
                    ->extraCellAttributes(['class' => 'edulaw-program-category-order-cell']),

                TextColumn::make('programs_count')
                    ->label('Program')
                    ->numeric()
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'primary' : 'gray')
                    ->alignCenter()
                    ->sortable()
                    ->visibleFrom('md')
                    ->extraHeaderAttributes(['class' => 'edulaw-program-category-count-header'])
                    ->extraCellAttributes(['class' => 'edulaw-program-category-count-cell']),

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
            ->searchPlaceholder('Cari kategori...')
            ->emptyStateIcon('heroicon-o-folder')
            ->emptyStateHeading('Belum ada kategori program')
            ->emptyStateDescription('Buat kategori untuk mengelompokkan program Edulaw.')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
                TernaryFilter::make('used')
                    ->label('Penggunaan')
                    ->placeholder('Semua')
                    ->trueLabel('Digunakan')
                    ->falseLabel('Belum digunakan')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->has('programs'),
                        false: fn (Builder $query): Builder => $query->doesntHave('programs'),
                    ),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->label('Edit'),
                    Action::make('toggle_active')
                        ->label(fn (ProgramCategory $record): string => $record->is_active ? 'Nonaktifkan' : 'Aktifkan')
                        ->icon(fn (ProgramCategory $record): string => $record->is_active ? 'heroicon-o-no-symbol' : 'heroicon-o-check-circle')
                        ->color(fn (ProgramCategory $record): string => $record->is_active ? 'warning' : 'success')
                        ->authorize('update')
                        ->requiresConfirmation()
                        ->action(fn (ProgramCategory $record) => $record->update(['is_active' => ! $record->is_active])),
                    DeleteAction::make()
                        ->label('Hapus')
                        ->requiresConfirmation()
                        ->visible(fn (ProgramCategory $record): bool => $record->programs_count === 0),
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
        return parent::getEloquentQuery()->withCount('programs');
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
            'index' => ListProgramCategories::route('/'),
            'create' => CreateProgramCategory::route('/create'),
            'edit' => EditProgramCategory::route('/{record}/edit'),
        ];
    }
}
