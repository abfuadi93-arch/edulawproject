<?php

namespace App\Policies;

class PublicationTypePolicy extends ResourcePermissionPolicy
{
    protected bool $superAdminOnly = true;
}
