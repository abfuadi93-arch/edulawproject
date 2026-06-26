<?php

namespace App\Policies;

class ProgramCategoryPolicy extends ResourcePermissionPolicy
{
    protected ?string $viewAnyPermission = 'view program categories';

    protected ?string $createPermission = 'create program categories';

    protected ?string $updatePermission = 'update program categories';

    protected ?string $deletePermission = 'delete program categories';
}
