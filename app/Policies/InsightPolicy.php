<?php

namespace App\Policies;

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

        return $this->isSuperAdmin($user)
            || ($user->canAccessAssignedEditorialInsights()
                && (int) $record->assigned_editor_id === (int) $user->id)
            || ($user->can('view insights') && (int) $record->created_by === (int) $user->id);
    }

    public function update(User $user, Model $record): bool
    {
        if (! $record instanceof Insight) {
            return false;
        }

        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if ((int) $record->assigned_editor_id === (int) $user->id
            && $user->can('review insights')
            && in_array($record->status->canonical(), [InsightStatus::Review, InsightStatus::Published], true)) {
            return true;
        }

        return $user->can('update own insights')
            && (int) $record->created_by === (int) $user->id
            && $record->status->canonical() === InsightStatus::Draft;
    }

    public function delete(User $user, Model $record): bool
    {
        if (! $record instanceof Insight) {
            return false;
        }

        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->can('delete own insights')
            && (int) $record->created_by === (int) $user->id
            && $record->status === InsightStatus::Draft;
    }

    public function deleteAny(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function submit(User $user, Insight $insight): bool
    {
        return $user->can('submit insights')
            && ($this->isSuperAdmin($user) || (int) $insight->created_by === (int) $user->id)
            && $insight->status->canonical() === InsightStatus::Draft;
    }

    public function assignEditor(User $user, Insight $insight): bool
    {
        return blank($insight->assigned_editor_id)
            && $this->isSuperAdmin($user)
            && $insight->status->canonical() !== InsightStatus::Published;
    }

    public function reassignEditor(User $user, Insight $insight): bool
    {
        return filled($insight->assigned_editor_id)
            && $this->isSuperAdmin($user)
            && $insight->status->canonical() !== InsightStatus::Published;
    }

    public function review(User $user, Insight $insight): bool
    {
        return $user->canAccessAssignedEditorialInsights()
            && (int) $insight->assigned_editor_id === (int) $user->id
            && $insight->status->canonical() === InsightStatus::Review;
    }

    public function viewHistory(User $user, Insight $insight): bool
    {
        return $user->can('view_status_history') && $this->view($user, $insight);
    }

    public function accessEditorialWorkspace(User $user, Insight $insight): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if ($insight->status === InsightStatus::Archived) {
            return false;
        }

        return $user->canAccessAssignedEditorialInsights()
            && (int) $insight->assigned_editor_id === (int) $user->id
            && ($user->hasAnyRole(['editor', 'Editor']) || $user->can('access_editorial_workspace'));
    }

    public function requestRevision(User $user, Insight $insight): bool
    {
        return $insight->status->canonical() === InsightStatus::Review
            && $user->can('request_revision')
            && ($this->isSuperAdmin($user) || (int) $insight->assigned_editor_id === (int) $user->id);
    }

    public function publish(User $user, Insight $insight): bool
    {
        return $insight->status->canonical() === InsightStatus::Review
            && ($user->can('publish_insight') || $user->can('publish insights'))
            && ($this->isSuperAdmin($user) || (int) $insight->assigned_editor_id === (int) $user->id);
    }

    public function archive(User $user, Insight $insight): bool
    {
        return $this->isSuperAdmin($user) && $insight->status !== InsightStatus::Archived;
    }

    private function isSuperAdmin(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'Super Admin', 'SuperAdmin']);
    }
}
