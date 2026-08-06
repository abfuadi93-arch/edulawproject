<?php

namespace App\Filament\Resources\Editorial;

use App\Enums\EditorialWorkflowStage;
use App\Enums\InsightStatus;
use App\Filament\Resources\Editorial\Pages\ListEditorialInsights;
use App\Filament\Resources\Editorial\Pages\ViewEditorialWorkspace;
use App\Filament\Resources\Insights\InsightResource;
use App\Models\Insight;
use App\Models\User;
use App\Services\Editorial\InsightAssignmentService;
use App\Services\InsightDeadlineService;
use App\Services\InsightEditorialWorkflowService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use LogicException;
use Throwable;

class EditorialResource extends Resource
{
    protected static ?string $model = Insight::class;

    protected static ?string $slug = 'editorial';

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen Editorial';

    protected static ?string $navigationLabel = 'Manajemen Editorial';

    protected static ?string $modelLabel = 'Naskah Editorial';

    protected static ?string $pluralModelLabel = 'Manajemen Editorial';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        $user = Auth::user();

        return $user?->can('view_all_editorial_submissions')
            || $user?->can('view_all_editorial_insights')
            || ($user?->can('access_editorial_workspace') ?? false);
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        return $user?->can('view_all_editorial_submissions')
            || ($user?->can('view_all_editorial_insights') ?? false);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->lineClamp(2)
                    ->description(fn (Insight $record): string => collect([
                        $record->authors->pluck('name')->take(2)->join(', '),
                        $record->category?->name,
                    ])->filter()->join(' · '))
                    ->grow(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (InsightStatus $state): string => $state->label())
                    ->color(fn (InsightStatus $state): string => $state->color())
                    ->description(fn (Insight $record): string => $record->workflow_stage->label()),
                TextColumn::make('activeEditorAssignment.editor.name')
                    ->label('Editor')
                    ->placeholder('Belum ditugaskan')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md')
                    ->wrap()
                    ->description(function (Insight $record): ?string {
                        $assignment = $record->activeEditorAssignment;

                        if (! $assignment) {
                            return null;
                        }

                        return collect([
                            $assignment->status?->label(),
                            $assignment->due_at?->locale('id')->translatedFormat('d M Y'),
                        ])->filter()->join(' · ');
                    }),
                TextColumn::make('activeEditorAssignment.due_at')
                    ->label('Tenggat')
                    ->dateTime('d M Y, H:i')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('editorial_notes_count')
                    ->label('Catatan')
                    ->counts('editorialNotes')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->paginationPageOptions([10, 25, 50])
            ->filters([
                SelectFilter::make('status')->options(InsightStatus::options()),
                SelectFilter::make('active_editor')
                    ->label('Editor')
                    ->options(fn (): array => static::editorOptions())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, $editorId): Builder => $query->whereHas('editorAssignments', fn (Builder $query): Builder => $query->active()->where('editor_id', $editorId)),
                    ))
                    ->searchable()
                    ->preload(),
                SelectFilter::make('authors')->label('Penulis')->relationship('authors', 'name')->searchable()->preload(),
                SelectFilter::make('insight_category_id')->label('Kategori')->relationship('category', 'name')->searchable()->preload(),
                Filter::make('created_at')
                    ->label('Tanggal masuk')
                    ->schema([
                        DatePicker::make('from')->label('Dari')->native(false),
                        DatePicker::make('until')->label('Sampai')->native(false),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('submitted_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('submitted_at', '<=', $date))),
                TernaryFilter::make('unassigned')
                    ->label('Tanpa Editor')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereDoesntHave('editorAssignments', fn (Builder $query): Builder => $query->active()),
                        false: fn (Builder $query): Builder => $query->whereHas('editorAssignments', fn (Builder $query): Builder => $query->active()),
                    ),
                TernaryFilter::make('overdue')
                    ->label('Melewati Tenggat')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereHas('editorAssignments', fn (Builder $query): Builder => $query->active()->whereNotNull('due_at')->where('due_at', '<', now())),
                        false: fn (Builder $query): Builder => $query->whereDoesntHave('editorAssignments', fn (Builder $query): Builder => $query->active()->whereNotNull('due_at')->where('due_at', '<', now())),
                    ),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('submit_for_review')
                        ->label('Kirim untuk Review')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Kirim naskah untuk review?')
                        ->modalDescription('Status akan berubah dari Draf menjadi Dikirim. Setelah itu Editor dapat ditugaskan.')
                        ->modalSubmitActionLabel('Kirim untuk Review')
                        ->visible(fn (Insight $record): bool => InsightResource::canSubmitRecord($record))
                        ->action(function (Insight $record): void {
                            try {
                                app(InsightEditorialWorkflowService::class)->submit($record, Auth::user());

                                Notification::make()
                                    ->success()
                                    ->title('Naskah berhasil dikirim')
                                    ->body('Action Tugaskan Editor sekarang tersedia pada naskah ini.')
                                    ->send();
                            } catch (ValidationException $exception) {
                                Notification::make()
                                    ->danger()
                                    ->title('Naskah belum dapat dikirim')
                                    ->body(collect($exception->errors())->flatten()->implode(' '))
                                    ->persistent()
                                    ->send();
                            } catch (Throwable $exception) {
                                report($exception);

                                $message = $exception instanceof AuthorizationException || $exception instanceof LogicException
                                    ? $exception->getMessage()
                                    : 'Terjadi kesalahan saat mengirim naskah. Muat ulang halaman lalu coba kembali.';

                                Notification::make()
                                    ->danger()
                                    ->title('Naskah gagal dikirim')
                                    ->body($message)
                                    ->persistent()
                                    ->send();
                            }
                        }),
                    static::assignmentAction(),
                    static::reassignmentAction(),
                    Action::make('edit_draft')
                        ->label('Lengkapi Draft')
                        ->icon('heroicon-o-pencil-square')
                        ->url(fn (Insight $record): string => InsightResource::getUrl('edit', ['record' => $record]))
                        ->visible(fn (Insight $record): bool => $record->status === InsightStatus::Draft
                            && (Auth::user()?->can('update', $record) ?? false)),
                    static::cancelAssignmentAction(),
                    Action::make('workspace')
                        ->label('Buka Workspace')
                        ->icon('heroicon-o-rectangle-stack')
                        ->url(fn (Insight $record): string => static::getUrl('workspace', ['record' => $record]))
                        ->visible(fn (Insight $record): bool => Auth::user()?->can('accessEditorialWorkspace', $record) ?? false),
                    Action::make('publish')
                        ->label('Terbitkan')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (Insight $record): bool => $record->status === InsightStatus::Approved && (Auth::user()?->can('publish_insight') ?? false))
                        ->action(fn (Insight $record) => app(InsightEditorialWorkflowService::class)->publish($record, Auth::user())),
                    static::extendDeadlineAction('editor'),
                    static::extendDeadlineAction('writer'),
                    static::historyAction(),
                    Action::make('archive')
                        ->label('Arsipkan')
                        ->icon('heroicon-o-archive-box')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn (Insight $record): bool => $record->status !== InsightStatus::Archived && (Auth::user()?->can('archive_insight') ?? false))
                        ->action(fn (Insight $record) => app(InsightEditorialWorkflowService::class)->archive($record, Auth::user())),
                ])->label('Aksi')->icon('heroicon-o-ellipsis-vertical')->tooltip('Aksi naskah')->color('gray'),
            ], position: RecordActionsPosition::AfterColumns)
            ->recordActionsColumnLabel('Aksi');
    }

    public static function assignmentAction(): Action
    {
        return Action::make('assign_editor')
            ->label('Tugaskan Editor')
            ->icon('heroicon-o-user-plus')
            ->schema([
                Select::make('editor_id')
                    ->label('Editor')
                    ->options(fn (): array => static::editorOptions())
                    ->searchable()
                    ->preload()
                    ->required(),
                DateTimePicker::make('due_at')
                    ->label('Tenggat Pemeriksaan')
                    ->seconds(false)
                    ->native(false),
                Textarea::make('assignment_note')
                    ->label('Catatan Penugasan')
                    ->rows(4)
                    ->maxLength(2000),
                Toggle::make('send_notification')
                    ->label('Kirim notifikasi kepada Editor')
                    ->default(true),
            ])
            ->visible(fn (Insight $record): bool => static::canAssignRecord($record))
            ->action(function (Insight $record, array $data): void {
                try {
                    $editor = User::query()->findOrFail($data['editor_id']);
                    $actor = Auth::user();

                    abort_unless($actor, 403);

                    app(InsightAssignmentService::class)->assignEditor(
                        $record,
                        $editor,
                        $actor,
                        $data['due_at'] ?? null,
                        $data['assignment_note'] ?? null,
                        (bool) ($data['send_notification'] ?? true),
                    );

                    Notification::make()
                        ->success()
                        ->title('Editor berhasil ditugaskan')
                        ->body("{$editor->name} sekarang menangani naskah ini.")
                        ->send();
                } catch (Throwable $exception) {
                    static::assignmentFailure($exception);
                }
            });
    }

    public static function reassignmentAction(): Action
    {
        return Action::make('reassign_editor')
            ->label('Ganti Editor')
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->schema([
                Select::make('editor_id')->label('Editor Baru')->options(fn (): array => static::editorOptions())->searchable()->preload()->required(),
                Textarea::make('reassignment_reason')->label('Alasan Penggantian')->required()->rows(4)->maxLength(2000),
                DateTimePicker::make('due_at')->label('Tenggat Baru')->seconds(false)->native(false),
                Textarea::make('assignment_note')->label('Catatan Penugasan')->rows(4)->maxLength(2000),
                Toggle::make('send_notification')->label('Kirim notifikasi kepada Editor lama dan baru')->default(true),
            ])
            ->fillForm(fn (Insight $record): array => ['due_at' => $record->activeEditorAssignment?->due_at])
            ->visible(fn (Insight $record): bool => static::canReassignRecord($record))
            ->action(function (Insight $record, array $data): void {
                try {
                    $editor = User::query()->findOrFail($data['editor_id']);
                    $actor = Auth::user();
                    abort_unless($actor, 403);

                    app(InsightAssignmentService::class)->reassignEditor(
                        $record,
                        $editor,
                        $actor,
                        $data['reassignment_reason'],
                        $data['due_at'] ?? null,
                        $data['assignment_note'] ?? null,
                        (bool) ($data['send_notification'] ?? true),
                    );

                    Notification::make()->success()->title('Editor berhasil diganti')->body("{$editor->name} sekarang menangani naskah ini.")->send();
                } catch (Throwable $exception) {
                    static::assignmentFailure($exception);
                }
            });
    }

    protected static function cancelAssignmentAction(): Action
    {
        return Action::make('cancel_assignment')
            ->label('Batalkan Assignment')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->schema([
                Textarea::make('reason')->label('Alasan Pembatalan')->required()->rows(4),
            ])
            ->visible(fn (Insight $record): bool => filled($record->activeEditorAssignment) && (Auth::user()?->can('cancel_editor_assignment') ?? false))
            ->action(function (Insight $record, array $data): void {
                try {
                    $actor = Auth::user();
                    abort_unless($actor && $record->activeEditorAssignment, 403);
                    app(InsightAssignmentService::class)->cancelAssignment($record->activeEditorAssignment, $actor, $data['reason']);
                    Notification::make()->success()->title('Assignment dibatalkan')->send();
                } catch (Throwable $exception) {
                    static::assignmentFailure($exception);
                }
            });
    }

    public static function editorOptions(): array
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn (Builder $query): Builder => $query->whereIn('name', ['editor', 'Editor']))
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'institution'])
            ->mapWithKeys(fn (User $editor): array => [
                $editor->id => collect([$editor->name, $editor->email, $editor->institution])->filter()->join(' · '),
            ])
            ->all();
    }

    public static function canAssignRecord(Insight $record): bool
    {
        return (Auth::user()?->can('assign_editor') ?? false)
            && $record->status === InsightStatus::Submitted
            && $record->workflow_stage === EditorialWorkflowStage::Submission
            && ! $record->editorAssignments()->active()->exists();
    }

    public static function canReassignRecord(Insight $record): bool
    {
        return (Auth::user()?->can('reassign_editor') ?? false)
            && $record->editorAssignments()->active()->exists()
            && in_array($record->status, [
                InsightStatus::EditorAssigned,
                InsightStatus::InReview,
                InsightStatus::RevisionRequested,
                InsightStatus::Revised,
            ], true);
    }

    private static function assignmentFailure(Throwable $exception): void
    {
        report($exception);

        $message = match (true) {
            $exception instanceof ValidationException => collect($exception->errors())->flatten()->first(),
            $exception instanceof AuthorizationException,
            $exception instanceof LogicException => $exception->getMessage(),
            default => 'Terjadi konflik atau kesalahan saat menyimpan assignment. Muat ulang halaman lalu coba kembali.',
        };

        Notification::make()->danger()->title('Assignment Editor gagal')->body($message)->persistent()->send();
    }

    protected static function extendDeadlineAction(string $owner): Action
    {
        $label = $owner === 'editor' ? 'Editor' : 'Writer';

        return Action::make("extend_{$owner}_deadline")
            ->label("Perpanjang Tenggat {$label}")
            ->icon('heroicon-o-calendar-days')
            ->schema([
                DateTimePicker::make('deadline')->label("Tenggat {$label} Baru")->seconds(false)->native(false)->required(),
                Textarea::make('reason')->label('Alasan Perpanjangan')->required()->rows(4),
            ])
            ->fillForm(fn (Insight $record): array => ['deadline' => $record->getAttribute("{$owner}_deadline")])
            ->visible(fn (Insight $record): bool => filled($record->getAttribute("{$owner}_deadline")) && (Auth::user()?->can("extend_{$owner}_deadline") ?? false))
            ->action(function (Insight $record, array $data) use ($owner): void {
                $service = app(InsightDeadlineService::class);

                if ($owner === 'editor') {
                    $service->extendEditorDeadline($record, Auth::user(), $data['deadline'], $data['reason']);
                } else {
                    $service->extendWriterDeadline($record, Auth::user(), $data['deadline'], $data['reason']);
                }
            });
    }

    public static function historyAction(): Action
    {
        return Action::make('history')
            ->label('Riwayat Status')
            ->icon('heroicon-o-clock')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup')
            ->schema([
                Placeholder::make('timeline')
                    ->hiddenLabel()
                    ->content(fn (Insight $record): HtmlString => static::historyHtml($record)),
            ])
            ->visible(fn (Insight $record): bool => Auth::user()?->can('viewHistory', $record) ?? false);
    }

    public static function historyHtml(Insight $record): HtmlString
    {
        $items = $record->statusHistories()
            ->with('actor:id,name')
            ->oldest()
            ->get()
            ->map(function ($history): string {
                $from = $history->from_status?->label() ?? 'Awal';
                $to = $history->to_status->label();
                $notes = filled($history->notes) ? '<div class="mt-1 text-sm text-gray-600">'.nl2br(e($history->notes)).'</div>' : '';

                return '<li class="mb-4 border-l-2 border-primary-500 pl-4"><div class="font-medium">'.e($from).' → '.e($to).'</div><div class="text-xs text-gray-500">'.e($history->created_at->locale('id')->translatedFormat('d M Y, H:i')).' · '.e($history->actor?->name ?? 'Sistem').'</div>'.$notes.'</li>';
            })
            ->join('');

        return new HtmlString($items !== '' ? '<ol>'.$items.'</ol>' : '<p class="text-sm text-gray-500">Belum ada riwayat status.</p>');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['activeEditorAssignment.editor:id,name,email,institution', 'authors:id,name', 'category:id,name'])
            ->withCount(['editorialNotes', 'revisions']);
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->can('view_all_editorial_submissions') || $user->can('view_all_editorial_insights')) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($user): void {
            if ($user->can('view_assigned_editorial_submissions') || $user->can('view_assigned_editorial_insights')) {
                $query->orWhereHas('editorAssignments', fn (Builder $assignmentQuery): Builder => $assignmentQuery
                    ->active()
                    ->where('editor_id', $user->id));
            }

            if ($user->can('view_own_editorial_submissions') || $user->can('view insights')) {
                $query->orWhere('created_by', $user->id);
            }
        });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEditorialInsights::route('/'),
            'workspace' => ViewEditorialWorkspace::route('/{record}/workspace'),
        ];
    }
}
