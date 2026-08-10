<?php

namespace App\Filament\Resources\Insights;

use App\Enums\InsightStatus;
use App\Filament\Resources\Editorial\EditorialResource;
use App\Filament\Resources\Insights\InsightResource\Pages;
use App\Filament\RichEditor\FootnoteRichContentPlugin;
use App\Models\Insight;
use App\Services\InsightEditorialWorkflowService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InsightResource extends Resource
{
    protected static ?string $model = Insight::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Editorial';

    protected static ?string $navigationLabel = 'Semua Insight';

    protected static ?string $modelLabel = 'Insight';

    protected static ?string $pluralModelLabel = 'Semua Insight';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'xl' => 12])
                ->schema([
                    Group::make()->schema([
                        Section::make('Konten')
                            ->description('Tulis dan lengkapi naskah utama.')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Judul')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($get, $set, ?string $old, ?string $state): void {
                                        $currentSlug = (string) ($get('slug') ?? '');

                                        if (filled($currentSlug) && $currentSlug !== Str::slug((string) $old)) {
                                            return;
                                        }

                                        $set('slug', Str::slug((string) $state));
                                    })
                                    ->columnSpanFull(),
                                Grid::make(['default' => 1, 'md' => 2])->schema([
                                    Select::make('insight_category_id')
                                        ->label('Kategori')
                                        ->relationship('category', 'name')
                                        ->searchable()
                                        ->preload(),
                                    Select::make('authors')
                                        ->label('Penulis')
                                        ->relationship('authors', 'name')
                                        ->multiple()
                                        ->searchable()
                                        ->preload(),
                                ])->columnSpanFull(),
                                FileUpload::make('cover_image')
                                    ->label('Cover')
                                    ->image()
                                    ->disk('public')
                                    ->directory('insights')
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(4096)
                                    ->columnSpanFull(),
                                RichEditor::make('content')
                                    ->label('Isi Artikel')
                                    ->plugins([new FootnoteRichContentPlugin])
                                    ->toolbarButtons([
                                        ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link', 'footnote'],
                                        ['h2', 'h3'],
                                        ['alignStart', 'alignCenter', 'alignEnd'],
                                        ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                                        ['table', 'attachFiles'],
                                        ['undo', 'redo'],
                                    ])
                                    ->disableToolbarButtons(['h1'])
                                    ->rule(static function (): \Closure {
                                        return static function (string $attribute, mixed $value, \Closure $fail): void {
                                            if (static::contentContainsH1(is_string($value) ? $value : null)) {
                                                $fail('Isi artikel tidak boleh menggunakan H1. Gunakan H2 untuk bagian utama dan H3 untuk subbagian.');
                                            }
                                        };
                                    })
                                    ->columnSpanFull(),
                                Repeater::make('footnotes')
                                    ->label('Daftar Catatan Kaki')
                                    ->helperText('Catatan baru dibuat melalui tombol Catatan Kaki pada toolbar. Simpan artikel agar catatan baru muncul di daftar ini.')
                                    ->relationship('footnotes')
                                    ->schema([
                                        Textarea::make('content')
                                            ->label('Isi Catatan Kaki')
                                            ->rows(4)
                                            ->required()
                                            ->maxLength(10000)
                                            ->columnSpanFull(),
                                    ])
                                    ->itemLabel(fn (array $state): string => Str::limit((string) ($state['content'] ?? 'Catatan kaki'), 80))
                                    ->itemNumbers()
                                    ->addable(false)
                                    ->deletable()
                                    ->reorderable(false)
                                    ->orderColumn('sort_order')
                                    ->collapsible()
                                    ->columnSpanFull(),
                            ]),
                        Section::make('Metadata')
                            ->description('Ringkasan, topik, alamat artikel, dan metadata pencarian.')
                            ->schema([
                                Textarea::make('excerpt')->label('Excerpt')->rows(4)->maxLength(500)->columnSpanFull(),
                                Select::make('tags')->label('Tag')->relationship('tags', 'name')->multiple()->searchable()->preload()->columnSpanFull(),
                                TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true)->maxLength(255)->columnSpanFull(),
                                TextInput::make('seo_title')->label('SEO Title')->maxLength(300)->columnSpanFull(),
                                Textarea::make('seo_description')->label('SEO Description')->rows(3)->maxLength(180)->columnSpanFull(),
                                FileUpload::make('og_image')
                                    ->label('OG Image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('seo/og-images')
                                    ->visibility('public')
                                    ->maxSize(4096)
                                    ->columnSpanFull(),
                            ])
                            ->collapsible(),
                    ])->columnSpan(['xl' => 8]),
                    Group::make()->schema([
                        Section::make('Editorial')
                            ->description('Informasi status—bukan konfigurasi.')
                            ->schema([
                                Placeholder::make('status_info')
                                    ->label('Status')
                                    ->content(fn (?Insight $record): string => $record?->status->canonical()->label() ?? 'Draft'),
                                Placeholder::make('editor_info')
                                    ->label('Editor')
                                    ->content(fn (?Insight $record): string => $record?->assignedEditor?->name ?? 'Belum ditugaskan'),
                                Placeholder::make('submitted_info')
                                    ->label('Dikirim')
                                    ->content(fn (?Insight $record): string => $record?->submitted_at?->locale('id')->translatedFormat('d M Y, H:i') ?? 'Belum dikirim'),
                                Placeholder::make('published_info')
                                    ->label('Terbit')
                                    ->content(fn (?Insight $record): string => $record?->published_at?->locale('id')->translatedFormat('d M Y, H:i') ?? 'Belum terbit'),
                                Placeholder::make('editor_notes_info')
                                    ->label('Catatan Editor')
                                    ->content(fn (?Insight $record): string => $record?->editor_notes ?: 'Belum ada catatan.')
                                    ->visible(fn (?Insight $record): bool => filled($record?->editor_notes)),
                                Placeholder::make('reading_time_preview')
                                    ->label('Estimasi baca')
                                    ->content(fn ($get): string => static::estimateReadingTime($get('content')).' menit'),
                            ]),
                        Section::make('Penempatan')
                            ->schema([
                                Toggle::make('featured')->label('Artikel Unggulan'),
                                Toggle::make('editor_pick')->label('Pilihan Editor'),
                                TextInput::make('sort_order')->label('Urutan')->numeric()->minValue(0)->default(0),
                            ])
                            ->visible(fn (): bool => static::canManageEditorialWorkflow())
                            ->collapsible(),
                    ])->columnSpan(['xl' => 4]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function prepareFormDataForPersistence(array $data): array
    {
        if (static::contentContainsH1($data['content'] ?? null)) {
            throw ValidationException::withMessages(['content' => 'Isi artikel tidak boleh menggunakan H1.']);
        }

        $data['slug'] = filled($data['slug'] ?? null) ? Str::slug((string) $data['slug']) : Str::slug((string) ($data['title'] ?? ''));
        $data['excerpt'] = filled($data['excerpt'] ?? null) ? trim((string) $data['excerpt']) : static::excerptFromContent($data['content'] ?? null);
        $data['seo_title'] = filled($data['seo_title'] ?? null) ? trim((string) $data['seo_title']) : ($data['title'] ?? null);
        $data['seo_description'] = filled($data['seo_description'] ?? null) ? trim((string) $data['seo_description']) : static::excerptFromContent($data['content'] ?? null, 180);
        $data['og_image'] = filled($data['og_image'] ?? null) ? $data['og_image'] : ($data['cover_image'] ?? null);
        $data['reading_time'] = static::estimateReadingTime($data['content'] ?? null);

        if (($data['status'] ?? null) === InsightStatus::Published->value) {
            $errors = collect([
                'cover_image' => blank($data['cover_image'] ?? null) ? 'Cover wajib diisi sebelum artikel diterbitkan.' : null,
                'excerpt' => blank($data['excerpt'] ?? null) ? 'Excerpt wajib diisi sebelum artikel diterbitkan.' : null,
            ])->filter()->all();

            if ($errors !== []) {
                throw ValidationException::withMessages($errors);
            }
        }

        return $data;
    }

    public static function contentContainsH1(?string $html): bool
    {
        return preg_match('/<h1\b[^>]*>/i', (string) $html) === 1;
    }

    public static function publishReadinessIssues(Insight $insight): array
    {
        return collect([
            blank($insight->cover_image) ? 'cover' : null,
            blank($insight->excerpt) ? 'excerpt' : null,
            ! $insight->authors()->exists() ? 'penulis' : null,
            static::contentContainsH1($insight->content) ? 'hapus H1 dari isi artikel' : null,
        ])->filter()->values()->all();
    }

    public static function isPublishReady(Insight $insight): bool
    {
        return static::publishReadinessIssues($insight) === [];
    }

    public static function excerptFromContent(?string $html, int $limit = 220): ?string
    {
        $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $html), ENT_QUOTES, 'UTF-8')) ?? '');

        if ($text === '') {
            return null;
        }

        return Str::limit($text, $limit, '...');
    }

    public static function estimateReadingTime(?string $html): int
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $html)) ?? '');
        preg_match_all('/[\p{L}\p{N}]+/u', $text, $matches);

        return max(1, (int) ceil(count($matches[0] ?? []) / 200));
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['authors:id,name', 'category:id,name', 'assignedEditor:id,name']);
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasAnyRole(['super_admin', 'Super Admin', 'SuperAdmin'])) {
            return $query;
        }

        if (Gate::forUser($user)->allows('view_assigned_editorial_insights')) {
            return $query->where('assigned_editor_id', $user->id);
        }

        return $query->where('created_by', $user->id);
    }

    public static function canManageEditorialWorkflow(): bool
    {
        $user = Auth::user();

        return filled($user) && $user->hasAnyRole(['super_admin', 'Super Admin', 'SuperAdmin']);
    }

    public static function statusOptions(): array
    {
        return InsightStatus::options();
    }

    public static function normalizeStatusForDisplay(InsightStatus|string|null $status): string
    {
        $status = $status instanceof InsightStatus ? $status : InsightStatus::tryFrom((string) $status);

        return ($status ?? InsightStatus::Draft)->canonical()->value;
    }

    public static function statusLabel(InsightStatus|string|null $status): string
    {
        $enum = $status instanceof InsightStatus ? $status : InsightStatus::tryFrom((string) $status);

        return ($enum ?? InsightStatus::Draft)->canonical()->label();
    }

    public static function statusColor(InsightStatus|string|null $status): string
    {
        $enum = $status instanceof InsightStatus ? $status : InsightStatus::tryFrom((string) $status);

        return ($enum ?? InsightStatus::Draft)->canonical()->color();
    }

    public static function canSubmitRecord(Insight $record): bool
    {
        return Auth::user()?->can('submit', $record) ?? false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->extraAttributes(['class' => 'edulaw-insight-table'])
            ->columns([
                ViewColumn::make('article')
                    ->label('Artikel')
                    ->view('filament.tables.columns.insight-article')
                    ->searchable(['title', 'authors.name'])
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('title', $direction))
                    ->extraHeaderAttributes(['class' => 'edulaw-insight-article-header'])
                    ->extraCellAttributes(['class' => 'edulaw-insight-article-cell'])
                    ->grow(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => static::statusLabel($state))
                    ->color(fn ($state): string => static::statusColor($state))
                    ->extraHeaderAttributes(['class' => 'edulaw-insight-status-header'])
                    ->extraCellAttributes(['class' => 'edulaw-insight-status-cell']),
                TextColumn::make('assignedEditor.name')
                    ->label('Editor')
                    ->placeholder('Belum ditugaskan')
                    ->limit(22)
                    ->tooltip(fn (Insight $record): ?string => $record->assignedEditor?->name)
                    ->visible(fn (): bool => static::canManageEditorialWorkflow())
                    ->visibleFrom('md')
                    ->extraHeaderAttributes(['class' => 'edulaw-insight-editor-header'])
                    ->extraCellAttributes(['class' => 'edulaw-insight-editor-cell']),
                TextColumn::make('submitted_at')
                    ->label('Dikirim / diperbarui')
                    ->state(fn (Insight $record) => $record->submitted_at ?: $record->updated_at)
                    ->dateTime('d M Y')
                    ->sortable()
                    ->visibleFrom('lg')
                    ->extraHeaderAttributes(['class' => 'edulaw-insight-submitted-header'])
                    ->extraCellAttributes(['class' => 'edulaw-insight-submitted-cell']),
                TextColumn::make('updated_at')
                    ->label('Terakhir diperbarui')
                    ->since()
                    ->sortable()
                    ->visibleFrom('xl')
                    ->extraHeaderAttributes(['class' => 'edulaw-insight-updated-header'])
                    ->extraCellAttributes(['class' => 'edulaw-insight-updated-cell']),
            ])
            ->defaultSort('updated_at', 'desc')
            ->searchPlaceholder('Cari judul atau penulis...')
            ->paginationPageOptions([10, 25, 50])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(static::statusOptions()),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->label('Edit'),
                    EditorialResource::assignmentAction(),
                    EditorialResource::reassignmentAction(),
                    Action::make('workspace')
                        ->label('Buka')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(fn (Insight $record): string => EditorialResource::getUrl('workspace', ['record' => $record]))
                        ->visible(fn (Insight $record): bool => Auth::user()?->can('accessEditorialWorkspace', $record) ?? false),
                    Action::make('submit')
                        ->label('Kirim untuk Review')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->visible(fn (Insight $record): bool => static::canSubmitRecord($record))
                        ->action(function (Insight $record): void {
                            try {
                                app(InsightEditorialWorkflowService::class)->submit($record, Auth::user());
                                Notification::make()->success()->title('Naskah dikirim untuk review')->send();
                            } catch (ValidationException $exception) {
                                Notification::make()->danger()->title('Naskah belum lengkap')->body(collect($exception->errors())->flatten()->implode(' '))->persistent()->send();
                            }
                        }),
                    EditorialResource::historyAction(),
                    Action::make('view_public')
                        ->label('Lihat di website')
                        ->icon('heroicon-o-eye')
                        ->url(fn (Insight $record): string => route('insights.show', $record->slug))
                        ->openUrlInNewTab()
                        ->visible(fn (Insight $record): bool => $record->status->canonical() === InsightStatus::Published),
                    ReplicateAction::make()
                        ->label('Duplikasi')
                        ->visible(fn (): bool => static::canCreate())
                        ->mutateRecordDataUsing(fn (array $data, Insight $record): array => [
                            ...$data,
                            'title' => Str::limit($record->title.' (Salinan)', 255, ''),
                            'slug' => static::uniqueDuplicateSlug($record),
                            'status' => InsightStatus::Draft->value,
                            'published_at' => null,
                            'archived_at' => null,
                            'reviewed_by' => null,
                            'reviewed_at' => null,
                            'assigned_editor_id' => null,
                            'assigned_by' => null,
                            'assigned_at' => null,
                            'submitted_at' => null,
                            'editor_notes' => null,
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                        ])
                        ->after(function (Insight $record, Insight $replica): void {
                            $record->loadMissing(['authors', 'tags']);
                            $replica->authors()->sync($record->authors->mapWithKeys(fn ($author): array => [
                                $author->getKey() => ['author_order' => $author->pivot->author_order, 'role' => $author->pivot->role],
                            ])->all());
                            $replica->tags()->sync($record->tags->modelKeys());
                        }),
                    Action::make('archive')
                        ->label('Arsipkan')
                        ->icon('heroicon-o-archive-box')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn (Insight $record): bool => Auth::user()?->can('archive', $record) ?? false)
                        ->action(fn (Insight $record) => app(InsightEditorialWorkflowService::class)->archive($record, Auth::user())),
                    DeleteAction::make()->label('Hapus'),
                ])->label('Aksi')->icon('heroicon-o-ellipsis-vertical'),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function applyPublishReadyFilter(Builder $query): Builder
    {
        return $query
            ->whereNotNull('cover_image')
            ->where('cover_image', '!=', '')
            ->whereNotNull('excerpt')
            ->where('excerpt', '!=', '')
            ->whereHas('authors')
            ->where(fn (Builder $query): Builder => $query->whereNull('content')->orWhere('content', 'not like', '%<h1%'));
    }

    public static function applyNotPublishReadyFilter(Builder $query): Builder
    {
        return $query->where(fn (Builder $query): Builder => $query
            ->whereNull('cover_image')
            ->orWhere('cover_image', '')
            ->orWhereNull('excerpt')
            ->orWhere('excerpt', '')
            ->orWhereDoesntHave('authors')
            ->orWhere('content', 'like', '%<h1%'));
    }

    public static function uniqueDuplicateSlug(Insight $record): string
    {
        $base = Str::limit(Str::slug($record->slug ?: $record->title).'-salinan', 240, '');
        $slug = $base;
        $suffix = 2;

        while (Insight::query()->where('slug', $slug)->exists()) {
            $slug = Str::limit($base, 235, '').'-'.$suffix++;
        }

        return $slug;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInsights::route('/'),
            'create' => Pages\CreateInsight::route('/create'),
            'edit' => Pages\EditInsight::route('/{record}/edit'),
        ];
    }
}
