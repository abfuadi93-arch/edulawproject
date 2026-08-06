<?php

namespace App\Notifications\Editorial;

use App\Filament\Resources\Editorial\EditorialResource;
use App\Models\Insight;
use App\Models\InsightEditorAssignment;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

abstract class EditorialDatabaseNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Insight $insight,
        protected ?InsightEditorAssignment $assignment,
        protected User $actor,
        protected string $messageType,
        protected string $messageTitle,
        protected string $messageBody,
        protected string $dedupeSuffix,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $url = EditorialResource::getUrl('workspace', ['record' => $this->insight]);
        $message = FilamentNotification::make()
            ->title($this->messageTitle)
            ->body($this->messageBody)
            ->icon('heroicon-o-newspaper')
            ->actions([
                Action::make('open')->label('Buka Workspace')->url($url)->button(),
            ])
            ->getDatabaseMessage();

        return [
            ...$message,
            'title' => $this->messageTitle,
            'body' => $this->messageBody,
            'insight_id' => $this->insight->id,
            'assignment_id' => $this->assignment?->id,
            'url' => $url,
            'type' => $this->messageType,
            'notification_type' => $this->legacyNotificationType(),
            'actor_id' => $this->actor->id,
            'timestamp' => now()->toIso8601String(),
            'dedupe_key' => $this->dedupeKey($notifiable),
        ];
    }

    public function dedupeKey(object $notifiable): string
    {
        return implode(':', [
            'editorial',
            $this->insight->id,
            $this->assignment?->id ?: 'none',
            $this->messageType,
            $notifiable->getKey(),
            $this->dedupeSuffix,
        ]);
    }

    protected function legacyNotificationType(): string
    {
        return $this->messageType;
    }
}
