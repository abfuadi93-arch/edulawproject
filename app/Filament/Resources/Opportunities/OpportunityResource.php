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
    protected static ?string $model = Opportunity::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Opportunities';

    protected static ?string $modelLabel = 'Opportunity';

    protected static ?string $pluralModelLabel = 'Opportunities';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'xl' => 12,
                ])
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make('Konten Opportunity')
                                    ->icon('heroicon-o-sparkles')
                                    ->description('Isi peluang utama: judul, jenis, deskripsi, poster, tautan pendaftaran, format, dan lokasi.')
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Judul')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($get, $set, ?string $old, ?string $state): void {
                                                $currentSlug = (string) ($get('slug') ?? '');
                                                $oldSlug = Str::slug((string) $old);

                                                if (filled($currentSlug) && $currentSlug !== $oldSlug) {
                                                    return;
                                                }

                                                $set('slug', Str::slug((string) $state));
                                            })
                                            ->columnSpanFull(),

                                        Grid::make([
                                            'default' => 1,
                                            'lg' => 2,
                                        ])
                                            ->schema([
                                                Select::make('type')
                                                    ->label('Jenis Peluang')
                                                    ->options(static::typeOptions())
                                                    ->default('open_collaboration')
                                                    ->searchable()
                                                    ->required(),

                                                TextInput::make('application_link')
                                                    ->label('Link Pendaftaran')
                                                    ->url()
                                                    ->maxLength(255)
                                                    ->placeholder('https://...'),
                                            ])
                                            ->columnSpanFull(),

                                        RichEditor::make('description')
                                            ->label('Deskripsi')
                                            ->columnSpanFull(),

                                        FileUpload::make('poster')
                                            ->label('Poster')
                                            ->image()
                                            ->disk('public')
                                            ->directory('opportunities')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->imagePreviewHeight('180')
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->maxSize(4096)
                                            ->columnSpanFull(),

                                        Grid::make([
                                            'default' => 1,
                                            'lg' => 2,
                                        ])
                                            ->schema([
                                                TextInput::make('format')
                                                    ->label('Format')
                                                    ->maxLength(255)
                                                    ->placeholder('Online / Offline / Hybrid'),

                                                TextInput::make('location')
                                                    ->label('Lokasi')
                                                    ->maxLength(255)
                                                    ->placeholder('Online / Jakarta / Hybrid'),
                                            ])
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Pengaturan Lanjutan')
                                    ->icon('heroicon-o-cog-6-tooth')
                                    ->description('Opsional. Slug dan detail tambahan hanya perlu dibuka bila ingin disesuaikan.')
                                    ->schema([
                                        TextInput::make('slug')
                                            ->label('Slug')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->helperText('Otomatis dari judul, boleh diedit sebelum dipublikasikan.')
                                            ->columnSpanFull(),

                                        Grid::make([
                                            'default' => 1,
                                            'lg' => 2,
                                        ])
                                            ->schema([
                                                static::listRepeater('eligibility', 'Eligibility', 'Tambah Eligibility'),
                                                static::listRepeater('benefits', 'Benefits', 'Tambah Benefit'),
                                            ])
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1)
                                    ->collapsible()
                                    ->collapsed(),

                                Section::make('SEO & Pratinjau')
                                    ->icon('heroicon-o-magnifying-glass')
                                    ->description('Opsional. Jika kosong, sistem memakai judul, deskripsi, dan poster.')
                                    ->schema([
                                        TextInput::make('seo_title')
                                            ->label('SEO Title')
                                            ->maxLength(300)
                                            ->placeholder(fn ($get): string => $get('title') ?: 'Otomatis dari judul')
                                            ->helperText('Target 45–65 karakter. Gunakan judul natural; nama situs ditambahkan otomatis.'),

                                        Textarea::make('seo_description')
                                            ->label('SEO Description')
                                            ->rows(3)
                                            ->maxLength(180)
                                            ->placeholder('Otomatis dari deskripsi')
                                            ->helperText('Target 120–160 karakter. Jelaskan manfaat dan topik utama secara alami.'),

                                        FileUpload::make('og_image')
                                            ->label('Gambar OG')
                                            ->image()
                                            ->disk('public')
                                            ->directory('seo/og-images')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->maxSize(4096)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->helperText('Kosongkan untuk memakai poster.'),
                                    ])
                                    ->columns(1)
                                    ->collapsible()
                                    ->collapsed(),
                            ])
                            ->columnSpan(['xl' => 8])
                            ->extraAttributes(['class' => 'edulaw-admin-main-column']),

                        Group::make()
                            ->schema([
                                Section::make('Status Peluang')
                                    ->icon('heroicon-o-paper-airplane')
                                    ->schema([
                                        Select::make('status')
                                            ->label('Status')
                                            ->options(static::statusOptions())
                                            ->default('open')
                                            ->required(),

                                        DatePicker::make('deadline')
                                            ->label('Deadline'),

                                        Toggle::make('featured')
                                            ->label('Tampilkan sebagai unggulan')
                                            ->default(false),
                                    ])
                                    ->columns(1),
                            ])
                            ->columnSpan(['xl' => 4])
                            ->extraAttributes(['class' => 'edulaw-admin-side-column edulaw-admin-sticky-column']),
                    ])
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'edulaw-admin-edit-shell']),
            ]);
    }

    public static function prepareFormDataForPersistence(array $data): array
    {
        if (blank($data['slug'] ?? null) && filled($data['title'] ?? null)) {
            $data['slug'] = Str::slug((string) $data['title']);
        }

        if (filled($data['slug'] ?? null)) {
            $data['slug'] = Str::slug((string) $data['slug']);
        }

        $data['status'] = static::normalizeStatusForForm($data['status'] ?? null);
        $data['excerpt'] = static::excerptFromDescription($data['description'] ?? null);

        if (blank($data['seo_title'] ?? null) && filled($data['title'] ?? null)) {
            $data['seo_title'] = (string) $data['title'];
        }

        if (blank($data['seo_description'] ?? null) && filled($data['excerpt'] ?? null)) {
            $data['seo_description'] = static::excerptFromDescription((string) $data['excerpt'], 180);
        }

        if (blank($data['og_image'] ?? null) && filled($data['poster'] ?? null)) {
            $data['og_image'] = $data['poster'];
        }

        return $data;
    }

    public static function excerptFromDescription(?string $html, int $limit = 220): ?string
    {
        $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $html), ENT_QUOTES, 'UTF-8')) ?? '');

        if ($text === '') {
            return null;
        }

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        $excerpt = rtrim(mb_substr($text, 0, max(0, $limit - 3)));
        $lastSpace = mb_strrpos($excerpt, ' ');

        if ($lastSpace !== false && $lastSpace >= 120) {
            $excerpt = rtrim(mb_substr($excerpt, 0, $lastSpace));
        }

        return $excerpt.'...';
    }

    public static function statusOptions(): array
    {
        return [
            'open' => 'Open',
            'closed' => 'Closed',
            'archived' => 'Archived',
        ];
    }

    public static function statusLabel(?string $status): string
    {
        return static::statusOptions()[static::normalizeStatusForDisplay($status)] ?? 'Archived';
    }

    public static function statusColor(?string $status): string
    {
        return match (static::normalizeStatusForDisplay($status)) {
            'open' => 'success',
            'closed' => 'warning',
            default => 'gray',
        };
    }

    public static function normalizeStatusForDisplay(?string $status): string
    {
        return match ($status) {
            'open', 'closed' => $status,
            default => 'archived',
        };
    }

    public static function normalizeStatusForForm(?string $status): string
    {
        return match ($status) {
            'closed', 'archived' => $status,
            default => 'open',
        };
    }

    public static function typeOptions(): array
    {
        return [
            'scholarship' => 'Beasiswa',
            'internship' => 'Magang',
            'volunteer' => 'Volunteer',
            'fellowship' => 'Fellowship',
            'call_for_paper' => 'Call for Papers',
            'competition' => 'Kompetisi',
            'open_collaboration' => 'Kolaborasi Terbuka',
        ];
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
                    ->formatStateUsing(fn (?string $state): string => static::typeOptions()[$state] ?? ($state ? Str::headline(str_replace('_', ' ', $state)) : '-')),

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
                    ->label('Status Peluang')
                    ->badge()
                    ->color(fn (?string $state): string => static::statusColor($state))
                    ->grow(false)
                    ->formatStateUsing(fn (?string $state): string => static::statusLabel($state)),

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
                    ->options(static::typeOptions()),

                SelectFilter::make('status')
                    ->label('Status Peluang')
                    ->options(static::statusOptions()),
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

    protected static function listRepeater(string $field, string $label, string $addActionLabel): Repeater
    {
        return Repeater::make($field)
            ->label($label)
            ->schema([
                TextInput::make('item')
                    ->label('Item')
                    ->maxLength(255)
                    ->required(),
            ])
            ->columns(1)
            ->itemLabel(fn (array $state): ?string => $state['item'] ?? $label)
            ->addActionLabel($addActionLabel)
            ->reorderable()
            ->collapsible()
            ->afterStateHydrated(static function (Repeater $component): void {
                $state = $component->getState();

                if (blank($state)) {
                    $component->state([]);

                    return;
                }

                if (is_string($state)) {
                    $state = preg_split('/\r\n|\r|\n/', $state) ?: [];
                }

                if (! is_array($state)) {
                    $component->state([]);

                    return;
                }

                $component->state(
                    collect($state)
                        ->map(fn ($item): ?string => is_array($item)
                            ? ($item['item'] ?? $item['text'] ?? null)
                            : $item)
                        ->map(fn ($item): ?string => is_string($item) ? trim($item) : null)
                        ->filter(fn (?string $item): bool => filled($item))
                        ->map(fn (string $item): array => ['item' => $item])
                        ->values()
                        ->all()
                );
            })
            ->dehydrateStateUsing(fn (?array $state): array => collect($state ?? [])
                ->map(fn ($item): ?string => is_array($item) ? ($item['item'] ?? null) : $item)
                ->map(fn ($item): ?string => is_string($item) ? trim($item) : null)
                ->filter(fn (?string $item): bool => filled($item))
                ->values()
                ->all());
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
