<?php

namespace App\Policies;

use App\Enums\EditorAssignmentStatus;
use App\Models\InsightEditorAssignment;
use App\Models\User;

class InsightEditorAssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_all_editorial_submissions')
            || $user->can('view_assigned_editorial_submissions');
    }

    public function view(User $user, InsightEditorAssignment $assignment): bool
    {
        return $user->can('view_all_editorial_submissions')
            || ($user->can('view_assigned_editorial_submissions') && (int) $assignment->editor_id === (int) $user->id);
    }

    public function accept(User $user, InsightEditorAssignment $assignment): bool
    {
        return $user->can('accept_editor_assignment')
            && (int) $assignment->editor_id === (int) $user->id
            && $assignment->status === EditorAssignmentStatus::Assigned;
    }

    public function start(User $user, InsightEditorAssignment $assignment): bool
    {
        return $user->can('start_editorial_review')
            && (int) $assignment->editor_id === (int) $user->id
            && $assignment->status->isActive();
    }

    public function complete(User $user, InsightEditorAssignment $assignment): bool
    {
        return $user->can('complete_editor_assignment')
            && (int) $assignment->editor_id === (int) $user->id
            && $assignment->status->isActive();
    }

    public function cancel(User $user, InsightEditorAssignment $assignment): bool
    {
        return $user->can('cancel_editor_assignment') && $assignment->status->isActive();
    }
}
