<?php

namespace App\Policies;

class ProgramPolicy extends ResourcePermissionPolicy
{
    protected ?string $viewAnyPermission = 'view programs';

    protected ?string $createPermission = 'create programs';

    protected ?string $updatePermission = 'update programs';

    protected ?string $deletePermission = 'delete programs';
}
