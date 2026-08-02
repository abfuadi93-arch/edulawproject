<?php

namespace App\Filament\Resources\PublicationTypes;

use App\Filament\Resources\PublicationTypes\Pages\CreatePublicationType;
use App\Filament\Resources\PublicationTypes\Pages\EditPublicationType;
use App\Filament\Resources\PublicationTypes\Pages\ListPublicationTypes;
use App\Models\PublicationType;
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

class PublicationTypeResource extends Resource
{
    protected static ?string $model = PublicationType::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Referensi';

    protected static ?string $navigationLabel = 'Tipe Publikasi';

    protected static ?string $modelLabel = 'Tipe Publikasi';

    protected static ?string $pluralModelLabel = 'Tipe Publikasi';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->extraAttributes(['class' => 'edulaw-admin-reference-form'])
            ->components([
                Section::make('Informasi')
                    ->icon('heroicon-o-document-duplicate')
                    ->description('Tipe untuk policy brief, kajian hukum, naskah akademik, working paper, dan buku digital.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nama tipe publikasi')
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
                            ->helperText('Slug digunakan sebagai identitas tipe publikasi.'),

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
            ->extraAttributes(['class' => 'edulaw-reference-table'])
            ->columns([
                ViewColumn::make('type')
                    ->label('Tipe Publikasi')
                    ->view('filament.tables.columns.reference-name', fn (PublicationType $record): array => [
                        'name' => $record->name,
                        'slug' => $record->slug,
                        'description' => $record->description,
                    ])
                    ->searchable(['name', 'slug', 'description'])
                    ->sortable(),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable()
                    ->visibleFrom('lg'),

                TextColumn::make('publications_count')
                    ->label('Jumlah Publikasi')
                    ->numeric()
                    ->sortable()
                    ->visibleFrom('md'),

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
            ->searchPlaceholder('Cari tipe publikasi...')
            ->emptyStateIcon('heroicon-o-document-duplicate')
            ->emptyStateHeading('Belum ada tipe publikasi')
            ->emptyStateDescription('Buat tipe untuk mengelompokkan riset dan publikasi.')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
                TernaryFilter::make('used')
                    ->label('Penggunaan')
                    ->placeholder('Semua')
                    ->trueLabel('Digunakan')
                    ->falseLabel('Belum digunakan')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->has('publications'),
                        false: fn (Builder $query): Builder => $query->doesntHave('publications'),
                    ),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->label('Edit'),
                    Action::make('toggle_active')
                        ->label(fn (PublicationType $record): string => $record->is_active ? 'Nonaktifkan' : 'Aktifkan')
                        ->icon(fn (PublicationType $record): string => $record->is_active ? 'heroicon-o-no-symbol' : 'heroicon-o-check-circle')
                        ->color(fn (PublicationType $record): string => $record->is_active ? 'warning' : 'success')
                        ->authorize('update')
                        ->requiresConfirmation()
                        ->action(fn (PublicationType $record) => $record->update(['is_active' => ! $record->is_active])),
                    DeleteAction::make()
                        ->label('Hapus')
                        ->requiresConfirmation()
                        ->visible(fn (PublicationType $record): bool => $record->publications_count === 0),
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
        return parent::getEloquentQuery()->withCount('publications');
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
            'index' => ListPublicationTypes::route('/'),
            'create' => CreatePublicationType::route('/create'),
            'edit' => EditPublicationType::route('/{record}/edit'),
        ];
    }
}
