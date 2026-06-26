<?php

namespace App\Policies;

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

        return $user->can('update all insights')
            || $user->can('review insights')
            || ($user->can('view insights') && (int) $record->created_by === (int) $user->id);
    }

    public function update(User $user, Model $record): bool
    {
        if (! $record instanceof Insight) {
            return false;
        }

        if ($user->can('update all insights')) {
            return true;
        }

        return $user->can('update own insights')
            && (int) $record->created_by === (int) $user->id
            && $record->status === 'draft';
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
            && $record->status === 'draft';
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete all insights');
    }
}
