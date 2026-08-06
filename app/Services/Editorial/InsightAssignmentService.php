<?php

namespace App\Services\Editorial;

use App\Enums\EditorAssignmentStatus;
use App\Enums\EditorialActivityType;
use App\Enums\EditorialDecisionType;
use App\Enums\EditorialWorkflowStage;
use App\Enums\InsightStatus;
use App\Models\Insight;
use App\Models\InsightEditorAssignment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

class InsightAssignmentService
{
    public function __construct(
        private readonly InsightWorkflowService $workflow,
        private readonly InsightDecisionService $decisions,
        private readonly InsightAuditService $audit,
        private readonly InsightNotificationService $notifications,
    ) {}

    public function assignEditor(
        Insight $insight,
        User $editor,
        User $actor,
        Carbon|string|null $dueAt = null,
        ?string $note = null,
        bool $sendNotification = true,
    ): InsightEditorAssignment {
        $this->authorize($actor, 'assign_editor');
        $this->validateEditor($editor);
        $dueAt = $this->validateDueAt($dueAt);

        $assignment = DB::transaction(function () use ($insight, $editor, $actor, $dueAt, $note): InsightEditorAssignment {
            $locked = Insight::query()->lockForUpdate()->findOrFail($insight->id);

            if ($locked->status !== InsightStatus::Submitted || $locked->workflow_stage !== EditorialWorkflowStage::Submission) {
                throw new LogicException('Editor hanya dapat ditugaskan pada naskah yang sudah dikirim.');
            }

            if ($locked->editorAssignments()->active()->lockForUpdate()->exists()) {
                throw ValidationException::withMessages([
                    'assignment' => 'Naskah sudah memiliki assignment aktif. Muat ulang halaman sebelum melanjutkan.',
                ]);
            }

            $assignment = $locked->editorAssignments()->create([
                'editor_id' => $editor->id,
                'assigned_by' => $actor->id,
                'workflow_stage' => EditorialWorkflowStage::EditorialReview,
                'status' => EditorAssignmentStatus::Assigned,
                'assigned_at' => now(),
                'due_at' => $dueAt,
                'assignment_note' => filled($note) ? trim($note) : null,
            ])->load('editor');

            $locked->forceFill([
                'assigned_editor_id' => $editor->id,
                'assigned_by' => $actor->id,
                'assigned_at' => $assignment->assigned_at,
                'editor_deadline' => $dueAt,
                'editorial_deadline' => $dueAt,
                'editor_deadline_completed_at' => null,
            ])->save();

            $historyNote = "Editor {$editor->name} ditugaskan.";

            if (filled($note)) {
                $historyNote .= ' Catatan penugasan: '.trim($note);
            }

            $this->workflow->moveToEditorialReview($locked, $actor, $assignment, $historyNote);

            return $assignment->refresh()->load(['insight', 'editor', 'assignedBy']);
        });

        if ($sendNotification) {
            $this->notifications->editorAssigned($assignment->insight->load('creator'), $assignment, $actor);
        }

        return $assignment;
    }

    public function reassignEditor(
        Insight $insight,
        User $newEditor,
        User $actor,
        string $reason,
        Carbon|string|null $dueAt = null,
        ?string $note = null,
        bool $sendNotification = true,
    ): InsightEditorAssignment {
        $this->authorize($actor, 'reassign_editor');
        $this->validateEditor($newEditor);
        $dueAt = $this->validateDueAt($dueAt);

        if (blank($reason)) {
            throw ValidationException::withMessages(['reassignment_reason' => 'Alasan penggantian Editor wajib diisi.']);
        }

        [$oldAssignment, $newAssignment] = DB::transaction(function () use ($insight, $newEditor, $actor, $reason, $dueAt, $note): array {
            $locked = Insight::query()->lockForUpdate()->findOrFail($insight->id);
            $oldAssignment = $locked->editorAssignments()->active()->lockForUpdate()->latest('id')->first();

            if (! $oldAssignment) {
                throw ValidationException::withMessages(['assignment' => 'Assignment aktif tidak ditemukan. Muat ulang halaman.']);
            }

            if ((int) $oldAssignment->editor_id === (int) $newEditor->id) {
                throw ValidationException::withMessages(['editor_id' => 'Editor baru harus berbeda dari Editor saat ini.']);
            }

            $oldEditorName = $oldAssignment->editor?->name ?: 'sebelumnya';
            $oldAssignment->forceFill([
                'status' => EditorAssignmentStatus::Reassigned,
                'completed_at' => now(),
                'reassignment_reason' => trim($reason),
            ])->save();

            $newAssignment = $locked->editorAssignments()->create([
                'editor_id' => $newEditor->id,
                'assigned_by' => $actor->id,
                'workflow_stage' => EditorialWorkflowStage::EditorialReview,
                'status' => EditorAssignmentStatus::Assigned,
                'assigned_at' => now(),
                'due_at' => $dueAt,
                'assignment_note' => filled($note) ? trim($note) : null,
            ])->load('editor');

            $locked->forceFill([
                'assigned_editor_id' => $newEditor->id,
                'assigned_by' => $actor->id,
                'assigned_at' => $newAssignment->assigned_at,
                'editor_deadline' => $dueAt,
                'editorial_deadline' => $dueAt,
                'editor_deadline_completed_at' => null,
                'review_started_at' => null,
            ])->save();

            $historyNote = "Editor diganti dari {$oldEditorName} menjadi {$newEditor->name}. Alasan: ".trim($reason);

            if (filled($note)) {
                $historyNote .= ' Catatan penugasan: '.trim($note);
            }

            $this->workflow->recordReassignment($locked, $actor, $newAssignment, $historyNote);

            return [
                $oldAssignment->refresh()->load('editor'),
                $newAssignment->refresh()->load(['insight', 'editor', 'assignedBy']),
            ];
        });

        if ($sendNotification) {
            $this->notifications->editorReassigned($newAssignment->insight->load('creator'), $oldAssignment, $newAssignment, $actor);
        }

        return $newAssignment;
    }

    public function acceptAssignment(InsightEditorAssignment $assignment, User $actor): InsightEditorAssignment
    {
        $this->authorize($actor, 'accept_editor_assignment');

        return DB::transaction(function () use ($assignment, $actor): InsightEditorAssignment {
            $locked = InsightEditorAssignment::query()->lockForUpdate()->findOrFail($assignment->id);
            $this->authorizeAssignedEditorOrAdmin($locked, $actor);

            if ($locked->status !== EditorAssignmentStatus::Assigned) {
                throw new LogicException('Hanya penugasan baru yang dapat diterima.');
            }

            $locked->forceFill(['status' => EditorAssignmentStatus::Accepted, 'accepted_at' => now()])->save();
            $decision = $this->decisions->record($locked->insight, EditorialDecisionType::AcceptAssignment, $actor, $locked, 'Editor menerima penugasan.');
            $this->audit->record($locked->insight, EditorialActivityType::AssignmentAccepted, $actor, 'Editor menerima penugasan.', [
                'assignment_id' => $locked->id,
                'decision_id' => $decision->id,
            ]);

            return $locked->refresh();
        });
    }

    public function startAssignment(InsightEditorAssignment $assignment, User $actor): InsightEditorAssignment
    {
        $this->authorizeAny($actor, ['start_editorial_review', 'start_review']);

        $assignment = DB::transaction(function () use ($assignment, $actor): InsightEditorAssignment {
            $locked = InsightEditorAssignment::query()->with('insight')->lockForUpdate()->findOrFail($assignment->id);
            $this->authorizeAssignedEditorOrAdmin($locked, $actor);
            $canRestartReview = $locked->status === EditorAssignmentStatus::Active && $locked->insight->status === InsightStatus::Revised;

            if (! in_array($locked->status, [EditorAssignmentStatus::Assigned, EditorAssignmentStatus::Accepted], true) && ! $canRestartReview) {
                throw new LogicException('Assignment tidak berada pada kondisi yang dapat memulai review.');
            }

            $locked->forceFill([
                'status' => EditorAssignmentStatus::Active,
                'accepted_at' => $locked->accepted_at ?: now(),
                'started_at' => $locked->started_at ?: now(),
            ])->save();

            $this->workflow->startReview($locked->insight, $actor, $locked);

            return $locked->refresh()->load(['insight', 'editor']);
        });

        $this->notifications->reviewStarted($assignment->insight->load('creator'), $assignment, $actor);

        return $assignment;
    }

    public function completeAssignment(InsightEditorAssignment $assignment, User $actor): InsightEditorAssignment
    {
        $this->authorize($actor, 'complete_editor_assignment');

        return DB::transaction(function () use ($assignment, $actor): InsightEditorAssignment {
            $locked = InsightEditorAssignment::query()->with('insight')->lockForUpdate()->findOrFail($assignment->id);
            $this->authorizeAssignedEditorOrAdmin($locked, $actor);

            if (! $locked->status->isActive()) {
                throw new LogicException('Assignment sudah tidak aktif.');
            }

            if (! in_array($locked->insight->status, [InsightStatus::Approved, InsightStatus::Rejected, InsightStatus::Published, InsightStatus::Archived], true)) {
                throw new LogicException('Assignment hanya dapat diselesaikan setelah keputusan editorial final.');
            }

            $locked->forceFill(['status' => EditorAssignmentStatus::Completed, 'completed_at' => now()])->save();
            $this->audit->record($locked->insight, EditorialActivityType::AssignmentCompleted, $actor, 'Assignment editorial diselesaikan.', [
                'assignment_id' => $locked->id,
            ]);

            return $locked->refresh();
        });
    }

    public function cancelAssignment(InsightEditorAssignment $assignment, User $actor, ?string $reason = null): InsightEditorAssignment
    {
        $this->authorize($actor, 'cancel_editor_assignment');

        $assignment = DB::transaction(function () use ($assignment, $actor, $reason): InsightEditorAssignment {
            $locked = InsightEditorAssignment::query()->with('insight')->lockForUpdate()->findOrFail($assignment->id);

            if (! $locked->status->isActive()) {
                throw new LogicException('Assignment sudah tidak aktif.');
            }

            $locked->forceFill([
                'status' => EditorAssignmentStatus::Cancelled,
                'completed_at' => now(),
                'reassignment_reason' => filled($reason) ? trim($reason) : null,
            ])->save();

            $insight = $this->workflow->cancelAssignment($locked->insight, $actor, $locked, $reason);
            $insight->forceFill([
                'assigned_editor_id' => null,
                'assigned_by' => null,
                'assigned_at' => null,
                'editor_deadline' => null,
                'editorial_deadline' => null,
            ])->save();

            return $locked->refresh()->load(['insight', 'editor']);
        });

        $this->notifications->assignmentCancelled($assignment->insight, $assignment, $actor);

        return $assignment;
    }

    public function getActiveAssignment(Insight $insight): ?InsightEditorAssignment
    {
        return $insight->editorAssignments()->active()->latest('id')->first();
    }

    private function validateEditor(User $editor): void
    {
        if (! $editor->is_active) {
            throw ValidationException::withMessages(['editor_id' => 'Editor yang dipilih tidak aktif.']);
        }

        if (! $editor->hasAnyRole(['editor', 'Editor'])) {
            throw ValidationException::withMessages(['editor_id' => 'User yang dipilih tidak memiliki role Editor.']);
        }
    }

    private function validateDueAt(Carbon|string|null $dueAt): ?Carbon
    {
        if (blank($dueAt)) {
            return null;
        }

        $dueAt = Carbon::parse($dueAt);

        if ($dueAt->isPast()) {
            throw ValidationException::withMessages(['due_at' => 'Tenggat pemeriksaan tidak boleh berada di masa lalu.']);
        }

        return $dueAt;
    }

    private function authorizeAssignedEditorOrAdmin(InsightEditorAssignment $assignment, User $actor): void
    {
        if (! $actor->hasRole('super_admin') && (int) $assignment->editor_id !== (int) $actor->id) {
            throw new AuthorizationException('Assignment ini bukan milik Editor tersebut.');
        }
    }

    private function authorize(User $actor, string $permission): void
    {
        $this->authorizeAny($actor, [$permission]);
    }

    private function authorizeAny(User $actor, array $permissions): void
    {
        if (! collect($permissions)->contains(fn (string $permission): bool => $actor->can($permission))) {
            throw new AuthorizationException('Permission editorial yang diperlukan tidak tersedia.');
        }
    }
}
