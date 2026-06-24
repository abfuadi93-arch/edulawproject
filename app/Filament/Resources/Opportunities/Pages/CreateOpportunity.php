<?php

namespace App\Filament\Resources\Opportunities\Pages;

use App\Filament\Resources\Opportunities\OpportunityResource;
use App\Filament\Resources\Pages\CreateRecordAndReturn;

class CreateOpportunity extends CreateRecordAndReturn
{
    protected static string $resource = OpportunityResource::class;
}
