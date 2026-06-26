<?php

namespace App\Policies;

class MultimediaPolicy extends ResourcePermissionPolicy
{
    protected ?string $viewAnyPermission = 'view multimedia';

    protected ?string $createPermission = 'create multimedia';

    protected ?string $updatePermission = 'update multimedia';

    protected ?string $deletePermission = 'delete multimedia';
}
