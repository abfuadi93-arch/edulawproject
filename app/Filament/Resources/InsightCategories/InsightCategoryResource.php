<?php

namespace App\Filament\Resources\InsightCategories;

use App\Filament\Resources\InsightCategories\Pages\CreateInsightCategory;
use App\Filament\Resources\InsightCategories\Pages\EditInsightCategory;
use App\Filament\Resources\InsightCategories\Pages\ListInsightCategories;
use App\Models\InsightCategory;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class InsightCategoryResource extends Resource
{
    protected static ?string $model = InsightCategory::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Reference';

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
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(72)
                    ->wrap()
                    ->placeholder('Belum ada deskripsi')
                    ->toggleable(),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('show_on_editorial_index')
                    ->label('Tampil')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort(fn (Builder $query): Builder => $query
                ->orderBy('sort_order')
                ->orderBy('name'))
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),

                TernaryFilter::make('show_on_editorial_index')
                    ->label('Tampil di Halaman Editorial'),
            ])
            ->recordActions([
                EditAction::make(),
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
            'index' => ListInsightCategories::route('/'),
            'create' => CreateInsightCategory::route('/create'),
            'edit' => EditInsightCategory::route('/{record}/edit'),
        ];
    }
}
