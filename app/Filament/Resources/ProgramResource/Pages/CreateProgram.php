<?php

namespace App\Filament\Resources\ProgramResource\Pages;

use App\Filament\Resources\Pages\CreateRecordAndReturn;
use App\Filament\Resources\ProgramResource;
use Filament\Support\Enums\Alignment;

class CreateProgram extends CreateRecordAndReturn
{
    protected static string $resource = ProgramResource::class;

    public function getTitle(): string
    {
        return 'Create Program';
    }

    public function getFormActionsAlignment(): string|Alignment
    {
        return Alignment::End;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction()->label('Batal')->extraAttributes(['class' => 'edulaw-program-cancel']),
            $this->getCreateAnotherFormAction()->label('Simpan & Buat Lagi'),
            $this->getCreateFormAction()->label('Simpan Program'),
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
        $data['sort_order'] = ProgramResource::nextSortOrder();

        return ProgramResource::prepareFormDataForPersistence($data);
    }
}
