<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ContactMessagePolicy extends ResourcePermissionPolicy
{
    protected ?string $viewAnyPermission = 'view contact messages';

    protected ?string $updatePermission = 'update contact messages';

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
