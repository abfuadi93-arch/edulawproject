<?php

namespace App\Policies;

use App\Enums\EditorialCommentType;
use App\Models\InsightEditorialNote;
use App\Models\User;

class InsightEditorialNotePolicy
{
    public function view(User $user, InsightEditorialNote $note): bool
    {
        $note->loadMissing('insight.activeEditorAssignment');

        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ((int) $note->insight->activeEditorAssignment?->editor_id === (int) $user->id) {
            return $user->can('view_assigned_editorial_insights');
        }

        return (int) $note->insight->created_by === (int) $user->id
            && $note->is_visible_to_writer
            && $note->type !== EditorialCommentType::Internal;
    }

    public function reply(User $user, InsightEditorialNote $note): bool
    {
        return $user->can('reply_editorial_comment')
            && $this->view($user, $note)
            && (int) $note->insight->created_by === (int) $user->id;
    }

    public function markAddressed(User $user, InsightEditorialNote $note): bool
    {
        return $user->can('mark_comment_addressed') && $this->reply($user, $note);
    }

    public function resolve(User $user, InsightEditorialNote $note): bool
    {
        return $user->can('resolve_editorial_comment')
            && ($user->hasRole('super_admin') || (int) $note->insight->activeEditorAssignment?->editor_id === (int) $user->id);
    }

    public function reopen(User $user, InsightEditorialNote $note): bool
    {
        return $user->can('reopen_editorial_comment')
            && ($user->hasRole('super_admin') || (int) $note->insight->activeEditorAssignment?->editor_id === (int) $user->id);
    }

    public function delete(User $user, InsightEditorialNote $note): bool
    {
        return false;
    }
}
