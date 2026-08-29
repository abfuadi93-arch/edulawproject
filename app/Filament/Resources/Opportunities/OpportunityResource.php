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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OpportunityResource extends Resource
{
    protected static ?string $model = Opportunity::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Publikasi & Program';

    protected static ?string $navigationLabel = 'Peluang';

    protected static ?string $modelLabel = 'Peluang';

    protected static ?string $pluralModelLabel = 'Peluang';

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
                                    ->description('Cukup isi identitas singkat dan tautan resmi. Hindari menyalin ulang deskripsi promosi panjang milik penyelenggara.')
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

                                                TextInput::make('organizer')
                                                    ->label('Penyelenggara')
                                                    ->maxLength(255),

                                                TextInput::make('application_link')
                                                    ->label('URL Informasi Resmi')
                                                    ->url()
                                                    ->maxLength(255)
                                                    ->placeholder('https://...')
                                                    ->helperText('Card publik akan langsung membuka URL resmi ini.'),
                                            ])
                                            ->columnSpanFull(),

                                        Textarea::make('excerpt')
                                            ->label('Ringkasan Kurasi')
                                            ->rows(3)
                                            ->maxLength(500)
                                            ->helperText('Opsional dan ringkas. Jelaskan relevansi peluang dalam 1–2 kalimat; detail lengkap tetap dibaca di situs resmi.')
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
                                                static::listRepeater('eligibility', 'Target Peserta', 'Tambah Target Peserta'),
                                            ])
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1)
                                    ->collapsible()
                                    ->collapsed(),

                                Section::make('SEO & Pratinjau')
                                    ->icon('heroicon-o-magnifying-glass')
                                    ->description('Opsional. Jika kosong, sistem memakai judul, ringkasan kurasi, dan poster pertama.')
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
                                            ->placeholder('Otomatis dari ringkasan kurasi')
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
                                            ->helperText('Kosongkan untuk memakai poster pertama.'),
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

                                Section::make('Poster')
                                    ->icon('heroicon-o-photo')
                                    ->description('Unggah poster utama terlebih dahulu, lalu tambahkan poster lain bila diperlukan.')
                                    ->schema([
                                        FileUpload::make('poster')
                                            ->label('Poster Slide 1')
                                            ->image()
                                            ->live()
                                            ->disk('public')
                                            ->directory('opportunities')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->imagePreviewHeight('180')
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->maxSize(4096)
                                            ->required(fn ($get): bool => count($get('additional_posters') ?? []) > 0)
                                            ->helperText('Poster ini selalu menjadi slide pertama dan gambar utama.'),

                                        Repeater::make('additional_posters')
                                            ->label('Poster Tambahan')
                                            ->schema([
                                                FileUpload::make('image')
                                                    ->label('Poster')
                                                    ->image()
                                                    ->disk('public')
                                                    ->directory('opportunities')
                                                    ->visibility('public')
                                                    ->imageEditor()
                                                    ->imagePreviewHeight('150')
                                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                                    ->maxSize(4096)
                                                    ->required(),
                                            ])
                                            ->defaultItems(0)
                                            ->maxItems(9)
                                            ->addable(fn ($get): bool => filled($get('poster')))
                                            ->addActionLabel('Tambah Poster')
                                            ->itemLabel(fn (array $state): string => filled($state['image'] ?? null) ? 'Poster Tambahan' : 'Poster Baru')
                                            ->reorderable()
                                            ->collapsible()
                                            ->helperText(fn ($get): string => filled($get('poster'))
                                                ? 'Tambahkan maksimal 9 poster. Urutkan untuk menentukan slide 2 dan seterusnya.'
                                                : 'Unggah Poster Slide 1 agar tombol Tambah Poster tersedia.'),
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
        $data['excerpt'] = filled($data['excerpt'] ?? null)
            ? static::excerptFromDescription((string) $data['excerpt'])
            : static::excerptFromDescription($data['description'] ?? null);

        $posterState = array_key_exists('additional_posters', $data)
            ? collect([$data['poster'] ?? null])
                ->merge(collect($data['additional_posters'] ?? [])->pluck('image'))
                ->all()
            : (array_key_exists('posters', $data) ? $data['posters'] : ($data['poster'] ?? null));
        $posters = collect(is_array($posterState) ? $posterState : [$posterState])
            ->filter(fn (mixed $poster): bool => is_string($poster) && filled(trim($poster)))
            ->map(fn (string $poster): string => trim($poster))
            ->unique()
            ->values();

        $data['posters'] = $posters->all();
        $data['poster'] = $posters->first();
        unset($data['additional_posters'], $data['primary_poster_index']);

        if (blank($data['seo_title'] ?? null) && filled($data['title'] ?? null)) {
            $data['seo_title'] = (string) $data['title'];
        }

        if (blank($data['seo_description'] ?? null) && filled($data['excerpt'] ?? null)) {
            $data['seo_description'] = static::excerptFromDescription((string) $data['excerpt'], 180);
        }

        if (blank($data['og_image'] ?? null) && filled($data['poster'])) {
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
            'open' => 'Dibuka',
            'closed' => 'Ditutup',
            'archived' => 'Diarsipkan',
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
            'career' => 'Karier',
            'open_collaboration' => 'Kolaborasi Terbuka',
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ViewColumn::make('opportunity')
                    ->label('Peluang')
                    ->view('filament.tables.columns.resource-content', fn (Opportunity $record): array => [
                        'imageUrl' => $record->poster_url,
                        'isPortrait' => true,
                        'title' => $record->title,
                        'metadata' => [$record->format, $record->location],
                    ])
                    ->searchable(['title', 'type', 'location', 'excerpt'])
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('title', $direction))
                    ->url(fn (Opportunity $record): ?string => static::canEdit($record) ? static::getUrl('edit', ['record' => $record]) : null)
                    ->extraHeaderAttributes(['class' => 'edulaw-resource-primary-header'])
                    ->extraCellAttributes(['class' => 'edulaw-resource-primary-cell']),

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
                        'career' => 'info',
                        'open_collaboration' => 'primary',
                        default => 'gray',
                    })
                    ->limit(24)
                    ->tooltip(fn (?string $state): ?string => filled($state) ? (static::typeOptions()[$state] ?? Str::headline($state)) : null)
                    ->formatStateUsing(fn (?string $state): string => static::typeOptions()[$state] ?? ($state ? Str::headline(str_replace('_', ' ', $state)) : '—'))
                    ->visibleFrom('lg')
                    ->extraHeaderAttributes(['class' => 'edulaw-resource-classification-header'])
                    ->extraCellAttributes(['class' => 'edulaw-resource-classification-cell']),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => static::statusColor($state))
                    ->formatStateUsing(fn (?string $state): string => static::statusLabel($state))
                    ->extraHeaderAttributes(['class' => 'edulaw-resource-status-header'])
                    ->extraCellAttributes(['class' => 'edulaw-resource-status-cell']),

                TextColumn::make('deadline')
                    ->label('Tenggat')
                    ->formatStateUsing(fn ($state): string => $state?->locale('id')->translatedFormat('d M Y') ?? '—')
                    ->description(fn (Opportunity $record): ?string => static::deadlineRelativeLabel($record->deadline))
                    ->color(fn ($state): string => static::deadlineColor($state))
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable()
                    ->visibleFrom('xl')
                    ->extraHeaderAttributes(['class' => 'edulaw-resource-time-header'])
                    ->extraCellAttributes(['class' => 'edulaw-resource-time-cell']),

                TextColumn::make('format')
                    ->label('Format')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('application_link')
                    ->label('Tautan Aplikasi')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->formatStateUsing(fn ($state): string => $state->locale('id')->diffForHumans())
                    ->tooltip(fn (Opportunity $record): string => $record->updated_at->locale('id')->translatedFormat('d M Y, H:i'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('deadline')
            ->searchPlaceholder('Cari peluang, jenis, atau lokasi...')
            ->searchDebounce('500ms')
            ->paginationPageOptions([10, 25, 50])
            ->filters([
                SelectFilter::make('type')
                    ->label('Jenis')
                    ->options(static::typeOptions()),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options(static::statusOptions()),

                SelectFilter::make('format')
                    ->label('Format')
                    ->options(fn (): array => Opportunity::query()
                        ->whereNotNull('format')
                        ->where('format', '!=', '')
                        ->distinct()
                        ->orderBy('format')
                        ->pluck('format', 'format')
                        ->all()),

                TernaryFilter::make('featured')
                    ->label('Featured')
                    ->placeholder('Semua')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak'),

                Filter::make('deadline')
                    ->label('Rentang Tenggat')
                    ->schema([
                        DatePicker::make('from')->label('Dari tanggal')->native(false),
                        DatePicker::make('until')->label('Sampai tanggal')->native(false),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('deadline', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('deadline', '<=', $date)))
                    ->indicateUsing(fn (array $data): array => static::dateRangeIndicators($data, 'Tenggat')),

                TernaryFilter::make('without_deadline')
                    ->label('Tanpa Tenggat')
                    ->placeholder('Semua')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNull('deadline'),
                        false: fn (Builder $query): Builder => $query->whereNotNull('deadline'),
                    ),
            ])
            ->recordActions([
                Actions\ActionGroup::make([
                    Actions\EditAction::make()->label('Edit'),
                    Actions\ReplicateAction::make()
                        ->label('Duplikasi')
                        ->visible(fn (): bool => static::canCreate())
                        ->mutateRecordDataUsing(fn (array $data, Opportunity $record): array => [
                            ...$data,
                            'title' => Str::limit($record->title.' (Salinan)', 255, ''),
                            'slug' => static::uniqueDuplicateSlug($record),
                            'status' => 'open',
                            'featured' => false,
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                        ]),
                    Actions\Action::make('archive')
                        ->label('Arsipkan')
                        ->icon('heroicon-o-archive-box')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn (Opportunity $record): bool => $record->status !== 'archived'
                            && (Auth::user()?->can('archive opportunities') ?? false))
                        ->action(fn (Opportunity $record) => $record->update(['status' => 'archived', 'updated_by' => Auth::id()])),
                    Actions\DeleteAction::make()->label('Hapus')->requiresConfirmation(),
                ])->label('Aksi lainnya')->icon('heroicon-o-ellipsis-vertical')->tooltip('Aksi lainnya')->color('gray'),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\BulkAction::make('open')
                        ->label('Buka')
                        ->icon('heroicon-o-lock-open')
                        ->color('success')
                        ->requiresConfirmation()
                        ->authorizeIndividualRecords('update')
                        ->visible(fn (): bool => Auth::user()?->can('publish opportunities') ?? false)
                        ->action(fn ($records) => $records->each->update(['status' => 'open', 'updated_by' => Auth::id()])),
                    Actions\BulkAction::make('close')
                        ->label('Tutup')
                        ->icon('heroicon-o-lock-closed')
                        ->requiresConfirmation()
                        ->authorizeIndividualRecords('update')
                        ->visible(fn (): bool => Auth::user()?->can('close opportunities') ?? false)
                        ->action(fn ($records) => $records->each->update(['status' => 'closed', 'updated_by' => Auth::id()])),
                    Actions\BulkAction::make('archive')
                        ->label('Arsipkan')
                        ->icon('heroicon-o-archive-box')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->authorizeIndividualRecords('update')
                        ->visible(fn (): bool => Auth::user()?->can('archive opportunities') ?? false)
                        ->action(fn ($records) => $records->each->update(['status' => 'archived', 'updated_by' => Auth::id()])),
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function deadlineRelativeLabel(?Carbon $deadline): ?string
    {
        if (! $deadline) {
            return null;
        }

        $days = (int) today()->diffInDays($deadline->copy()->startOfDay(), false);

        return match (true) {
            $days === 0 => 'Berakhir hari ini',
            $days > 0 => $days.' hari lagi',
            default => 'Lewat '.abs($days).' hari',
        };
    }

    public static function deadlineColor(?Carbon $deadline): string
    {
        if (! $deadline) {
            return 'gray';
        }

        $days = (int) today()->diffInDays($deadline->copy()->startOfDay(), false);

        return $days >= 0 && $days <= 7 ? 'warning' : 'gray';
    }

    private static function dateRangeIndicators(array $data, string $label): array
    {
        return collect([
            ($data['from'] ?? null) ? Indicator::make($label.' mulai '.Carbon::parse($data['from'])->locale('id')->translatedFormat('d M Y'))->removeField('from') : null,
            ($data['until'] ?? null) ? Indicator::make($label.' sampai '.Carbon::parse($data['until'])->locale('id')->translatedFormat('d M Y'))->removeField('until') : null,
        ])->filter()->all();
    }

    public static function uniqueDuplicateSlug(Opportunity $record): string
    {
        $base = Str::limit(Str::slug($record->slug ?: $record->title).'-salinan', 240, '');
        $slug = $base;
        $suffix = 2;

        while (Opportunity::query()->where('slug', $slug)->exists()) {
            $slug = Str::limit($base, 240 - strlen((string) $suffix), '').'-'.$suffix++;
        }

        return $slug;
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
