<?php

namespace App\Policies;

class RolePolicy extends ResourcePermissionPolicy
{
    protected bool $superAdminOnly = true;
}
