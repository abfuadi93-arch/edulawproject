<?php

namespace App\Filament\Resources\CollaborationSubmissions\Pages;

use App\Filament\Resources\CollaborationSubmissions\CollaborationSubmissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCollaborationSubmissions extends ListRecords
{
    protected static string $resource = CollaborationSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
