<?php

namespace App\Filament\Resources\Opportunities\Pages;

use App\Filament\Resources\Opportunities\OpportunityResource;
use App\Filament\Resources\Pages\CreateRecordAndReturn;
use Filament\Support\Enums\Alignment;

class CreateOpportunity extends CreateRecordAndReturn
{
    protected static string $resource = OpportunityResource::class;

    public function getTitle(): string
    {
        return 'Create Opportunity';
    }

    public function getSubheading(): ?string
    {
        return 'Tambahkan informasi peluang baru';
    }

    public function getFormActionsAlignment(): string|Alignment
    {
        return Alignment::End;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction()->label('Batal'),
            $this->getCreateAnotherFormAction()->label('Simpan & Buat Lagi'),
            $this->getCreateFormAction()->label('Simpan Opportunity'),
        ];
    }

    public function createAnother(): void
    {
        $this->create(another: true);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        $data['created_by'] = $user?->id;
        $data['updated_by'] = $user?->id;

        return OpportunityResource::prepareFormDataForPersistence($data);
    }
}
