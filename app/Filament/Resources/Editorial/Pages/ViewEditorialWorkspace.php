<?php

namespace App\Filament\Resources\Editorial\Pages;

use App\Enums\InsightStatus;
use App\Filament\Resources\Editorial\EditorialResource;
use App\Models\Insight;
use App\Models\User;
use App\Services\InsightEditorialWorkflowService;
use App\Services\InsightFootnoteService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Throwable;

class ViewEditorialWorkspace extends EditRecord
{
    protected static string $resource = EditorialResource::class;

    protected static ?string $title = 'Workspace Editor';

    protected function authorizeAccess(): void
    {
        Gate::authorize('accessEditorialWorkspace', $this->getRecord());
    }

    public function getSubheading(): ?string
    {
        return 'Tinjau naskah, beri catatan, lalu minta perbaikan atau terbitkan.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan')
                ->icon('heroicon-o-check')
                ->color('gray')
                ->visible(fn (): bool => Auth::user()?->can('update', $this->getRecord()) ?? false)
                ->action(fn () => $this->save()),
            Action::make('assign_editor')
                ->label(fn (): string => filled($this->getRecord()->assigned_editor_id) ? 'Ganti Editor' : 'Tugaskan Editor')
                ->icon('heroicon-o-user-plus')
                ->schema([
                    Select::make('editor_id')
                        ->label('Editor')
                        ->options(fn (): array => EditorialResource::editorOptions())
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->visible(fn (): bool => filled($this->getRecord()->assigned_editor_id)
                    ? EditorialResource::canReassignRecord($this->getRecord())
                    : EditorialResource::canAssignRecord($this->getRecord()))
                ->action(function (array $data): void {
                    $this->runWorkflowAction(function () use ($data): void {
                        app(InsightEditorialWorkflowService::class)->assignEditor(
                            $this->getRecord(),
                            User::query()->findOrFail($data['editor_id']),
                            Auth::user(),
                        );
                    }, 'Editor berhasil ditugaskan.');
                }),
            Action::make('request_revision')
                ->label('Minta Perbaikan')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->schema([
                    Textarea::make('editor_notes')
                        ->label('Catatan untuk Penulis')
                        ->required()
                        ->rows(7)
                        ->maxLength(10000),
                ])
                ->fillForm(fn (): array => ['editor_notes' => $this->getRecord()->editor_notes])
                ->visible(fn (): bool => $this->getRecord()->status->canonical() === InsightStatus::Review
                    && (Auth::user()?->can('requestRevision', $this->getRecord()) ?? false))
                ->action(fn (array $data) => $this->runWorkflowAction(
                    fn () => app(InsightEditorialWorkflowService::class)->requestRevision($this->getRecord(), Auth::user(), $data['editor_notes']),
                    'Naskah dikembalikan ke Draft.',
                )),
            Action::make('publish')
                ->label('Terbitkan')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Terbitkan artikel?')
                ->modalDescription('Jika Jadwal Terbit kosong, artikel langsung tayang. Jika diisi waktu mendatang, artikel akan tayang otomatis sesuai jadwal.')
                ->visible(fn (): bool => $this->getRecord()->status->canonical() === InsightStatus::Review
                    && (Auth::user()?->can('publish', $this->getRecord()) ?? false))
                ->action(fn () => $this->runWorkflowAction(
                    function (): Insight {
                        $formData = $this->getSchema('form')?->getState() ?? [];
                        $this->getRecord()->update([
                            'published_at' => $formData['published_at'] ?? null,
                            'updated_by' => Auth::id(),
                        ]);

                        return app(InsightEditorialWorkflowService::class)->publish($this->getRecord()->refresh(), Auth::user());
                    },
                    fn (Insight $insight): string => $insight->published_at?->isFuture()
                        ? 'Artikel berhasil dijadwalkan untuk terbit.'
                        : 'Artikel berhasil diterbitkan.',
                )),
            EditorialResource::historyAction(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = Auth::id();

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->getRecord()->wasChanged('editor_notes')
            && filled($this->getRecord()->editor_notes)
            && $this->getRecord()->status->canonical() === InsightStatus::Review) {
            app(InsightEditorialWorkflowService::class)->addEditorialNote(
                $this->getRecord(),
                Auth::user(),
                $this->getRecord()->editor_notes,
            );
        }

        app(InsightFootnoteService::class)->sync($this->getRecord());

        $this->record = Insight::query()->findOrFail($this->getRecord()->getKey());
        $this->form->model($this->record);
        $this->fillForm();
    }

    private function runWorkflowAction(callable $callback, string|\Closure $successMessage): void
    {
        try {
            $result = $callback();
            $this->record = Insight::query()->findOrFail($this->getRecord()->id);
            $this->fillForm();
            $title = $successMessage instanceof \Closure ? $successMessage($result) : $successMessage;
            Notification::make()->success()->title($title)->send();
        } catch (Throwable $exception) {
            report($exception);
            Notification::make()->danger()->title('Aksi editorial gagal')->body($exception->getMessage())->persistent()->send();
        }
    }
}
