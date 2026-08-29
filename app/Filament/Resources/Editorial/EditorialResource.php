<?php

namespace App\Filament\Resources\Editorial;

use App\Enums\InsightStatus;
use App\Filament\Forms\Components\TinyMceEditor;
use App\Filament\Resources\Editorial\Pages\ListEditorialInsights;
use App\Filament\Resources\Editorial\Pages\ViewEditorialWorkspace;
use App\Models\Insight;
use App\Models\User;
use App\Services\InsightEditorialWorkflowService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Throwable;

class EditorialResource extends Resource
{
    protected static ?string $model = Insight::class;

    protected static ?string $slug = 'editorial';

    protected static string|\UnitEnum|null $navigationGroup = 'Editorial';

    protected static ?string $navigationLabel = 'Semua Insight';

    protected static ?string $modelLabel = 'Insight';

    protected static ?string $pluralModelLabel = 'Semua Insight';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->hasAnyRole(['super_admin', 'Super Admin', 'SuperAdmin'])
            || (Auth::user()?->canAccessAssignedEditorialInsights() ?? false);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'xl' => 12])
                ->schema([
                    Section::make('Informasi Editorial')
                        ->description('Ringkasan status dan penanggung jawab naskah.')
                        ->schema([
                            Grid::make(['default' => 1, 'sm' => 2, 'lg' => 6])->schema([
                                Placeholder::make('status_info')
                                    ->label('Status')
                                    ->content(fn (?Insight $record): string => $record?->status->canonical()->label() ?? 'Draft')
                                    ->columnSpan(['lg' => 2]),
                                Placeholder::make('editor_info')
                                    ->label('Editor')
                                    ->content(fn (?Insight $record): string => $record?->assignedEditor?->name ?? 'Belum ditugaskan')
                                    ->columnSpan(['lg' => 2]),
                                Placeholder::make('writer_info')
                                    ->label('Penulis')
                                    ->content(fn (?Insight $record): string => $record?->authors->pluck('name')->join(', ') ?: '—')
                                    ->columnSpan(['lg' => 2]),
                                Placeholder::make('submitted_info')
                                    ->label('Dikirim')
                                    ->content(fn (?Insight $record): string => $record?->submitted_at?->locale('id')->translatedFormat('d M Y, H:i') ?? '—')
                                    ->columnSpan(['lg' => 3]),
                                Placeholder::make('updated_info')
                                    ->label('Diperbarui')
                                    ->content(fn (?Insight $record): string => $record?->updated_at?->locale('id')->translatedFormat('d M Y, H:i') ?? '—')
                                    ->columnSpan(['lg' => 3]),
                            ])->columnSpanFull(),
                        ])
                        ->columnSpan(['xl' => 8]),
                    Section::make('Pengaturan Terbit')
                        ->description('Tentukan kapan artikel tersedia di website.')
                        ->schema([
                            DateTimePicker::make('published_at')
                                ->label('Jadwal Terbit (WIB)')
                                ->helperText('Kosongkan untuk langsung tayang. Pilih waktu mendatang untuk menjadwalkan artikel.')
                                ->timezone(config('edulaw.timezone'))
                                ->displayFormat('d M Y, H:i')
                                ->native(false)
                                ->seconds(false)
                                ->columnSpanFull(),
                        ])
                        ->columnSpan(['xl' => 4]),
                    Section::make('Naskah')
                        ->description('Identitas dan materi pendukung artikel.')
                        ->schema([
                            Grid::make(['default' => 1, 'lg' => 12])
                                ->schema([
                                    Group::make()
                                        ->schema([
                                            TextInput::make('title')->label('Judul')->required()->maxLength(255)->columnSpanFull(),
                                            Grid::make(['default' => 1, 'md' => 2])->schema([
                                                Select::make('insight_category_id')
                                                    ->label('Kategori')
                                                    ->relationship('category', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required(),
                                                Select::make('authors')
                                                    ->label('Penulis')
                                                    ->relationship('authors', 'name')
                                                    ->multiple()
                                                    ->searchable()
                                                    ->preload()
                                                    ->required(),
                                            ])->columnSpanFull(),
                                            Textarea::make('excerpt')->label('Excerpt')->rows(5)->maxLength(500)->columnSpanFull(),
                                        ])
                                        ->columnSpan(['lg' => 7]),
                                    Group::make()
                                        ->schema([
                                            FileUpload::make('cover_image')
                                                ->label('Cover')
                                                ->image()
                                                ->disk('public')
                                                ->directory('insights')
                                                ->visibility('public')
                                                ->imageEditor()
                                                ->maxSize(4096)
                                                ->columnSpanFull(),
                                        ])
                                        ->columnSpan(['lg' => 5]),
                                ])
                                ->columnSpanFull(),
                        ])
                        ->columnSpanFull(),
                    Section::make('Isi Artikel')
                        ->description('Tinjau dan sunting body artikel pada area kerja penuh.')
                        ->schema([
                            TinyMceEditor::make('content')
                                ->hiddenLabel()
                                ->required()
                                ->height(650)
                                ->fileAttachmentsDisk('public')
                                ->fileAttachmentsDirectory('insights/content-images')
                                ->fileAttachmentsVisibility('public')
                                ->fileAttachmentsAcceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                                ->fileAttachmentsMaxSize(4096)
                                ->columnSpanFull(),
                            Repeater::make('footnotes')
                                ->label('Sumber & Rujukan')
                                ->helperText('Cantumkan peraturan, putusan, jurnal, buku, atau URL resmi yang benar-benar digunakan. Tambahkan melalui tombol Catatan Kaki pada toolbar.')
                                ->relationship('footnotes')
                                ->defaultItems(0)
                                ->schema([
                                    Textarea::make('content')
                                        ->label('Rujukan')
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
                        ])
                        ->extraAttributes(['class' => 'edulaw-editorial-body-section'])
                        ->columnSpanFull(),
                    Section::make('Catatan Editor')
                        ->description('Catatan untuk Penulis saat naskah dikembalikan ke Draft.')
                        ->schema([
                            Textarea::make('editor_notes')
                                ->label('Catatan untuk Penulis')
                                ->rows(5)
                                ->maxLength(10000)
                                ->columnSpanFull(),
                        ])
                        ->columnSpanFull(),
                ])
                ->extraAttributes(['class' => 'edulaw-editorial-workspace-shell'])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Judul')->description(fn (Insight $record): string => $record->display_author)->searchable(['title', 'authors.name'])->wrap()->grow(),
                TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (InsightStatus $state): string => $state->canonical()->label())->color(fn (InsightStatus $state): string => $state->canonical()->color()),
                TextColumn::make('assignedEditor.name')->label('Editor')->placeholder('Belum ditugaskan'),
                TextColumn::make('updated_at')->label('Diperbarui')->since()->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([SelectFilter::make('status')->options(InsightStatus::options())])
            ->recordActions([
                Action::make('open')->label('Buka')->url(fn (Insight $record): string => static::getUrl('workspace', ['record' => $record])),
                static::assignmentAction(),
                static::reassignmentAction(),
            ]);
    }

    public static function assignmentAction(): Action
    {
        return Action::make('assign_editor')
            ->label('Tugaskan Editor')
            ->icon('heroicon-o-user-plus')
            ->schema([
                Select::make('editor_id')->label('Editor')->options(fn (): array => static::editorOptions())->searchable()->preload()->required(),
            ])
            ->visible(fn (Insight $record): bool => static::canAssignRecord($record))
            ->action(fn (Insight $record, array $data) => static::runAssignment($record, $data));
    }

    public static function reassignmentAction(): Action
    {
        return Action::make('reassign_editor')
            ->label('Ganti Editor')
            ->icon('heroicon-o-arrow-path')
            ->schema([
                Select::make('editor_id')->label('Editor Baru')->options(fn (): array => static::editorOptions())->searchable()->preload()->required(),
            ])
            ->visible(fn (Insight $record): bool => static::canReassignRecord($record))
            ->action(fn (Insight $record, array $data) => static::runAssignment($record, $data));
    }

    public static function historyAction(): Action
    {
        return Action::make('history')
            ->label('Riwayat')
            ->icon('heroicon-o-clock')
            ->modalSubmitAction(false)
            ->schema([
                Placeholder::make('history_list')->hiddenLabel()->content(function (Insight $record): HtmlString {
                    $items = $record->editorialActivities()->with('actor:id,name')->oldest()->get()->map(
                        fn ($activity): string => '<li class="mb-4 border-l-2 border-primary-500 pl-3"><strong>'.e($activity->actor?->name ?? 'Sistem').'</strong> '.e(mb_strtolower($activity->description)).'<br><span class="text-xs text-gray-500">'.e($activity->created_at->locale('id')->translatedFormat('d M Y · H:i')).'</span></li>'
                    )->join('');

                    return new HtmlString($items !== '' ? '<ol>'.$items.'</ol>' : '<p>Belum ada riwayat.</p>');
                }),
            ]);
    }

    public static function editorOptions(): array
    {
        return User::query()->role('editor')->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all();
    }

    public static function canAssignRecord(Insight $record): bool
    {
        return blank($record->assigned_editor_id) && (Auth::user()?->can('assignEditor', $record) ?? false);
    }

    public static function canReassignRecord(Insight $record): bool
    {
        return filled($record->assigned_editor_id) && (Auth::user()?->can('reassignEditor', $record) ?? false);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['authors:id,name', 'assignedEditor:id,name', 'category:id,name']);
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasAnyRole(['super_admin', 'Super Admin', 'SuperAdmin'])) {
            return $query;
        }

        return $query->where('assigned_editor_id', $user->id);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEditorialInsights::route('/'),
            'workspace' => ViewEditorialWorkspace::route('/{record}/workspace'),
        ];
    }

    private static function runAssignment(Insight $record, array $data): void
    {
        try {
            app(InsightEditorialWorkflowService::class)->assignEditor(
                $record,
                User::query()->findOrFail($data['editor_id']),
                Auth::user(),
            );

            Notification::make()->success()->title('Penugasan Editor diperbarui')->send();
        } catch (Throwable $exception) {
            report($exception);
            Notification::make()->danger()->title('Penugasan gagal')->body($exception->getMessage())->persistent()->send();
        }
    }
}
