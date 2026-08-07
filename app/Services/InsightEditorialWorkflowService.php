<?php

namespace App\Services;

use App\Enums\InsightStatus;
use App\Models\Insight;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use LogicException;

class InsightEditorialWorkflowService
{
    public function submit(Insight $insight, User $actor): Insight
    {
        Gate::forUser($actor)->authorize('submit', $insight);
        $this->validateSubmissionCompleteness($insight);

        $wasPreviouslySubmitted = filled($insight->submitted_at);
        $didTransition = false;

        $insight = DB::transaction(function () use ($insight, $actor, $wasPreviouslySubmitted, &$didTransition): Insight {
            $locked = $this->lock($insight);

            // A double click or a delayed Livewire request may still carry a
            // Draft snapshot after the first request has moved it to Review.
            // Treat that as a successful no-op instead of recording twice.
            if ($locked->status->canonical() === InsightStatus::Review) {
                return $locked->refresh();
            }

            $this->assertStatus($locked, InsightStatus::Draft);
            $didTransition = true;

            return $this->transition(
                $locked,
                $actor,
                InsightStatus::Review,
                $wasPreviouslySubmitted ? 'resubmitted_for_review' : 'submitted_for_review',
                $wasPreviouslySubmitted
                    ? 'Penulis mengirim ulang naskah untuk review.'
                    : 'Penulis mengirim naskah untuk review.',
                [
                    'submitted_at' => now(),
                    'updated_by' => $actor->id,
                    'archived_at' => null,
                ],
            );
        });

        if ($didTransition) {
            app(InsightNotificationService::class)->notifySubmission($insight, $wasPreviouslySubmitted);
        }

        return $insight;
    }

    public function assignEditor(Insight $insight, User $editor, User $actor): Insight
    {
        $ability = filled($insight->assigned_editor_id) ? 'reassignEditor' : 'assignEditor';
        Gate::forUser($actor)->authorize($ability, $insight);
        $this->validateEditor($editor);

        $previousEditorId = $insight->assigned_editor_id;

        $insight = DB::transaction(function () use ($insight, $editor, $actor, $previousEditorId): Insight {
            $locked = $this->lock($insight);
            $event = filled($previousEditorId) ? 'editor_changed' : 'editor_assigned';
            $description = filled($previousEditorId)
                ? "Editor diganti menjadi {$editor->name}."
                : "{$editor->name} ditugaskan sebagai Editor.";

            $locked->forceFill([
                'assigned_editor_id' => $editor->id,
                'assigned_by' => $actor->id,
                'assigned_at' => now(),
                'updated_by' => $actor->id,
            ])->save();

            $this->recordActivity($locked, $actor, $event, $description, metadata: [
                'previous_editor_id' => $previousEditorId,
                'editor_id' => $editor->id,
            ]);

            return $locked->refresh();
        });

        app(InsightNotificationService::class)->notifyAssignment($insight, filled($previousEditorId));

        return $insight;
    }

    public function requestRevision(Insight $insight, User $actor, string $note): Insight
    {
        Gate::forUser($actor)->authorize('requestRevision', $insight);

        if (blank($note)) {
            throw ValidationException::withMessages(['editor_notes' => 'Catatan untuk Penulis wajib diisi.']);
        }

        $insight = DB::transaction(function () use ($insight, $actor, $note): Insight {
            $locked = $this->lock($insight);
            $this->assertStatus($locked, InsightStatus::Review);
            $note = trim($note);

            $locked->editorialNotes()->create([
                'user_id' => $actor->id,
                'revision_round' => 0,
                'type' => 'revision_request',
                'status' => 'open',
                'note' => $note,
                'is_visible_to_writer' => true,
            ]);

            return $this->transition(
                $locked,
                $actor,
                InsightStatus::Draft,
                'revision_requested',
                'Editor meminta perbaikan dan mengembalikan naskah ke Draft.',
                [
                    'editor_notes' => $note,
                    'revision_requested_at' => now(),
                    'updated_by' => $actor->id,
                ],
            );
        });

        app(InsightNotificationService::class)->notifyRevisionRequested($insight);

        return $insight;
    }

    public function addEditorialNote(Insight $insight, User $actor, string $note): Insight
    {
        Gate::forUser($actor)->authorize('review', $insight);

        if (blank($note)) {
            throw ValidationException::withMessages(['editor_notes' => 'Catatan untuk Penulis wajib diisi.']);
        }

        $insight->forceFill([
            'editor_notes' => trim($note),
            'updated_by' => $actor->id,
        ])->save();

        $insight->editorialNotes()->create([
            'user_id' => $actor->id,
            'revision_round' => 0,
            'type' => 'note',
            'status' => 'open',
            'note' => trim($note),
            'is_visible_to_writer' => true,
        ]);

        $this->recordActivity($insight, $actor, 'editor_note_saved', 'Editor menyimpan catatan untuk Penulis.');

        return $insight->refresh();
    }

    public function publish(Insight $insight, User $actor): Insight
    {
        Gate::forUser($actor)->authorize('publish', $insight);
        $this->validateSubmissionCompleteness($insight);

        $insight = DB::transaction(function () use ($insight, $actor): Insight {
            $locked = $this->lock($insight);
            $this->assertStatus($locked, InsightStatus::Review);
            $publishedAt = $locked->published_at ?: now();
            $description = $publishedAt->isFuture()
                ? 'Editor menjadwalkan artikel terbit pada '.$publishedAt->copy()->timezone(config('edulaw.timezone'))->translatedFormat('d M Y, H:i').' WIB.'
                : 'Editor menerbitkan artikel.';

            return $this->transition(
                $locked,
                $actor,
                InsightStatus::Published,
                'published',
                $description,
                [
                    'reviewed_by' => $actor->id,
                    'reviewed_at' => now(),
                    'published_at' => $publishedAt,
                    'updated_by' => $actor->id,
                    'archived_at' => null,
                ],
            );
        });

        app(InsightNotificationService::class)->notifyPublication($insight);

        return $insight;
    }

    public function archive(Insight $insight, User $actor): Insight
    {
        Gate::forUser($actor)->authorize('archive', $insight);

        if ($insight->status === InsightStatus::Archived) {
            throw new LogicException('Insight sudah diarsipkan.');
        }

        return DB::transaction(function () use ($insight, $actor): Insight {
            $locked = $this->lock($insight);

            return $this->transition(
                $locked,
                $actor,
                InsightStatus::Archived,
                'archived',
                'Super Admin mengarsipkan Insight.',
                [
                    'archived_at' => now(),
                    'updated_by' => $actor->id,
                ],
            );
        });
    }

    public function recordDraftCreated(Insight $insight, User $actor): void
    {
        $this->recordActivity($insight, $actor, 'draft_created', 'Penulis membuat draft.');
    }

    private function transition(
        Insight $insight,
        User $actor,
        InsightStatus $to,
        string $event,
        string $description,
        array $attributes = [],
    ): Insight {
        $from = $insight->status;

        $insight->forceFill([
            ...$attributes,
            'status' => $to,
        ])->save();

        $insight->statusHistories()->create([
            'changed_by' => $actor->id,
            'from_status' => $from->value,
            'to_status' => $to->value,
            'notes' => $description,
        ]);

        $this->recordActivity($insight, $actor, $event, $description, $from, $to);

        return $insight->refresh();
    }

    private function recordActivity(
        Insight $insight,
        User $actor,
        string $event,
        string $description,
        ?InsightStatus $from = null,
        ?InsightStatus $to = null,
        ?array $metadata = null,
    ): void {
        $insight->editorialActivities()->create([
            'actor_id' => $actor->id,
            'event' => $event,
            'from_status' => $from?->value,
            'to_status' => $to?->value,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    private function assertStatus(Insight $insight, InsightStatus $expected): void
    {
        if ($insight->status->canonical() !== $expected) {
            throw new LogicException("Transisi tidak valid dari status {$insight->status->label()}.");
        }
    }

    private function validateEditor(User $editor): void
    {
        if (! $editor->is_active || ! $editor->hasAnyRole(['editor', 'Editor'])) {
            throw ValidationException::withMessages([
                'editor_id' => 'Pilih pengguna aktif dengan role Editor.',
            ]);
        }
    }

    private function validateSubmissionCompleteness(Insight $insight): void
    {
        $errors = collect([
            'title' => blank($insight->title) ? 'Judul wajib diisi.' : null,
            'insight_category_id' => blank($insight->insight_category_id) ? 'Kategori wajib dipilih.' : null,
            'authors' => ! $insight->authors()->exists() ? 'Minimal satu penulis wajib dipilih.' : null,
            'excerpt' => blank($insight->excerpt) ? 'Excerpt wajib diisi.' : null,
            'content' => blank($insight->content) ? 'Isi artikel wajib diisi.' : null,
            'cover_image' => blank($insight->cover_image) ? 'Cover wajib diisi.' : null,
            'slug' => blank($insight->slug) ? 'Slug wajib diisi.' : null,
        ])->filter()->all();

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function lock(Insight $insight): Insight
    {
        return Insight::query()->lockForUpdate()->findOrFail($insight->getKey());
    }
}
