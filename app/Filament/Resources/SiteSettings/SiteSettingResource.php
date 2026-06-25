<?php

namespace App\Filament\Resources\SiteSettings;

use App\Filament\Resources\SiteSettings\Pages\CreateSiteSetting;
use App\Filament\Resources\SiteSettings\Pages\EditSiteSetting;
use App\Filament\Resources\SiteSettings\Pages\ListSiteSettings;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SiteSettingResource extends Resource
{
    protected static bool $isDiscovered = false;

    protected static ?string $model = SiteSetting::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan Website';

    protected static ?string $navigationLabel = 'Pengaturan Situs';

    protected static ?string $modelLabel = 'Pengaturan Situs';

    protected static ?string $pluralModelLabel = 'Pengaturan Situs';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'label';

    public static function canAccess(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Pengaturan')
                    ->schema([
                        TextInput::make('group')
                            ->label('Grup')
                            ->required()
                            ->maxLength(255)
                            ->default('general'),

                        TextInput::make('label')
                            ->label('Label')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('key')
                            ->label('Key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Gunakan key stabil, contoh: contact.email atau social.instagram_url.'),

                        Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'text' => 'Text',
                                'textarea' => 'Textarea',
                                'email' => 'Email',
                                'url' => 'URL',
                                'image' => 'Image path',
                            ])
                            ->default('text')
                            ->required(),
                    ])
                    ->columns(1),

                Section::make('Nilai')
                    ->schema([
                        Textarea::make('value')
                            ->label('Value')
                            ->rows(5)
                            ->columnSpanFull(),

                        Textarea::make('help_text')
                            ->label('Catatan Bantuan')
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_public')
                            ->label('Tersedia untuk website publik')
                            ->default(true),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group')
                    ->label('Grup')
                    ->badge()
                    ->grow(false)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('label')
                    ->label('Pengaturan')
                    ->searchable()
                    ->sortable()
                    ->limit(42)
                    ->wrap()
                    ->width('18rem')
                    ->description(fn (SiteSetting $record): string => $record->key),

                TextColumn::make('key')
                    ->label('Key')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('value')
                    ->label('Value')
                    ->limit(72)
                    ->wrap()
                    ->searchable()
                    ->placeholder('-'),

                IconColumn::make('is_public')
                    ->label('Publik')
                    ->boolean()
                    ->grow(false)
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('group')
            ->searchPlaceholder('Cari pengaturan...')
            ->paginationPageOptions([10, 25, 50])
            ->filters([
                SelectFilter::make('group')
                    ->label('Grup')
                    ->options(fn () => SiteSetting::query()
                        ->orderBy('group')
                        ->pluck('group', 'group')
                        ->all()),
            ])
            ->recordActions([
                Actions\EditAction::make(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSiteSettings::route('/'),
            'create' => CreateSiteSetting::route('/create'),
            'edit' => EditSiteSetting::route('/{record}/edit'),
        ];
    }
}
