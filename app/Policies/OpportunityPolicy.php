<?php

namespace App\Policies;

class OpportunityPolicy extends ResourcePermissionPolicy
{
    protected ?string $viewAnyPermission = 'view opportunities';

    protected ?string $createPermission = 'create opportunities';

    protected ?string $updatePermission = 'update opportunities';

    protected ?string $deletePermission = 'delete opportunities';
}
