<?php

namespace App\Policies;

class TagPolicy extends ResourcePermissionPolicy
{
    protected ?string $viewAnyPermission = 'view tags';

    protected ?string $createPermission = 'create tags';

    protected ?string $updatePermission = 'update tags';

    protected ?string $deletePermission = 'delete tags';
}
