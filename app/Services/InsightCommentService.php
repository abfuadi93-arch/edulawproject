<?php

namespace App\Services;

use App\Enums\EditorialCommentField;
use App\Enums\EditorialCommentStatus;
use App\Enums\EditorialCommentType;
use App\Models\Insight;
use App\Models\InsightEditorialNote;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InsightCommentService
{
    public function createSectionComment(
        Insight $insight,
        User $actor,
        string $note,
        EditorialCommentField|string|null $fieldName = null,
        ?string $quotedText = null,
        EditorialCommentType|string $type = EditorialCommentType::Section,
        bool $isVisibleToWriter = true,
    ): InsightEditorialNote {
        $this->authorizeEditorOrManager($insight, $actor, 'create_section_comment');

        if (blank($note)) {
            throw ValidationException::withMessages(['note' => 'Komentar wajib diisi.']);
        }

        $type = $type instanceof EditorialCommentType ? $type : EditorialCommentType::from($type);
        $fieldName = is_string($fieldName) ? EditorialCommentField::from($fieldName) : $fieldName;

        if ($type === EditorialCommentType::Internal) {
            $isVisibleToWriter = false;
        }

        $comment = DB::transaction(fn (): InsightEditorialNote => $insight->editorialNotes()->create([
            'user_id' => $actor->id,
            'revision_id' => $insight->revisions()->latest('revision_number')->value('id'),
            'revision_round' => $insight->revision_round,
            'type' => $type->value,
            'field_name' => $fieldName?->value,
            'quoted_text' => filled($quotedText) ? trim($quotedText) : null,
            'status' => EditorialCommentStatus::Open->value,
            'note' => trim($note),
            'is_visible_to_writer' => $isVisibleToWriter,
        ]));

        app(InsightNotificationService::class)->notifyCommentCreated($comment);

        return $comment;
    }

    public function reply(InsightEditorialNote $comment, User $actor, string $reply): InsightEditorialNote
    {
        $comment->loadMissing('insight');

        if (! $actor->can('reply', $comment)) {
            throw new AuthorizationException('Writer tidak dapat membalas komentar ini.');
        }

        if (blank($reply)) {
            throw ValidationException::withMessages(['reply' => 'Balasan wajib diisi.']);
        }

        $response = DB::transaction(fn (): InsightEditorialNote => $comment->insight->editorialNotes()->create([
            'parent_id' => $comment->parent_id ?: $comment->id,
            'user_id' => $actor->id,
            'revision_id' => $comment->revision_id,
            'revision_round' => $comment->insight->revision_round,
            'type' => EditorialCommentType::AuthorResponse->value,
            'field_name' => $comment->field_name?->value,
            'status' => EditorialCommentStatus::Open->value,
            'note' => trim($reply),
            'is_visible_to_writer' => true,
        ]));

        app(InsightNotificationService::class)->notifyCommentCreated($response);

        return $response;
    }

    public function markAddressed(InsightEditorialNote $comment, User $actor): InsightEditorialNote
    {
        $comment->loadMissing('insight');

        if (! $actor->can('markAddressed', $comment)) {
            throw new AuthorizationException('Writer tidak dapat menandai komentar ini.');
        }

        if (! in_array($comment->status, [EditorialCommentStatus::Open, EditorialCommentStatus::Reopened], true)) {
            throw new \LogicException('Hanya komentar terbuka atau dibuka kembali yang dapat ditandai sudah ditanggapi.');
        }

        DB::transaction(fn () => $comment->forceFill([
            'status' => EditorialCommentStatus::Addressed,
            'addressed_by' => $actor->id,
            'addressed_at' => now(),
        ])->save());

        app(InsightNotificationService::class)->notifyCommentAddressed($comment->refresh());

        return $comment->refresh();
    }

    public function resolve(InsightEditorialNote $comment, User $actor): InsightEditorialNote
    {
        $comment->loadMissing('insight');

        if (! $actor->can('resolve', $comment)) {
            throw new AuthorizationException('Komentar ini tidak dapat diselesaikan oleh actor tersebut.');
        }

        if ($comment->status !== EditorialCommentStatus::Addressed) {
            throw new \LogicException('Hanya komentar yang sudah ditanggapi Writer yang dapat diselesaikan.');
        }

        DB::transaction(fn () => $comment->forceFill([
            'status' => EditorialCommentStatus::Resolved,
            'resolved_by' => $actor->id,
            'resolved_at' => now(),
        ])->save());

        return $comment->refresh();
    }

    public function reopen(InsightEditorialNote $comment, User $actor, string $reason): InsightEditorialNote
    {
        $comment->loadMissing('insight');

        if (! $actor->can('reopen', $comment)) {
            throw new AuthorizationException('Komentar ini tidak dapat dibuka kembali oleh actor tersebut.');
        }

        if (! in_array($comment->status, [EditorialCommentStatus::Addressed, EditorialCommentStatus::Resolved], true)) {
            throw new \LogicException('Hanya komentar addressed atau resolved yang dapat dibuka kembali.');
        }

        if (blank($reason)) {
            throw ValidationException::withMessages(['reason' => 'Alasan membuka kembali komentar wajib diisi.']);
        }

        DB::transaction(function () use ($comment, $actor, $reason): void {
            $comment->forceFill([
                'status' => EditorialCommentStatus::Reopened,
                'reopened_by' => $actor->id,
                'reopened_at' => now(),
            ])->save();

            $comment->insight->editorialNotes()->create([
                'parent_id' => $comment->id,
                'user_id' => $actor->id,
                'revision_id' => $comment->revision_id,
                'revision_round' => $comment->revision_round,
                'type' => EditorialCommentType::General->value,
                'field_name' => $comment->field_name?->value,
                'status' => EditorialCommentStatus::Resolved->value,
                'note' => 'Dibuka kembali: '.trim($reason),
                'is_visible_to_writer' => true,
            ]);
        });

        app(InsightNotificationService::class)->notifyCommentReopened($comment->refresh());

        return $comment->refresh();
    }

    private function authorizeEditorOrManager(Insight $insight, User $actor, string $permission): void
    {
        if (! $actor->can($permission)) {
            throw new AuthorizationException("Izin {$permission} diperlukan.");
        }

        $insight->loadMissing('activeEditorAssignment');

        if (! $actor->hasRole('super_admin') && (int) $insight->activeEditorAssignment?->editor_id !== (int) $actor->id) {
            throw new AuthorizationException('Editor hanya dapat mengomentari naskah yang ditugaskan kepadanya.');
        }
    }
}
