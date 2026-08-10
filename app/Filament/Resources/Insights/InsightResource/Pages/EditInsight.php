<?php

namespace App\Filament\Resources\Insights\InsightResource\Pages;

use App\Filament\Resources\Insights\InsightResource;
use App\Filament\Resources\Pages\EditRecordAndReturn;
use App\Services\InsightEditorialWorkflowService;
use App\Services\InsightFootnoteService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use LogicException;
use Throwable;

class EditInsight extends EditRecordAndReturn
{
    protected static string $resource = InsightResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('submit_for_review')
                ->label('Kirim untuk Review')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Kirim naskah untuk review?')
                ->modalDescription('Perubahan disimpan dan status berubah dari Draft menjadi Review. Naskah tidak dapat diedit Penulis sampai Editor meminta perbaikan.')
                ->modalSubmitActionLabel('Simpan & Kirim')
                ->visible(fn (): bool => InsightResource::canSubmitRecord($this->getRecord()))
                ->action(function (): void {
                    try {
                        $this->save(shouldRedirect: false, shouldSendSavedNotification: false);
                        app(InsightEditorialWorkflowService::class)->submit($this->getRecord()->refresh(), Auth::user());

                        Notification::make()
                            ->success()
                            ->title('Naskah berhasil dikirim')
                            ->body('Status naskah sekarang Review.')
                            ->send();

                        $this->redirect(InsightResource::getUrl('index'));
                    } catch (ValidationException $exception) {
                        Notification::make()
                            ->danger()
                            ->title('Naskah belum dapat dikirim')
                            ->body(collect($exception->errors())->flatten()->implode(' '))
                            ->persistent()
                            ->send();
                    } catch (Throwable $exception) {
                        report($exception);

                        $message = $exception instanceof AuthorizationException || $exception instanceof LogicException
                            ? $exception->getMessage()
                            : 'Terjadi kesalahan saat mengirim naskah. Muat ulang halaman lalu coba kembali.';

                        Notification::make()
                            ->danger()
                            ->title('Naskah gagal dikirim')
                            ->body($message)
                            ->persistent()
                            ->send();
                    }
                }),

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
        $data['status'] = $this->record->status->value;
        $data = InsightResource::prepareFormDataForPersistence($data);

        return $data;
    }

    protected function afterSave(): void
    {
        app(InsightFootnoteService::class)->sync($this->getRecord());
    }
}
