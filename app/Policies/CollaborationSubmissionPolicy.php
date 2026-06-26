<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CollaborationSubmissionPolicy extends ResourcePermissionPolicy
{
    protected ?string $viewAnyPermission = 'view collaboration submissions';

    protected ?string $updatePermission = 'update collaboration submissions';

    public function create(User $user): bool
    {
        return false;
    }

    public function delete(User $user, Model $record): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
