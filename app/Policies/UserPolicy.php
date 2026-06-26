<?php

namespace App\Policies;

class UserPolicy extends ResourcePermissionPolicy
{
    protected bool $superAdminOnly = true;
}
