<?php

namespace App\Policies;

class PermissionPolicy extends ResourcePermissionPolicy
{
    protected bool $superAdminOnly = true;
}
