<?php

namespace App\Filament\Resources\Opportunities;

use App\Filament\Concerns\HasSlugFormBehavior;
use App\Filament\Resources\Opportunities\Pages\CreateOpportunity;
use App\Filament\Resources\Opportunities\Pages\EditOpportunity;
use App\Filament\Resources\Opportunities\Pages\ListOpportunities;
use App\Models\Opportunity;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class OpportunityResource extends Resource
{
    use HasSlugFormBehavior;

    protected static ?string $model = Opportunity::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Konten Website';

    protected static ?string $navigationLabel = 'Opportunities';

    protected static ?string $modelLabel = 'Opportunity';

    protected static ?string $pluralModelLabel = 'Opportunities';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama')
                    ->description('Kelola informasi ringkas peluang yang tampil di halaman Opportunities.')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'lg' => 2,
                        ])
                            ->schema([
                                Group::make()
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Judul')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(static::syncSlugFrom()),

                                        TextInput::make('slug')
                                            ->label('Slug')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->helperText('Slug digunakan sebagai alamat peluang di website.'),

                                        Select::make('type')
                                            ->label('Jenis Peluang')
                                            ->options([
                                                'scholarship' => 'Beasiswa',
                                                'internship' => 'Magang',
                                                'volunteer' => 'Volunteer',
                                                'fellowship' => 'Fellowship',
                                                'call_for_paper' => 'Call for Papers',
                                                'competition' => 'Kompetisi',
                                                'open_collaboration' => 'Kolaborasi Terbuka',
                                            ])
                                            ->default('open_collaboration')
                                            ->searchable()
                                            ->required(),

                                        TextInput::make('format')
                                            ->label('Format')
                                            ->placeholder('Online / Offline / Hybrid'),

                                        TextInput::make('location')
                                            ->label('Lokasi')
                                            ->maxLength(255)
                                            ->placeholder('Online / Jakarta / Hybrid'),
                                    ]),

                                Group::make()
                                    ->schema([
                                        Textarea::make('excerpt')
                                            ->label('Ringkasan')
                                            ->rows(7)
                                            ->maxLength(300)
                                            ->required()
                                            ->live()
                                            ->placeholder('Tulis ringkasan peluang secara singkat dan jelas...')
                                            ->helperText('Maksimal 300 karakter termasuk spasi.'),

                                        DatePicker::make('deadline')
                                            ->label('Deadline'),

                                        Select::make('status')
                                            ->label('Status')
                                            ->options([
                                                'open' => 'Open',
                                                'closed' => 'Closed',
                                                'archived' => 'Archived',
                                            ])
                                            ->default('open')
                                            ->required(),

                                        Toggle::make('featured')
                                            ->label('Tampilkan sebagai unggulan')
                                            ->default(false),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'edulaw-admin-two-column-section']),

                Grid::make([
                    'default' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        Section::make('Pendaftaran')
                            ->description('Tambahkan tautan pendaftaran atau informasi aplikasi.')
                            ->schema([
                                TextInput::make('application_link')
                                    ->label('Link Pendaftaran')
                                    ->url()
                                    ->maxLength(255)
                                    ->placeholder('https://...'),
                            ]),

                        Section::make('Media')
                            ->description('Unggah poster untuk kartu Opportunities.')
                            ->schema([
                                FileUpload::make('poster')
                                    ->label('Poster')
                                    ->image()
                                    ->disk('public')
                                    ->directory('opportunities')
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->imagePreviewHeight('180')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(5120)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'edulaw-admin-section-pair']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('poster')
                    ->label('Poster')
                    ->disk('public')
                    ->square()
                    ->size(44)
                    ->width(64)
                    ->grow(false)
                    ->defaultImageUrl(asset('images/logo/icon-bg.png')),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(44)
                    ->wrap()
                    ->width('22rem')
                    ->description(fn (Opportunity $record): ?string => filled($record->excerpt)
                        ? Str::limit($record->excerpt, 82)
                        : null),

                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'scholarship' => 'success',
                        'internship' => 'info',
                        'volunteer' => 'warning',
                        'fellowship' => 'primary',
                        'call_for_paper' => 'gray',
                        'competition' => 'danger',
                        'open_collaboration' => 'primary',
                        default => 'gray',
                    })
                    ->wrap()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'scholarship' => 'Beasiswa',
                        'internship' => 'Magang',
                        'volunteer' => 'Volunteer',
                        'fellowship' => 'Fellowship',
                        'call_for_paper' => 'Call for Papers',
                        'competition' => 'Kompetisi',
                        'open_collaboration' => 'Kolaborasi Terbuka',
                        default => $state ? ucfirst($state) : '-',
                    }),

                TextColumn::make('deadline')
                    ->label('Deadline')
                    ->date('d M Y')
                    ->sortable()
                    ->grow(false),

                TextColumn::make('format')
                    ->label('Format')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('location')
                    ->label('Lokasi')
                    ->limit(36)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'open' => 'success',
                        'closed' => 'warning',
                        'archived' => 'gray',
                        default => 'gray',
                    })
                    ->grow(false)
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'open' => 'Open',
                        'closed' => 'Closed',
                        'archived' => 'Archived',
                        default => $state ? ucfirst($state) : '-',
                    }),

                IconColumn::make('featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
            ->defaultSort('deadline')
            ->searchPlaceholder('Cari opportunities...')
            ->paginationPageOptions([10, 25, 50])
            ->filters([
                SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'scholarship' => 'Beasiswa',
                        'internship' => 'Magang',
                        'volunteer' => 'Volunteer',
                        'fellowship' => 'Fellowship',
                        'call_for_paper' => 'Call for Papers',
                        'competition' => 'Kompetisi',
                        'open_collaboration' => 'Kolaborasi Terbuka',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Open',
                        'closed' => 'Closed',
                        'archived' => 'Archived',
                    ]),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOpportunities::route('/'),
            'create' => CreateOpportunity::route('/create'),
            'edit' => EditOpportunity::route('/{record}/edit'),
        ];
    }
}
