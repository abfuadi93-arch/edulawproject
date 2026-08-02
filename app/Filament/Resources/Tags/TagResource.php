<?php

namespace App\Filament\Resources\Tags;

use App\Filament\Resources\Tags\Pages\CreateTag;
use App\Filament\Resources\Tags\Pages\EditTag;
use App\Filament\Resources\Tags\Pages\ListTags;
use App\Models\Tag;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Referensi';

    protected static ?string $navigationLabel = 'Tag';

    protected static ?string $modelLabel = 'Tag';

    protected static ?string $pluralModelLabel = 'Tag';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->extraAttributes(['class' => 'edulaw-admin-reference-form'])
            ->components([
                Section::make('Informasi')
                    ->icon('heroicon-o-tag')
                    ->description('Tag digunakan lintas Editorial dan Publikasi.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nama tag')
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
                            ->helperText('Slug digunakan sebagai identitas tag di website.'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->extraAttributes(['class' => 'edulaw-reference-table'])
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->fontFamily('mono'),

                TextColumn::make('insights_count')
                    ->label('Artikel')
                    ->numeric()
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('publications_count')
                    ->label('Publikasi')
                    ->numeric()
                    ->sortable()
                    ->visibleFrom('lg'),

                TextColumn::make('total_usage')
                    ->label('Total Penggunaan')
                    ->state(fn (Tag $record): int => $record->insights_count + $record->publications_count)
                    ->numeric()
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
            ->searchPlaceholder('Cari tag...')
            ->emptyStateIcon('heroicon-o-tag')
            ->emptyStateHeading('Belum ada tag')
            ->emptyStateDescription('Buat tag untuk mengelompokkan artikel dan publikasi.')
            ->filters([
                TernaryFilter::make('used')
                    ->label('Penggunaan')
                    ->placeholder('Semua')
                    ->trueLabel('Digunakan')
                    ->falseLabel('Belum digunakan')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->where(fn (Builder $query): Builder => $query->has('insights')->orHas('publications')),
                        false: fn (Builder $query): Builder => $query->doesntHave('insights')->doesntHave('publications'),
                    ),
                TernaryFilter::make('used_in_insights')
                    ->label('Digunakan di Artikel')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->has('insights'),
                        false: fn (Builder $query): Builder => $query->doesntHave('insights'),
                    ),
                TernaryFilter::make('used_in_publications')
                    ->label('Digunakan di Publikasi')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->has('publications'),
                        false: fn (Builder $query): Builder => $query->doesntHave('publications'),
                    ),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->label('Edit'),
                    DeleteAction::make()
                        ->label('Hapus')
                        ->requiresConfirmation()
                        ->visible(fn (Tag $record): bool => ($record->insights_count + $record->publications_count) === 0),
                ])->label('Aksi lainnya')->icon('heroicon-o-ellipsis-vertical')->tooltip('Aksi lainnya')->color('gray'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount(['insights', 'publications']);
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
            'index' => ListTags::route('/'),
            'create' => CreateTag::route('/create'),
            'edit' => EditTag::route('/{record}/edit'),
        ];
    }
}
