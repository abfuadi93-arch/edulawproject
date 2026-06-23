<?php

namespace App\Filament\Resources\Opportunities;

use App\Filament\Resources\Opportunities\Pages\CreateOpportunity;
use App\Filament\Resources\Opportunities\Pages\EditOpportunity;
use App\Filament\Resources\Opportunities\Pages\ListOpportunities;
use App\Models\Opportunity;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class OpportunityResource extends Resource
{
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
                Section::make('Informasi Opportunity')
                    ->description('Kelola peluang magang, volunteer, fellowship, call for papers, kompetisi, beasiswa, dan kolaborasi terbuka.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, $state) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

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
                            ->required(),

                        Textarea::make('excerpt')
                            ->label('Ringkasan')
                            ->rows(4)
                            ->maxLength(600)
                            ->columnSpanFull(),

                        RichEditor::make('description')
                            ->label('Deskripsi Lengkap')
                            ->columnSpanFull(),

                        FileUpload::make('poster')
                            ->label('Poster')
                            ->image()
                            ->directory('opportunities')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Detail Peluang')
                    ->schema([
                        DatePicker::make('deadline')
                            ->label('Deadline'),

                        TextInput::make('application_link')
                            ->label('Link Pendaftaran')
                            ->url()
                            ->maxLength(255),

                        TextInput::make('format')
                            ->label('Format')
                            ->placeholder('Online / Offline / Hybrid'),

                        TextInput::make('location')
                            ->label('Lokasi')
                            ->maxLength(255),
                    ])
                    ->columns(1),

                Section::make('Syarat dan Manfaat')
                    ->schema([
                        Repeater::make('eligibility')
                            ->label('Eligibility / Syarat')
                            ->schema([
                                TextInput::make('item')
                                    ->label('Syarat')
                                    ->required(),
                            ])
                            ->defaultItems(3)
                            ->columnSpanFull(),

                        Repeater::make('benefits')
                            ->label('Benefits / Manfaat')
                            ->schema([
                                TextInput::make('item')
                                    ->label('Manfaat')
                                    ->required(),
                            ])
                            ->defaultItems(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Status dan SEO')
                    ->schema([
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
                            ->label('Featured')
                            ->default(false),

                        TextInput::make('seo_title')
                            ->label('SEO Title')
                            ->maxLength(255),

                        Textarea::make('seo_description')
                            ->label('SEO Description')
                            ->rows(3)
                            ->maxLength(500),

                        FileUpload::make('og_image')
                            ->label('OG Image')
                            ->image()
                            ->directory('seo/opportunities')
                            ->imageEditor(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('poster')
                    ->label('Poster')
                    ->square(),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(45)
                    ->description(fn (Opportunity $record): ?string => $record->excerpt),

                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
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
                    ->sortable(),

                TextColumn::make('format')
                    ->label('Format')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'open' => 'Open',
                        'closed' => 'Closed',
                        'archived' => 'Archived',
                        default => $state ? ucfirst($state) : '-',
                    }),

                IconColumn::make('featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
