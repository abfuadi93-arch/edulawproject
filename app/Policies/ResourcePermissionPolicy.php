<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class ResourcePermissionPolicy
{
    protected ?string $viewAnyPermission = null;

    protected ?string $createPermission = null;

    protected ?string $updatePermission = null;

    protected ?string $deletePermission = null;

    protected bool $superAdminOnly = false;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, $this->viewAnyPermission);
    }

    public function view(User $user, Model $record): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, $this->createPermission);
    }

    public function update(User $user, Model $record): bool
    {
        return $this->allows($user, $this->updatePermission);
    }

    public function delete(User $user, Model $record): bool
    {
        return $this->allows($user, $this->deletePermission);
    }

    public function deleteAny(User $user): bool
    {
        return $this->allows($user, $this->deletePermission);
    }

    public function restore(User $user, Model $record): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, Model $record): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    protected function allows(User $user, ?string $permission): bool
    {
        if ($this->superAdminOnly) {
            return $user->hasRole('super_admin');
        }

        return filled($permission) && $user->can($permission);
    }
}
