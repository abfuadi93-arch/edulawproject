<?php

namespace App\Filament\Resources\ProgramResource\Pages;

use App\Filament\Resources\Pages\CreateRecordAndReturn;
use App\Filament\Resources\ProgramResource;
use Filament\Actions\Action;
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
            Action::make('save_draft')->label('Simpan Draft')->color('gray')
                ->action(fn () => $this->createWithStatus('draft')),
            Action::make('publish_program')->label('Publikasikan')
                ->action(fn () => $this->createWithStatus('published')),
        ];
    }

    public function createWithStatus(string $status): void
    {
        abort_unless(in_array($status, ['draft', 'published'], true), 422);
        $this->data['publication_status'] = $status;
        $this->create();
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
