<?php

namespace App\Policies;

use App\Models\InsightRevision;
use App\Models\User;

class InsightRevisionPolicy
{
    public function view(User $user, InsightRevision $revision): bool
    {
        $revision->loadMissing('insight.activeEditorAssignment');

        return $user->can('view_revision_history') && (
            $user->hasRole('super_admin')
            || (int) $revision->insight->activeEditorAssignment?->editor_id === (int) $user->id
            || (int) $revision->insight->created_by === (int) $user->id
        );
    }

    public function compare(User $user, InsightRevision $revision): bool
    {
        return $user->can('compare_insight_revisions') && $this->view($user, $revision);
    }

    public function delete(User $user, InsightRevision $revision): bool
    {
        return false;
    }
}
