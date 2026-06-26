<?php

namespace App\Policies;

class AuthorPolicy extends ResourcePermissionPolicy
{
    protected ?string $viewAnyPermission = 'view authors';

    protected ?string $createPermission = 'create authors';

    protected ?string $updatePermission = 'update authors';

    protected ?string $deletePermission = 'delete authors';
}
