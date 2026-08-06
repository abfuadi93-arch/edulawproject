<?php

namespace App\Filament\Resources\Editorial\Pages;

use App\Enums\EditorAssignmentStatus;
use App\Enums\EditorialWorkflowStage;
use App\Enums\InsightStatus;
use App\Filament\Resources\Editorial\EditorialResource;
use App\Models\Insight;
use App\Models\User;
use App\Services\Editorial\InsightAssignmentService;
use App\Services\InsightEditorialWorkflowService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Throwable;

class ViewEditorialWorkspace extends ViewRecord
{
    protected static string $resource = EditorialResource::class;

    protected string $view = 'filament.resources.editorial.pages.editorial-workspace';

    protected static ?string $title = 'Workspace Editorial';

    protected function authorizeAccess(): void
    {
        Gate::authorize('accessEditorialWorkspace', $this->getRecord());
    }

    public function getSubheading(): ?string
    {
        return 'ID Editorial #'.$this->getRecord()->id;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('assign_editor')
                ->label('Tugaskan Editor')
                ->icon('heroicon-o-user-plus')
                ->schema($this->assignmentSchema())
                ->visible(fn (): bool => EditorialResource::canAssignRecord($this->getRecord()))
                ->action(function (array $data): void {
                    $this->runAction(function () use ($data): void {
                        app(InsightAssignmentService::class)->assignEditor(
                            $this->getRecord(),
                            User::query()->findOrFail($data['editor_id']),
                            Auth::user(),
                            $data['due_at'] ?? null,
                            $data['assignment_note'] ?? null,
                            (bool) ($data['send_notification'] ?? true),
                        );
                    }, 'Editor berhasil ditugaskan.');
                }),
            Action::make('accept_assignment')
                ->label('Terima Penugasan')
                ->icon('heroicon-o-hand-thumb-up')
                ->visible(fn (): bool => ($assignment = $this->activeAssignment())
                    && $assignment->status === EditorAssignmentStatus::Assigned
                    && (Auth::user()?->can('accept', $assignment) ?? false))
                ->action(fn () => $this->runAction(
                    fn () => app(InsightAssignmentService::class)->acceptAssignment($this->activeAssignment(), Auth::user()),
                    'Penugasan diterima.',
                )),
            Action::make('start_review')
                ->label('Mulai Review')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->visible(fn (): bool => ($assignment = $this->activeAssignment())
                    && in_array($this->getRecord()->status, [InsightStatus::EditorAssigned, InsightStatus::Revised], true)
                    && (Auth::user()?->can('start', $assignment) ?? false))
                ->action(fn () => $this->runAction(
                    fn () => app(InsightAssignmentService::class)->startAssignment($this->activeAssignment(), Auth::user()),
                    'Review editorial dimulai.',
                )),
            Action::make('review_note')
                ->label('Catatan Review')
                ->icon('heroicon-o-pencil-square')
                ->schema([
                    Textarea::make('note')
                        ->label('Catatan Umum')
                        ->helperText('Tulis catatan untuk keseluruhan naskah. Tidak perlu memilih bagian atau menyelesaikan status komentar.')
                        ->required()
                        ->rows(6),
                    Toggle::make('is_visible_to_writer')
                        ->label('Bagikan kepada Penulis')
                        ->default(true),
                ])
                ->visible(fn (): bool => in_array($this->getRecord()->status, [
                    InsightStatus::EditorAssigned,
                    InsightStatus::InReview,
                    InsightStatus::RevisionRequested,
                    InsightStatus::Revised,
                ], true) && (Auth::user()?->can('add_editorial_note') ?? false))
                ->action(fn (array $data) => $this->runAction(
                    fn () => app(InsightEditorialWorkflowService::class)->addEditorialNote(
                        $this->getRecord(),
                        Auth::user(),
                        $data['note'],
                        (bool) ($data['is_visible_to_writer'] ?? true),
                    ),
                    'Catatan review disimpan.',
                )),
            Action::make('request_revision')
                ->label('Minta Perbaikan')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->schema([
                    Textarea::make('note')->label('Catatan Perbaikan Umum')->required()->rows(6),
                    DateTimePicker::make('editorial_deadline')->label('Tenggat Perbaikan')->seconds(false)->native(false),
                ])
                ->visible(fn (): bool => $this->getRecord()->status === InsightStatus::InReview
                    && (Auth::user()?->can('request_revision') ?? false))
                ->action(fn (array $data) => $this->runAction(
                    fn () => app(InsightEditorialWorkflowService::class)->requestRevision(
                        $this->getRecord(),
                        Auth::user(),
                        $data['note'],
                        $data['editorial_deadline'] ?? null,
                    ),
                    'Perbaikan naskah diminta.',
                )),
            Action::make('approve')
                ->label('Setujui Naskah')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->getRecord()->status === InsightStatus::InReview
                    && (Auth::user()?->can('approve_insight') ?? false))
                ->action(fn () => $this->runAction(
                    fn () => app(InsightEditorialWorkflowService::class)->approve($this->getRecord(), Auth::user()),
                    'Naskah disetujui.',
                )),
            ActionGroup::make([
                Action::make('reassign_editor')
                    ->label('Ganti Editor')
                    ->icon('heroicon-o-arrow-path')
                    ->schema([
                        Select::make('editor_id')->label('Editor Baru')->options(fn (): array => EditorialResource::editorOptions())->searchable()->preload()->required(),
                        Textarea::make('reassignment_reason')->label('Alasan Penggantian')->required()->rows(4),
                        DateTimePicker::make('due_at')->label('Tenggat Baru')->seconds(false)->native(false),
                        Textarea::make('assignment_note')->label('Catatan Penugasan')->rows(4),
                        Toggle::make('send_notification')->label('Kirim notifikasi')->default(true),
                    ])
                    ->visible(fn (): bool => EditorialResource::canReassignRecord($this->getRecord()))
                    ->action(function (array $data): void {
                        $this->runAction(function () use ($data): void {
                            app(InsightAssignmentService::class)->reassignEditor(
                                $this->getRecord(),
                                User::query()->findOrFail($data['editor_id']),
                                Auth::user(),
                                $data['reassignment_reason'],
                                $data['due_at'] ?? null,
                                $data['assignment_note'] ?? null,
                                (bool) ($data['send_notification'] ?? true),
                            );
                        }, 'Editor berhasil diganti.');
                    }),
                Action::make('reject')
                    ->label('Tidak Dilanjutkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->schema([
                        Textarea::make('rejection_reason')->label('Alasan')->required()->rows(5),
                    ])
                    ->visible(fn (): bool => in_array($this->getRecord()->status, [
                        InsightStatus::EditorAssigned,
                        InsightStatus::InReview,
                        InsightStatus::Revised,
                    ], true) && (Auth::user()?->can('reject_insight') ?? false))
                    ->action(fn (array $data) => $this->runAction(
                        fn () => app(InsightEditorialWorkflowService::class)->reject(
                            $this->getRecord(),
                            Auth::user(),
                            $data['rejection_reason'],
                        ),
                        'Naskah tidak dilanjutkan.',
                    )),
                Action::make('complete_assignment')
                    ->label('Selesaikan Assignment')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (): bool => ($assignment = $this->activeAssignment())
                        && in_array($this->getRecord()->status, [InsightStatus::Approved, InsightStatus::Rejected, InsightStatus::Published, InsightStatus::Archived], true)
                        && (Auth::user()?->can('complete', $assignment) ?? false))
                    ->action(fn () => $this->runAction(
                        fn () => app(InsightAssignmentService::class)->completeAssignment($this->activeAssignment(), Auth::user()),
                        'Assignment diselesaikan.',
                    )),
                Action::make('cancel_assignment')
                    ->label('Batalkan Assignment')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->schema([Textarea::make('reason')->label('Alasan')->required()->rows(4)])
                    ->visible(fn (): bool => ($assignment = $this->activeAssignment())
                        && (Auth::user()?->can('cancel', $assignment) ?? false))
                    ->action(fn (array $data) => $this->runAction(
                        fn () => app(InsightAssignmentService::class)->cancelAssignment($this->activeAssignment(), Auth::user(), $data['reason']),
                        'Assignment dibatalkan.',
                    )),
                Action::make('publish')
                    ->label('Terbitkan')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (): bool => $this->getRecord()->status === InsightStatus::Approved
                        && $this->getRecord()->workflow_stage === EditorialWorkflowStage::FinalApproval
                        && (Auth::user()?->can('publish_approved_insight') ?? false))
                    ->action(fn () => $this->runAction(
                        fn () => app(InsightEditorialWorkflowService::class)->publish($this->getRecord(), Auth::user()),
                        'Naskah diterbitkan.',
                    )),
                Action::make('archive')
                    ->label('Arsipkan')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (): bool => $this->getRecord()->status !== InsightStatus::Archived
                        && (Auth::user()?->can('archive_insight') ?? false))
                    ->action(fn () => $this->runAction(
                        fn () => app(InsightEditorialWorkflowService::class)->archive($this->getRecord(), Auth::user()),
                        'Naskah diarsipkan.',
                    )),
            ])
                ->label('Aksi Lainnya')
                ->icon('heroicon-o-ellipsis-horizontal')
                ->button()
                ->color('gray'),
        ];
    }

    private function assignmentSchema(): array
    {
        return [
            Select::make('editor_id')->label('Editor')->options(fn (): array => EditorialResource::editorOptions())->searchable()->preload()->required(),
            DateTimePicker::make('due_at')->label('Tenggat Pemeriksaan')->seconds(false)->native(false),
            Textarea::make('assignment_note')->label('Catatan Penugasan')->rows(4),
            Toggle::make('send_notification')->label('Kirim notifikasi')->default(true),
        ];
    }

    private function activeAssignment()
    {
        return app(InsightAssignmentService::class)->getActiveAssignment($this->getRecord());
    }

    private function runAction(callable $callback, string $successMessage): void
    {
        try {
            $callback();
            $this->record = Insight::query()->findOrFail($this->getRecord()->id);
            Notification::make()->success()->title($successMessage)->send();
        } catch (Throwable $exception) {
            report($exception);
            Notification::make()->danger()->title('Aksi editorial gagal')->body($exception->getMessage())->persistent()->send();
        }
    }
}
