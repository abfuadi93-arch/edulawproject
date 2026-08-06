<?php

namespace App\Services;

use App\Enums\EditorialCommentStatus;
use App\Enums\InsightStatus;
use App\Models\Insight;
use App\Models\InsightEditorialNote;
use App\Models\User;
use App\Services\Editorial\InsightAssignmentService;
use App\Services\Editorial\InsightNotificationService as EditorialNotificationService;
use App\Services\Editorial\InsightWorkflowService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

class InsightEditorialWorkflowService
{
    public function submit(Insight $insight, User $actor): Insight
    {
        $this->authorizeSubmission($insight, $actor);
        $this->assertStatus($insight, InsightStatus::Draft);
        $this->validateSubmissionCompleteness($insight);

        $insight = app(InsightWorkflowService::class)->submit($insight, $actor, 'Naskah dikirim untuk diperiksa.');

        app(InsightRevisionService::class)->createInitialSnapshot($insight, $actor);
        app(InsightNotificationService::class)->notifySubmission($insight);

        return $insight;
    }

    public function assignEditor(
        Insight $insight,
        User $editor,
        User $actor,
        Carbon|string|null $deadline = null,
        ?string $assignmentNote = null,
    ): Insight {
        $assignments = app(InsightAssignmentService::class);
        $activeAssignment = $assignments->getActiveAssignment($insight);

        $assignment = $activeAssignment
            ? $assignments->reassignEditor(
                $insight,
                $editor,
                $actor,
                (string) $assignmentNote,
                $deadline,
                $assignmentNote,
            )
            : $assignments->assignEditor($insight, $editor, $actor, $deadline, $assignmentNote);

        return $assignment->insight->refresh();
    }

    public function startReview(Insight $insight, User $actor): Insight
    {
        $assignment = app(InsightAssignmentService::class)->getActiveAssignment($insight)
            ?? throw new LogicException('Assignment aktif tidak ditemukan.');

        return app(InsightAssignmentService::class)
            ->startAssignment($assignment, $actor)
            ->insight
            ->refresh();
    }

    public function addEditorialNote(
        Insight $insight,
        User $actor,
        string $note,
        bool $isVisibleToWriter = true,
        string $type = 'general',
    ): InsightEditorialNote {
        $this->authorizeAssignedEditorOrManager($insight, $actor, 'add_editorial_note');
        $this->assertStatus($insight, [
            InsightStatus::EditorAssigned,
            InsightStatus::InReview,
            InsightStatus::RevisionRequested,
            InsightStatus::Revised,
        ]);

        if (blank($note)) {
            throw ValidationException::withMessages(['note' => 'Catatan wajib diisi.']);
        }

        $comment = DB::transaction(fn (): InsightEditorialNote => $this->createNote(
            $insight,
            $actor,
            trim($note),
            $type,
            $isVisibleToWriter,
            status: EditorialCommentStatus::Resolved,
        ));

        app(InsightNotificationService::class)->notifyCommentCreated($comment);

        return $comment;
    }

    public function requestRevision(
        Insight $insight,
        User $actor,
        string $note,
        Carbon|string|null $deadline = null,
    ): Insight {
        $this->authorizeAssignedEditor($insight, $actor, 'request_revision');
        $this->assertStatus($insight, InsightStatus::InReview);

        if (blank($note)) {
            throw ValidationException::withMessages(['note' => 'Catatan perbaikan wajib diisi.']);
        }

        $insight = DB::transaction(function () use ($insight, $actor, $note): Insight {
            $locked = $this->lock($insight);
            $this->assertStatus($locked, InsightStatus::InReview);
            $this->createNote($locked, $actor, trim($note), 'revision_request', true);

            $assignment = app(InsightAssignmentService::class)->getActiveAssignment($locked)
                ?? throw new LogicException('Assignment aktif tidak ditemukan.');

            return app(InsightWorkflowService::class)->moveToAuthorRevision($locked, $actor, $assignment, trim($note));
        });

        app(InsightDeadlineService::class)->completeEditorDeadline($insight);
        $insight = app(InsightDeadlineService::class)->setWriterDeadline($insight, $actor, $deadline ?: now()->addDays(3));
        app(InsightNotificationService::class)->notifyRevisionRequested($insight->load('creator'));

        return $insight;
    }

    public function resubmit(Insight $insight, User $actor, string $changeSummary): Insight
    {
        $this->authorizeOwner($insight, $actor, 'resubmit_revision');
        $this->assertStatus($insight, InsightStatus::RevisionRequested);

        if (blank($changeSummary)) {
            throw ValidationException::withMessages(['change_summary' => 'Ringkasan perubahan wajib diisi.']);
        }

        $this->validateSubmissionCompleteness($insight);

        if (! app(InsightRevisionService::class)->hasMeaningfulChanges($insight)) {
            throw ValidationException::withMessages([
                'change_summary' => 'Tidak ada perubahan bermakna pada naskah dibandingkan versi terakhir.',
            ]);
        }

        $insight = DB::transaction(function () use ($insight, $actor, $changeSummary): Insight {
            $locked = $this->lock($insight);
            $this->assertStatus($locked, InsightStatus::RevisionRequested);
            $nextRound = $locked->revision_round + 1;
            $locked->editorialNotes()
                ->whereNull('parent_id')
                ->where('type', 'revision_request')
                ->whereIn('status', ['open', 'reopened'])
                ->update([
                    'status' => 'addressed',
                    'addressed_by' => $actor->id,
                    'addressed_at' => now(),
                ]);

            $assignment = app(InsightAssignmentService::class)->getActiveAssignment($locked)
                ?? throw new LogicException('Assignment aktif tidak ditemukan.');

            return app(InsightWorkflowService::class)->returnToEditorialReview(
                $locked,
                $actor,
                $assignment,
                trim($changeSummary),
                $nextRound,
            );
        });

        app(InsightRevisionService::class)->createRevisionSnapshot($insight, $actor, $changeSummary);
        app(InsightDeadlineService::class)->completeWriterDeadline($insight);
        app(InsightNotificationService::class)->notifyRevisionSubmitted($insight->load('assignedEditor'));

        return $insight;
    }

    public function approve(Insight $insight, User $actor, ?string $note = null, ?string $overrideReason = null): Insight
    {
        $this->authorizeAssignedEditorOrManager($insight, $actor, 'approve_insight');
        $this->assertStatus($insight, InsightStatus::InReview);

        $assignment = app(InsightAssignmentService::class)->getActiveAssignment($insight)
            ?? throw new LogicException('Assignment aktif tidak ditemukan.');
        $insight = app(InsightWorkflowService::class)->moveToFinalApproval($insight, $actor, $assignment, $note);
        app(InsightAssignmentService::class)->completeAssignment($assignment, $actor);

        app(InsightDeadlineService::class)->completeEditorDeadline($insight);
        app(EditorialNotificationService::class)->insightApproved($insight->load('creator'), $assignment->refresh(), $actor);

        return $insight;
    }

    public function reject(Insight $insight, User $actor, string $reason): Insight
    {
        $this->authorizeAssignedEditor($insight, $actor, 'reject_insight');
        $this->assertStatus($insight, [
            InsightStatus::EditorAssigned,
            InsightStatus::InReview,
            InsightStatus::Revised,
        ]);

        if (blank($reason)) {
            throw ValidationException::withMessages(['rejection_reason' => 'Alasan wajib diisi.']);
        }

        $assignment = app(InsightAssignmentService::class)->getActiveAssignment($insight)
            ?? throw new LogicException('Assignment aktif tidak ditemukan.');
        $insight = app(InsightWorkflowService::class)->reject($insight, $actor, $assignment, trim($reason));
        app(InsightAssignmentService::class)->completeAssignment($assignment, $actor);

        app(InsightDeadlineService::class)->completeEditorDeadline($insight);
        app(InsightNotificationService::class)->notifyRejection($insight->load('creator'));

        return $insight;
    }

    public function publish(Insight $insight, User $actor): Insight
    {
        if (! $actor->can('publish_approved_insight') && ! $actor->can('publish_insight')) {
            throw new AuthorizationException('Izin menerbitkan naskah yang disetujui diperlukan.');
        }

        $this->assertStatus($insight, InsightStatus::Approved);
        $this->validateSubmissionCompleteness($insight);

        $assignment = $insight->editorAssignments()->latest('id')->first();

        if (! $actor->hasRole('super_admin') && (int) $assignment?->editor_id !== (int) $actor->id) {
            throw new AuthorizationException('Hanya Editor yang ditugaskan atau Super Admin yang dapat menerbitkan naskah.');
        }

        if (app(InsightRevisionService::class)->hasMeaningfulChanges($insight)) {
            app(InsightRevisionService::class)->createRevisionSnapshot($insight, $actor, 'Perubahan setelah approval sebelum publikasi.');
        }

        $insight = app(InsightWorkflowService::class)->moveToPublication($insight, $actor, $assignment);

        app(EditorialNotificationService::class)->insightPublished($insight->load('creator'), $assignment, $actor);

        return $insight;
    }

    public function archive(Insight $insight, User $actor, ?string $note = null): Insight
    {
        $this->authorizePermission($actor, 'archive_insight');

        $assignment = $insight->editorAssignments()->latest('id')->first();

        if (! $actor->hasRole('super_admin') && (int) $assignment?->editor_id !== (int) $actor->id) {
            throw new AuthorizationException('Hanya Editor yang ditugaskan atau Super Admin yang dapat mengarsipkan naskah.');
        }

        if ($insight->status === InsightStatus::Archived) {
            throw new LogicException('Naskah sudah diarsipkan.');
        }

        return app(InsightWorkflowService::class)->archive(
            $insight,
            $actor,
            $assignment,
            $note,
        );
    }

    private function lock(Insight $insight): Insight
    {
        return Insight::query()->lockForUpdate()->findOrFail($insight->getKey());
    }

    private function createNote(
        Insight $insight,
        User $actor,
        string $note,
        string $type,
        bool $visible,
        ?int $revisionRound = null,
        EditorialCommentStatus $status = EditorialCommentStatus::Open,
    ): InsightEditorialNote {
        return $insight->editorialNotes()->create([
            'user_id' => $actor->id,
            'revision_id' => $insight->revisions()->latest('revision_number')->value('id'),
            'revision_round' => $revisionRound ?? $insight->revision_round,
            'type' => $type,
            'status' => $status->value,
            'note' => $note,
            'is_visible_to_writer' => $visible,
            'resolved_by' => $status === EditorialCommentStatus::Resolved ? $actor->id : null,
            'resolved_at' => $status === EditorialCommentStatus::Resolved ? now() : null,
        ]);
    }

    private function assertStatus(Insight $insight, InsightStatus|array $allowed): void
    {
        $allowed = is_array($allowed) ? $allowed : [$allowed];

        if (! in_array($insight->status, $allowed, true)) {
            $labels = collect($allowed)->map->label()->join(', ');

            throw new LogicException("Transisi tidak valid dari status {$insight->status->label()}. Status yang diizinkan: {$labels}.");
        }
    }

    private function authorizePermission(User $actor, string $permission): void
    {
        if (! $actor->can($permission)) {
            throw new AuthorizationException("Izin {$permission} diperlukan.");
        }
    }

    private function authorizeSubmission(Insight $insight, User $actor): void
    {
        $this->authorizePermission($actor, 'submit insights');

        if (! $actor->hasRole('super_admin') && (int) $insight->created_by !== (int) $actor->id) {
            throw new AuthorizationException('Hanya pemilik naskah atau Super Admin yang dapat mengirim draft.');
        }
    }

    private function authorizeOwner(Insight $insight, User $actor, string $permission): void
    {
        $this->authorizePermission($actor, $permission);

        if ((int) $insight->created_by !== (int) $actor->id) {
            throw new AuthorizationException('Writer hanya dapat memproses naskah miliknya sendiri.');
        }
    }

    private function authorizeAssignedEditor(Insight $insight, User $actor, string $permission): void
    {
        $this->authorizePermission($actor, $permission);

        $insight->loadMissing('activeEditorAssignment');

        if ((int) $insight->activeEditorAssignment?->editor_id !== (int) $actor->id) {
            throw new AuthorizationException('Editor hanya dapat memproses naskah yang ditugaskan kepadanya.');
        }
    }

    private function authorizeAssignedEditorOrManager(Insight $insight, User $actor, string $permission): void
    {
        $this->authorizePermission($actor, $permission);

        $insight->loadMissing('activeEditorAssignment');

        if (! $actor->hasRole('super_admin') && (int) $insight->activeEditorAssignment?->editor_id !== (int) $actor->id) {
            throw new AuthorizationException('Naskah ini tidak ditugaskan kepada Editor tersebut.');
        }
    }

    private function validateSubmissionCompleteness(Insight $insight): void
    {
        $errors = collect([
            'title' => blank($insight->title) ? 'Judul wajib diisi.' : null,
            'insight_category_id' => blank($insight->insight_category_id) ? 'Kategori wajib dipilih.' : null,
            'authors' => ! $insight->authors()->exists() ? 'Minimal satu penulis wajib dipilih.' : null,
            'excerpt' => blank($insight->excerpt) ? 'Ringkasan wajib diisi.' : null,
            'content' => blank($insight->content) ? 'Isi artikel wajib diisi.' : null,
            'cover_image' => blank($insight->cover_image) ? 'Gambar utama wajib diisi.' : null,
            'slug' => blank($insight->slug) ? 'Slug wajib diisi.' : null,
        ])->filter()->all();

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
