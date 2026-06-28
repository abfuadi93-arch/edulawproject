<?php

namespace App\Filament\Resources\PublicationTypes;

use App\Filament\Concerns\HasSlugFormBehavior;
use App\Filament\Resources\PublicationTypes\Pages\CreatePublicationType;
use App\Filament\Resources\PublicationTypes\Pages\EditPublicationType;
use App\Filament\Resources\PublicationTypes\Pages\ListPublicationTypes;
use App\Models\PublicationType;
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

class PublicationTypeResource extends Resource
{
    use HasSlugFormBehavior;

    protected static ?string $model = PublicationType::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Referensi Konten';

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
                            ->afterStateUpdated(static::syncSlugFrom()),

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
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

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
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
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
            'index' => ListPublicationTypes::route('/'),
            'create' => CreatePublicationType::route('/create'),
            'edit' => EditPublicationType::route('/{record}/edit'),
        ];
    }
}
