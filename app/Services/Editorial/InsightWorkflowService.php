<?php

namespace App\Services\Editorial;

use App\Enums\EditorialActivityType;
use App\Enums\EditorialDecisionType;
use App\Enums\EditorialWorkflowStage;
use App\Enums\InsightStatus;
use App\Models\Insight;
use App\Models\InsightEditorAssignment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use LogicException;

class InsightWorkflowService
{
    public function __construct(
        private readonly InsightDecisionService $decisions,
        private readonly InsightAuditService $audit,
    ) {}

    public function submit(Insight $insight, User $actor, ?string $note = null): Insight
    {
        $this->authorizeSubmission($insight, $actor);

        return $this->transition(
            $insight,
            $actor,
            [InsightStatus::Draft],
            [EditorialWorkflowStage::Submission],
            InsightStatus::Submitted,
            EditorialWorkflowStage::Submission,
            EditorialDecisionType::Submit,
            EditorialActivityType::SubmissionSubmitted,
            ['submitted_at' => now(), 'published_at' => null],
            $note ?: 'Naskah dikirim untuk diperiksa.',
        );
    }

    public function moveToEditorialReview(Insight $insight, User $actor, InsightEditorAssignment $assignment, ?string $note = null): Insight
    {
        $this->authorizeAnyPermission($actor, ['assign_editor']);

        return $this->transition(
            $insight,
            $actor,
            [InsightStatus::Submitted],
            [EditorialWorkflowStage::Submission],
            InsightStatus::EditorAssigned,
            EditorialWorkflowStage::EditorialReview,
            EditorialDecisionType::AssignEditor,
            EditorialActivityType::EditorAssigned,
            [],
            $note ?: "Editor {$assignment->editor?->name} ditugaskan.",
            $assignment,
        );
    }

    public function recordReassignment(Insight $insight, User $actor, InsightEditorAssignment $assignment, string $reason): Insight
    {
        $this->authorizeAnyPermission($actor, ['reassign_editor']);

        return $this->transition(
            $insight,
            $actor,
            [InsightStatus::EditorAssigned, InsightStatus::InReview, InsightStatus::RevisionRequested, InsightStatus::Revised],
            [EditorialWorkflowStage::EditorialReview, EditorialWorkflowStage::AuthorRevision],
            InsightStatus::EditorAssigned,
            EditorialWorkflowStage::EditorialReview,
            EditorialDecisionType::ReassignEditor,
            EditorialActivityType::EditorReassigned,
            ['review_started_at' => null],
            $reason,
            $assignment,
        );
    }

    public function startReview(Insight $insight, User $actor, InsightEditorAssignment $assignment): Insight
    {
        $this->authorizeAnyPermission($actor, ['start_editorial_review', 'start_review']);

        return $this->transition(
            $insight,
            $actor,
            [InsightStatus::EditorAssigned, InsightStatus::Revised],
            [EditorialWorkflowStage::EditorialReview],
            InsightStatus::InReview,
            EditorialWorkflowStage::EditorialReview,
            EditorialDecisionType::StartReview,
            EditorialActivityType::ReviewStarted,
            [
                'review_started_at' => now(),
                'current_review_round' => max(1, ((int) $insight->current_review_round) + 1),
            ],
            'Editor mulai memeriksa naskah.',
            $assignment,
        );
    }

    public function moveToAuthorRevision(Insight $insight, User $actor, InsightEditorAssignment $assignment, string $note): Insight
    {
        $this->authorizeAnyPermission($actor, ['request_revision']);

        return $this->transition(
            $insight,
            $actor,
            [InsightStatus::InReview],
            [EditorialWorkflowStage::EditorialReview],
            InsightStatus::RevisionRequested,
            EditorialWorkflowStage::AuthorRevision,
            EditorialDecisionType::RequestRevision,
            EditorialActivityType::RevisionRequested,
            ['revision_requested_at' => now()],
            $note,
            $assignment,
        );
    }

    public function returnToEditorialReview(Insight $insight, User $actor, InsightEditorAssignment $assignment, string $note, int $revisionNumber): Insight
    {
        $this->authorizeOwner($insight, $actor, 'resubmit_revision');

        return $this->transition(
            $insight,
            $actor,
            [InsightStatus::RevisionRequested],
            [EditorialWorkflowStage::AuthorRevision],
            InsightStatus::Revised,
            EditorialWorkflowStage::EditorialReview,
            EditorialDecisionType::Resubmit,
            EditorialActivityType::RevisionSubmitted,
            [
                'revision_round' => $revisionNumber,
                'current_revision_number' => $revisionNumber,
                'revised_at' => now(),
            ],
            $note,
            $assignment,
        );
    }

    public function moveToFinalApproval(Insight $insight, User $actor, InsightEditorAssignment $assignment, ?string $note = null): Insight
    {
        $this->authorizeAnyPermission($actor, ['approve_insight']);

        return $this->transition(
            $insight,
            $actor,
            [InsightStatus::InReview],
            [EditorialWorkflowStage::EditorialReview],
            InsightStatus::Approved,
            EditorialWorkflowStage::FinalApproval,
            EditorialDecisionType::Approve,
            EditorialActivityType::InsightApproved,
            [
                'approved_at' => now(),
                'approved_by' => $actor->id,
                'reviewed_at' => now(),
                'reviewed_by' => $actor->id,
            ],
            $note ?: 'Naskah disetujui Editor.',
            $assignment,
        );
    }

    public function reject(Insight $insight, User $actor, InsightEditorAssignment $assignment, string $reason): Insight
    {
        $this->authorizeAnyPermission($actor, ['reject_insight']);

        return $this->transition(
            $insight,
            $actor,
            [InsightStatus::EditorAssigned, InsightStatus::InReview, InsightStatus::Revised],
            [EditorialWorkflowStage::EditorialReview],
            InsightStatus::Rejected,
            EditorialWorkflowStage::FinalApproval,
            EditorialDecisionType::Reject,
            EditorialActivityType::InsightRejected,
            ['rejected_at' => now(), 'rejected_by' => $actor->id, 'rejection_reason' => $reason],
            $reason,
            $assignment,
        );
    }

    public function moveToPublication(Insight $insight, User $actor, ?InsightEditorAssignment $assignment = null): Insight
    {
        $this->authorizeAnyPermission($actor, ['publish_approved_insight', 'publish_insight']);

        return $this->transition(
            $insight,
            $actor,
            [InsightStatus::Approved],
            [EditorialWorkflowStage::FinalApproval],
            InsightStatus::Published,
            EditorialWorkflowStage::Publication,
            EditorialDecisionType::Publish,
            EditorialActivityType::InsightPublished,
            ['published_at' => now()],
            'Naskah diterbitkan.',
            $assignment,
        );
    }

    public function archive(Insight $insight, User $actor, ?InsightEditorAssignment $assignment = null, ?string $note = null): Insight
    {
        $this->authorizeAnyPermission($actor, ['archive_insight']);

        if ($insight->status === InsightStatus::Archived) {
            throw new LogicException('Naskah sudah diarsipkan.');
        }

        return $this->transition(
            $insight,
            $actor,
            InsightStatus::cases(),
            EditorialWorkflowStage::cases(),
            InsightStatus::Archived,
            EditorialWorkflowStage::Publication,
            EditorialDecisionType::Archive,
            EditorialActivityType::InsightArchived,
            ['published_at' => null],
            $note ?: 'Naskah diarsipkan.',
            $assignment,
        );
    }

    public function cancelAssignment(Insight $insight, User $actor, InsightEditorAssignment $assignment, ?string $note = null): Insight
    {
        $this->authorizeAnyPermission($actor, ['cancel_editor_assignment']);

        return $this->transition(
            $insight,
            $actor,
            [InsightStatus::EditorAssigned, InsightStatus::InReview, InsightStatus::RevisionRequested, InsightStatus::Revised],
            [EditorialWorkflowStage::EditorialReview, EditorialWorkflowStage::AuthorRevision],
            InsightStatus::Submitted,
            EditorialWorkflowStage::Submission,
            EditorialDecisionType::CancelAssignment,
            EditorialActivityType::AssignmentCancelled,
            ['review_started_at' => null],
            $note ?: 'Penugasan Editor dibatalkan.',
            $assignment,
        );
    }

    private function transition(
        Insight $insight,
        User $actor,
        array $allowedStatuses,
        array $allowedStages,
        InsightStatus $toStatus,
        EditorialWorkflowStage $toStage,
        EditorialDecisionType $decisionType,
        EditorialActivityType $activityType,
        array $attributes,
        string $note,
        ?InsightEditorAssignment $assignment = null,
    ): Insight {
        return DB::transaction(function () use ($insight, $actor, $allowedStatuses, $allowedStages, $toStatus, $toStage, $decisionType, $activityType, $attributes, $note, $assignment): Insight {
            $locked = Insight::query()->lockForUpdate()->findOrFail($insight->id);
            $this->assertState($locked, $allowedStatuses, $allowedStages);
            $fromStatus = $locked->status;
            $fromStage = $locked->workflow_stage;

            $locked->forceFill([
                ...$attributes,
                'status' => $toStatus,
                'workflow_stage' => $toStage,
                'updated_by' => $actor->id,
            ])->save();

            $locked->statusHistories()->create([
                'changed_by' => $actor->id,
                'from_status' => $fromStatus->value,
                'to_status' => $toStatus->value,
                'notes' => $note,
            ]);

            $locked = $locked->refresh();
            $decision = $this->decisions->record($locked, $decisionType, $actor, $assignment, $note);
            $context = [
                'workflow_stage' => $toStage->value,
                'from_status' => $fromStatus->value,
                'to_status' => $toStatus->value,
                'assignment_id' => $assignment?->id,
                'decision_id' => $decision->id,
            ];
            $this->audit->record($locked, $activityType, $actor, $note, $context);

            if ($fromStage !== $toStage) {
                $this->audit->record(
                    $locked,
                    EditorialActivityType::WorkflowStageChanged,
                    $actor,
                    "Tahap editorial berubah dari {$fromStage->label()} menjadi {$toStage->label()}.",
                    [...$context, 'metadata' => ['from_stage' => $fromStage->value, 'to_stage' => $toStage->value]],
                );
            }

            return $locked;
        });
    }

    private function assertState(Insight $insight, array $statuses, array $stages): void
    {
        if (! in_array($insight->status, $statuses, true) || ! in_array($insight->workflow_stage, $stages, true)) {
            throw new LogicException("Transisi editorial tidak valid dari status {$insight->status->label()} pada tahap {$insight->workflow_stage->label()}.");
        }
    }

    private function authorizeOwner(Insight $insight, User $actor, string $permission): void
    {
        $this->authorizeAnyPermission($actor, [$permission]);

        if ((int) $insight->created_by !== (int) $actor->id) {
            throw new AuthorizationException('Writer hanya dapat memproses naskah miliknya sendiri.');
        }
    }

    private function authorizeSubmission(Insight $insight, User $actor): void
    {
        $this->authorizeAnyPermission($actor, ['submit insights']);

        if (! $actor->hasRole('super_admin') && (int) $insight->created_by !== (int) $actor->id) {
            throw new AuthorizationException('Hanya pemilik naskah atau Super Admin yang dapat mengirim draft.');
        }
    }

    private function authorizeAnyPermission(User $actor, array $permissions): void
    {
        if (! collect($permissions)->contains(fn (string $permission): bool => $actor->can($permission))) {
            throw new AuthorizationException('Izin untuk transisi editorial ini tidak tersedia.');
        }
    }
}
