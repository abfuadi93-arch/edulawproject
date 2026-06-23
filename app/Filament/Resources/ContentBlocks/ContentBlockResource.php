<?php

namespace App\Filament\Resources\ContentBlocks;

use App\Filament\Resources\ContentBlocks\Pages\CreateContentBlock;
use App\Filament\Resources\ContentBlocks\Pages\EditContentBlock;
use App\Filament\Resources\ContentBlocks\Pages\ListContentBlocks;
use App\Models\ContentBlock;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\KeyValue;
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

class ContentBlockResource extends Resource
{
    protected static ?string $model = ContentBlock::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan Website';

    protected static ?string $navigationLabel = 'Blok Konten';

    protected static ?string $modelLabel = 'Blok Konten';

    protected static ?string $pluralModelLabel = 'Blok Konten';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Lokasi dan Status')
                    ->schema([
                        Select::make('area')
                            ->label('Area Website')
                            ->options(self::areaOptions())
                            ->searchable()
                            ->required(),

                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])
                    ->columns(1),

                Section::make('Konten')
                    ->schema([
                        TextInput::make('eyebrow')
                            ->label('Eyebrow / Label Kecil')
                            ->maxLength(255),

                        TextInput::make('title')
                            ->label('Judul / Nama')
                            ->maxLength(255),

                        TextInput::make('subtitle')
                            ->label('Subtitle / Nilai')
                            ->maxLength(255),

                        Textarea::make('body')
                            ->label('Isi / Deskripsi')
                            ->rows(6)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Media dan Tampilan')
                    ->schema([
                        TextInput::make('image')
                            ->label('Path / URL Gambar')
                            ->placeholder('images/hero/hero-edulaw.jpg atau https://...')
                            ->maxLength(255),

                        TextInput::make('image_alt')
                            ->label('Alt Gambar')
                            ->maxLength(255),

                        TextInput::make('icon')
                            ->label('Icon')
                            ->placeholder('book, users, chart, scale, ...')
                            ->maxLength(255),

                        TextInput::make('accent')
                            ->label('Class Accent')
                            ->placeholder('bg-brand-amber text-brand-black')
                            ->maxLength(255),
                    ])
                    ->columns(1),

                Section::make('Link dan Data Tambahan')
                    ->schema([
                        TextInput::make('url')
                            ->label('URL Utama')
                            ->maxLength(255),

                        TextInput::make('url_label')
                            ->label('Label URL')
                            ->maxLength(255),

                        KeyValue::make('meta')
                            ->label('Meta')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('image')
                    ->label('Gambar')
                    ->limit(36)
                    ->toggleable(),

                TextColumn::make('area')
                    ->label('Area')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('subtitle')
                    ->label('Subtitle')
                    ->limit(40)
                    ->toggleable(),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('area')
            ->filters([
                SelectFilter::make('area')
                    ->label('Area')
                    ->options(self::areaOptions()),
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
            'index' => ListContentBlocks::route('/'),
            'create' => CreateContentBlock::route('/create'),
            'edit' => EditContentBlock::route('/{record}/edit'),
        ];
    }

    private static function areaOptions(): array
    {
        return [
            'home.hero' => 'Home: Hero',
            'home.values' => 'Home: Value Cards',
            'home.audience_intro' => 'Home: Intro Audience',
            'home.audience' => 'Home: Audience Cards',
            'shared.cta' => 'Global: CTA Kolaborasi',
            'about.hero' => 'Tentang: Hero',
            'about.stats' => 'Tentang: Angka',
            'about.leaders' => 'Tentang: Penggerak',
            'about.managers' => 'Tentang: Manager',
            'about.team' => 'Tentang: Tim',
            'about.why' => 'Tentang: Mengapa',
            'about.focus_intro' => 'Tentang: Intro Fokus',
            'about.focus' => 'Tentang: Fokus',
            'about.timeline_intro' => 'Tentang: Intro Timeline',
            'about.timeline' => 'Tentang: Timeline',
            'about.timeline_meta' => 'Tentang: Info Samping Timeline',
        ];
    }
}
