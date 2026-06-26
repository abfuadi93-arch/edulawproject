<?php

namespace App\Policies;

class InsightCategoryPolicy extends ResourcePermissionPolicy
{
    protected bool $superAdminOnly = true;
}
