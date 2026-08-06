<?php

namespace App\Policies;

use App\Enums\EditorialWorkflowStage;
use App\Enums\InsightStatus;
use App\Models\Insight;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class InsightPolicy extends ResourcePermissionPolicy
{
    protected ?string $viewAnyPermission = 'view insights';

    protected ?string $createPermission = 'create insights';

    public function view(User $user, Model $record): bool
    {
        if (! $record instanceof Insight) {
            return false;
        }

        return $user->can('view_all_editorial_submissions')
            || $user->can('view_all_editorial_insights')
            || (($user->can('view_assigned_editorial_submissions') || $user->can('view_assigned_editorial_insights'))
                && $record->editorAssignments()->active()->where('editor_id', $user->id)->exists())
            || ($user->can('view insights') && (int) $record->created_by === (int) $user->id);
    }

    public function update(User $user, Model $record): bool
    {
        if (! $record instanceof Insight) {
            return false;
        }

        if ($user->can('view_all_editorial_insights')) {
            return true;
        }

        return $user->can('update own insights')
            && (int) $record->created_by === (int) $user->id
            && in_array($record->status, [InsightStatus::Draft, InsightStatus::RevisionRequested], true);
    }

    public function delete(User $user, Model $record): bool
    {
        if (! $record instanceof Insight) {
            return false;
        }

        if ($user->can('delete all insights')) {
            return true;
        }

        return $user->can('delete own insights')
            && (int) $record->created_by === (int) $user->id
            && $record->status === InsightStatus::Draft;
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete all insights');
    }

    public function submit(User $user, Insight $insight): bool
    {
        return $user->can('submit insights')
            && ($user->hasRole('super_admin') || (int) $insight->created_by === (int) $user->id)
            && $insight->status === InsightStatus::Draft;
    }

    public function assignEditor(User $user, Insight $insight): bool
    {
        return ! $insight->editorAssignments()->active()->exists()
            && $this->hasEditorialAssignmentAccess($user, 'assign_editor')
            && $insight->status === InsightStatus::Submitted
            && $insight->workflow_stage === EditorialWorkflowStage::Submission;
    }

    public function reassignEditor(User $user, Insight $insight): bool
    {
        return $insight->editorAssignments()->active()->exists()
            && $this->hasEditorialAssignmentAccess($user, 'reassign_editor')
            && in_array($insight->status, [InsightStatus::EditorAssigned, InsightStatus::InReview, InsightStatus::RevisionRequested, InsightStatus::Revised], true);
    }

    public function review(User $user, Insight $insight): bool
    {
        return ($user->can('view_assigned_editorial_submissions') || $user->can('view_assigned_editorial_insights'))
            && $insight->editorAssignments()->active()->where('editor_id', $user->id)->exists();
    }

    public function resubmit(User $user, Insight $insight): bool
    {
        return $user->can('resubmit_revision')
            && (int) $insight->created_by === (int) $user->id
            && $insight->status === InsightStatus::RevisionRequested;
    }

    public function viewHistory(User $user, Insight $insight): bool
    {
        return $user->can('view_status_history') && $this->view($user, $insight);
    }

    public function accessEditorialWorkspace(User $user, Insight $insight): bool
    {
        if ($user->hasAnyRole(['super_admin', 'Super Admin', 'SuperAdmin'])
            || $user->can('view_all_editorial_submissions')
            || $user->can('view_all_editorial_insights')) {
            return true;
        }

        if (! $user->can('access_editorial_workspace')) {
            return false;
        }

        if (($user->can('view_assigned_editorial_submissions') || $user->can('view_assigned_editorial_insights'))
            && $insight->editorAssignments()->active()->where('editor_id', $user->id)->exists()) {
            return true;
        }

        return ($user->can('view_own_editorial_submissions') || $user->can('view insights'))
            && (int) $insight->created_by === (int) $user->id;
    }

    public function publishApproved(User $user, Insight $insight): bool
    {
        return $user->can('publish_approved_insight')
            && $insight->status === InsightStatus::Approved
            && $insight->workflow_stage === EditorialWorkflowStage::FinalApproval;
    }

    private function hasEditorialAssignmentAccess(User $user, string $permission): bool
    {
        return $user->hasAnyRole(['super_admin', 'Super Admin', 'SuperAdmin'])
            || $user->can($permission);
    }
}
