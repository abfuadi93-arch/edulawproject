<?php

namespace App\Filament\Resources\Insights\InsightResource\Pages;

use App\Filament\Resources\Insights\InsightResource;
use App\Filament\Resources\Pages\EditRecordAndReturn;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class EditInsight extends EditRecordAndReturn
{
    protected static string $resource = InsightResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Pratinjau')
                ->icon('heroicon-o-eye')
                ->url(fn (): ?string => filled($this->record?->slug)
                    ? route('insights.show', $this->record->slug)
                    : null)
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled($this->record?->slug)),

            ActionGroup::make([
                DeleteAction::make()
                    ->label('Hapus Editorial'),
            ])
                ->label('Lainnya')
                ->icon('heroicon-o-ellipsis-vertical')
                ->color('gray'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['status'] = InsightResource::normalizeStatusForDisplay($data['status'] ?? null);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();

        $data['updated_by'] = $user?->id;
        $data = InsightResource::prepareFormDataForPersistence($data);

        if (! $user) {
            throw new AuthorizationException;
        }

        if (! InsightResource::canManageEditorialWorkflow()) {
            if ((int) $this->record->created_by !== (int) $user->id || $this->record->status !== 'draft') {
                throw new AuthorizationException;
            }

            $nextStatus = $data['status'] ?? $this->record->status;

            if (! in_array($nextStatus, ['draft', 'reviewed'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Writer hanya dapat menyimpan Draft atau Reviewed.',
                ]);
            }

            if ($nextStatus === 'reviewed' && ! $user->can('submit insights')) {
                throw new AuthorizationException;
            }

            $data['published_at'] = null;
            $data['featured'] = false;
            $data['editor_pick'] = false;
            $data['sort_order'] = 0;
            $data['reviewed_by'] = null;
            $data['reviewed_at'] = null;

            return $data;
        }

        $nextStatus = $data['status'] ?? $this->record->status;

        if (in_array($nextStatus, ['reviewed', 'published'], true) && $nextStatus !== $this->record->status) {
            $data['reviewed_by'] = $user->id;
            $data['reviewed_at'] = now();
        }

        if ($nextStatus === 'published' && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
