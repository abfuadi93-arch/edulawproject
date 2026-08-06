<?php

namespace App\Filament\Resources\AssignedInsights;

use App\Enums\EditorAssignmentStatus;
use App\Enums\EditorialWorkflowStage;
use App\Enums\InsightStatus;
use App\Filament\Resources\AssignedInsights\Pages\ListAssignedInsights;
use App\Filament\Resources\Editorial\EditorialResource;
use App\Models\Insight;
use App\Services\Editorial\InsightAssignmentService;
use App\Services\InsightDeadlineService;
use App\Services\InsightEditorialWorkflowService;
use App\Services\InsightRevisionService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class AssignedInsightResource extends Resource
{
    protected static ?string $model = Insight::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen Editorial';

    protected static ?string $navigationLabel = 'Naskah Saya';

    protected static ?string $modelLabel = 'Naskah';

    protected static ?string $pluralModelLabel = 'Naskah Saya';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('view_assigned_editorial_submissions')
            || (Auth::user()?->can('view_assigned_editorial_insights') ?? false);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->extraAttributes(['class' => 'edulaw-inbox-table edulaw-assigned-insight-table'])
            ->columns([
                TextColumn::make('title')
                    ->label('Naskah')
                    ->searchable(['title', 'authors.name', 'category.name'])
                    ->sortable()
                    ->wrap()
                    ->lineClamp(2)
                    ->grow()
                    ->description(function (Insight $record): string {
                        $authors = $record->authors->pluck('name')->take(2)->join(', ');
                        $remainingAuthors = max(0, $record->authors->count() - 2);
                        $authorLabel = filled($authors) ? $authors : 'Penulis belum ditetapkan';

                        if ($remainingAuthors > 0) {
                            $authorLabel .= " +{$remainingAuthors}";
                        }

                        return $authorLabel.' · '.($record->category?->name ?? 'Tanpa kategori');
                    })
                    ->tooltip(fn (Insight $record): string => $record->title)
                    ->extraHeaderAttributes(['class' => 'edulaw-assigned-title-header'])
                    ->extraCellAttributes(['class' => 'edulaw-assigned-title-cell']),

                TextColumn::make('status')
                    ->label('Progres')
                    ->badge()
                    ->formatStateUsing(fn (InsightStatus $state): string => $state->label())
                    ->color(fn (InsightStatus $state): string => $state->color())
                    ->description(fn (Insight $record): string => $record->workflow_stage->label())
                    ->extraHeaderAttributes(['class' => 'edulaw-assigned-progress-header'])
                    ->extraCellAttributes(['class' => 'edulaw-assigned-progress-cell']),

                TextColumn::make('my_assignment_status')
                    ->label('Penugasan')
                    ->state(fn (Insight $record) => $record->editorAssignments->sortByDesc('id')->first()?->status)
                    ->badge()
                    ->formatStateUsing(fn (?EditorAssignmentStatus $state): string => $state?->label() ?? '—')
                    ->color(fn (?EditorAssignmentStatus $state): string => $state?->color() ?? 'gray')
                    ->description(function (Insight $record): string {
                        $dueAt = $record->editorAssignments->sortByDesc('id')->first()?->due_at;

                        return filled($dueAt)
                            ? 'Tenggat '.$dueAt->locale('id')->translatedFormat('d M Y, H:i')
                            : 'Tanpa tenggat';
                    })
                    ->tooltip(function (Insight $record): string {
                        $assignedAt = $record->editorAssignments->sortByDesc('id')->first()?->assigned_at;

                        return filled($assignedAt)
                            ? 'Ditugaskan '.$assignedAt->locale('id')->translatedFormat('d M Y, H:i')
                            : 'Tanggal penugasan tidak tersedia';
                    })
                    ->visibleFrom('md')
                    ->extraHeaderAttributes(['class' => 'edulaw-assigned-assignment-header'])
                    ->extraCellAttributes(['class' => 'edulaw-assigned-assignment-cell']),

                TextColumn::make('last_activity_at')
                    ->label('Aktivitas')
                    ->state(fn (Insight $record) => $record->editorialActivities->first()?->created_at ?: $record->updated_at)
                    ->formatStateUsing(fn ($state): string => $state->locale('id')->diffForHumans())
                    ->description(fn (Insight $record): string => "Putaran {$record->revision_round} · {$record->editorial_notes_count} catatan")
                    ->tooltip(fn ($state): string => $state->locale('id')->translatedFormat('d M Y, H:i'))
                    ->visibleFrom('lg')
                    ->extraHeaderAttributes(['class' => 'edulaw-assigned-activity-header'])
                    ->extraCellAttributes(['class' => 'edulaw-assigned-activity-cell']),
            ])
            ->recordUrl(fn (Insight $record): ?string => Auth::user()?->can('accessEditorialWorkspace', $record)
                ? EditorialResource::getUrl('workspace', ['record' => $record])
                : null)
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(InsightStatus::options()),
                SelectFilter::make('workflow_stage')->label('Tahap')->options(EditorialWorkflowStage::options()),
                SelectFilter::make('insight_category_id')->label('Kategori')->relationship('category', 'name')->searchable()->preload(),
                Filter::make('assignment_due')
                    ->label('Tenggat Assignment')
                    ->schema([
                        DatePicker::make('from')->label('Dari')->native(false),
                        DatePicker::make('until')->label('Sampai')->native(false),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->whereHas(
                        'editorAssignments',
                        fn (Builder $assignmentQuery): Builder => $assignmentQuery
                            ->where('editor_id', Auth::id())
                            ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('due_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('due_at', '<=', $date)),
                    )),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('workspace')
                        ->label('Buka Workspace')
                        ->icon('heroicon-o-rectangle-stack')
                        ->url(fn (Insight $record): string => EditorialResource::getUrl('workspace', ['record' => $record]))
                        ->visible(fn (Insight $record): bool => Auth::user()?->can('accessEditorialWorkspace', $record) ?? false),
                    Action::make('accept_assignment')
                        ->label('Terima Penugasan')
                        ->icon('heroicon-o-hand-thumb-up')
                        ->visible(fn (Insight $record): bool => filled($record->activeEditorAssignment)
                            && $record->activeEditorAssignment->status === EditorAssignmentStatus::Assigned
                            && (Auth::user()?->can('accept', $record->activeEditorAssignment) ?? false))
                        ->action(fn (Insight $record) => app(InsightAssignmentService::class)->acceptAssignment($record->activeEditorAssignment, Auth::user())),
                    Action::make('start_review')
                        ->label('Mulai Review')
                        ->icon('heroicon-o-play')
                        ->color('primary')
                        ->visible(fn (Insight $record): bool => filled($record->activeEditorAssignment)
                            && in_array($record->status, [InsightStatus::EditorAssigned, InsightStatus::Revised], true)
                            && (Auth::user()?->can('start', $record->activeEditorAssignment) ?? false))
                        ->action(fn (Insight $record) => app(InsightAssignmentService::class)->startAssignment($record->activeEditorAssignment, Auth::user())),
                    static::revisionHistoryAction(),
                    static::extendWriterDeadlineAction(),
                    Action::make('request_revision')
                        ->label('Minta Perbaikan')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('warning')
                        ->schema([
                            Textarea::make('note')->label('Catatan Perbaikan')->required()->rows(6),
                            DateTimePicker::make('editorial_deadline')->label('Tenggat Perbaikan')->seconds(false)->native(false),
                        ])
                        ->visible(fn (Insight $record): bool => $record->status === InsightStatus::InReview)
                        ->action(fn (Insight $record, array $data) => app(InsightEditorialWorkflowService::class)->requestRevision($record, Auth::user(), $data['note'], $data['editorial_deadline'] ?? null)),
                    Action::make('approve')
                        ->label('Setujui')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (Insight $record): bool => $record->status === InsightStatus::InReview)
                        ->action(fn (Insight $record) => app(InsightEditorialWorkflowService::class)->approve($record, Auth::user())),
                    Action::make('reject')
                        ->label('Tidak Dilanjutkan')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->schema([
                            Textarea::make('rejection_reason')->label('Alasan')->required()->rows(5),
                        ])
                        ->visible(fn (Insight $record): bool => in_array($record->status, [InsightStatus::EditorAssigned, InsightStatus::InReview, InsightStatus::Revised], true))
                        ->action(fn (Insight $record, array $data) => app(InsightEditorialWorkflowService::class)->reject($record, Auth::user(), $data['rejection_reason'])),
                    Action::make('publish')
                        ->label('Terbitkan')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (Insight $record): bool => $record->status === InsightStatus::Approved && (Auth::user()?->can('publish_insight') ?? false))
                        ->action(fn (Insight $record) => app(InsightEditorialWorkflowService::class)->publish($record, Auth::user())),
                    EditorialResource::historyAction(),
                ])->label('Aksi')->icon('heroicon-o-ellipsis-vertical'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $userId = Auth::id();

        return parent::getEloquentQuery()
            ->whereHas('editorAssignments', fn (Builder $query): Builder => $query->where('editor_id', $userId))
            ->with([
                'authors:id,name',
                'category:id,name',
                'latestRevision',
                'activeEditorAssignment.editor:id,name',
                'editorAssignments' => fn ($query) => $query->where('editor_id', $userId)->latest('id'),
                'editorialActivities' => fn ($query) => $query->latest('created_at')->limit(1),
            ])
            ->withCount('editorialNotes');
    }

    public static function getPages(): array
    {
        return ['index' => ListAssignedInsights::route('/')];
    }

    protected static function revisionHistoryAction(): Action
    {
        return Action::make('revisions')
            ->label('Riwayat Versi')
            ->icon('heroicon-o-document-duplicate')
            ->modalSubmitAction(false)
            ->schema([
                Placeholder::make('revision_list')->hiddenLabel()->content(function (Insight $record): HtmlString {
                    $items = $record->revisions()->with('submitter:id,name')->latest('revision_number')->get()->map(
                        fn ($revision): string => '<li class="mb-3"><strong>Versi '.e((string) $revision->revision_number).'</strong> · '.e($revision->submitted_at?->locale('id')->translatedFormat('d M Y, H:i') ?? '—').'<br><span class="text-sm">'.e($revision->revision_summary ?: 'Tanpa ringkasan').'</span></li>'
                    )->join('');

                    return new HtmlString($items !== '' ? '<ol>'.$items.'</ol>' : '<p>Belum ada versi.</p>');
                }),
            ]);
    }

    protected static function compareRevisionsAction(): Action
    {
        return Action::make('compare_revisions')
            ->label('Bandingkan Versi')
            ->icon('heroicon-o-arrows-right-left')
            ->modalWidth('7xl')
            ->modalSubmitAction(false)
            ->schema([
                Select::make('older_id')->label('Versi Lama')->options(fn (Insight $record): array => $record->revisions()->pluck('revision_number', 'id')->map(fn ($number) => "Versi {$number}")->all())->live()->required(),
                Select::make('newer_id')->label('Versi Baru')->options(fn (Insight $record): array => $record->revisions()->pluck('revision_number', 'id')->map(fn ($number) => "Versi {$number}")->all())->live()->required(),
                Placeholder::make('comparison')->hiddenLabel()->content(function ($get, Insight $record): HtmlString {
                    if (! $get('older_id') || ! $get('newer_id')) {
                        return new HtmlString('<p class="text-sm text-gray-500">Pilih dua versi untuk dibandingkan.</p>');
                    }

                    $older = $record->revisions()->findOrFail($get('older_id'));
                    $newer = $record->revisions()->findOrFail($get('newer_id'));
                    $comparison = app(InsightRevisionService::class)->compare($older, $newer);
                    $rows = collect($comparison)->map(function (array $values, string $field): string {
                        $old = is_array($values['old']) ? json_encode($values['old'], JSON_UNESCAPED_UNICODE) : strip_tags((string) $values['old']);
                        $new = is_array($values['new']) ? json_encode($values['new'], JSON_UNESCAPED_UNICODE) : strip_tags((string) $values['new']);
                        $class = $values['changed'] ? 'bg-amber-50' : '';

                        return '<tr class="'.$class.'"><th class="p-2 align-top text-left">'.e(str_replace('_', ' ', $field)).'</th><td class="p-2 align-top">'.nl2br(e($old)).'</td><td class="p-2 align-top">'.nl2br(e($new)).'</td></tr>';
                    })->join('');

                    return new HtmlString('<div class="overflow-x-auto"><table class="w-full table-fixed text-sm"><thead><tr><th class="w-32">Bagian</th><th>Versi Lama</th><th>Versi Baru</th></tr></thead><tbody>'.$rows.'</tbody></table></div>');
                }),
            ])
            ->visible(fn (Insight $record): bool => $record->revisions()->count() >= 2 && (Auth::user()?->can('compare_insight_revisions') ?? false));
    }

    protected static function extendWriterDeadlineAction(): Action
    {
        return Action::make('extend_writer_deadline')
            ->label('Perpanjang Tenggat Writer')
            ->icon('heroicon-o-calendar-days')
            ->schema([
                DateTimePicker::make('deadline')->label('Tenggat Baru')->seconds(false)->native(false)->required(),
                Textarea::make('reason')->label('Alasan')->required()->rows(4),
            ])
            ->fillForm(fn (Insight $record): array => ['deadline' => $record->writer_deadline])
            ->visible(fn (Insight $record): bool => filled($record->writer_deadline) && (Auth::user()?->can('extend_writer_deadline') ?? false))
            ->action(fn (Insight $record, array $data) => app(InsightDeadlineService::class)->extendWriterDeadline($record, Auth::user(), $data['deadline'], $data['reason']));
    }
}
