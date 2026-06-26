<?php

namespace App\Policies;

class PublicationPolicy extends ResourcePermissionPolicy
{
    protected ?string $viewAnyPermission = 'view publications';

    protected ?string $createPermission = 'create publications';

    protected ?string $updatePermission = 'update publications';

    protected ?string $deletePermission = 'delete publications';
}
