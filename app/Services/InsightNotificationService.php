<?php

namespace App\Services;

use App\Filament\Resources\Editorial\EditorialResource;
use App\Filament\Resources\Insights\InsightResource;
use App\Models\Insight;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\DatabaseNotification;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class InsightNotificationService
{
    public function notifySubmission(Insight $insight, bool $resubmission = false): void
    {
        $recipients = filled($insight->assigned_editor_id)
            ? collect([$insight->assignedEditor])
            : $this->superAdmins();

        $this->send(
            $recipients,
            $insight,
            $resubmission ? 'resubmitted_for_review' : 'submitted_for_review',
            $resubmission ? 'Naskah dikirim ulang' : 'Naskah menunggu review',
            $insight->title,
            filled($insight->assigned_editor_id) ? $this->editorUrl($insight) : EditorialResource::getUrl('index'),
            (string) $insight->submitted_at?->timestamp,
        );
    }

    public function notifyAssignment(Insight $insight, bool $changed): void
    {
        $this->send(
            collect([$insight->assignedEditor]),
            $insight,
            $changed ? 'editor_changed' : 'editor_assigned',
            $changed ? 'Penugasan Editor diperbarui' : 'Naskah baru ditugaskan',
            $insight->title,
            $this->editorUrl($insight),
            (string) $insight->assigned_at?->timestamp,
        );
    }

    public function notifyRevisionRequested(Insight $insight): void
    {
        $this->send(
            collect([$insight->creator]),
            $insight,
            'revision_requested',
            'Perbaikan diminta',
            $insight->editor_notes ?: $insight->title,
            InsightResource::getUrl('edit', ['record' => $insight]),
            (string) $insight->revision_requested_at?->timestamp,
        );
    }

    public function notifyPublication(Insight $insight): void
    {
        $isScheduled = $insight->published_at?->isFuture() ?? false;
        $schedule = $insight->published_at?->copy()->timezone(config('edulaw.timezone'))->translatedFormat('d M Y, H:i');

        $this->send(
            collect([$insight->creator]),
            $insight,
            'published',
            $isScheduled ? 'Artikel dijadwalkan' : 'Artikel diterbitkan',
            $isScheduled
                ? $insight->title." akan tayang pada {$schedule} WIB."
                : $insight->title.' sudah tayang di website.',
            $isScheduled
                ? InsightResource::getUrl('index', ['tableSearch' => $insight->title])
                : route('insights.show', $insight->slug),
            (string) $insight->published_at?->timestamp,
        );
    }

    private function send(
        Collection $users,
        Insight $insight,
        string $type,
        string $title,
        string $body,
        string $url,
        string $eventKey,
    ): void {
        foreach ($users->filter()->unique('id') as $user) {
            $dedupeKey = "insight:{$insight->id}:{$type}:{$eventKey}";

            if ($user->notifications()->where('data->dedupe_key', $dedupeKey)->exists()) {
                continue;
            }

            $message = Notification::make()
                ->title($title)
                ->body($body)
                ->icon('heroicon-o-newspaper')
                ->actions([Action::make('open')->label('Buka')->url($url)->button()])
                ->getDatabaseMessage();

            $user->notify(new DatabaseNotification([
                ...$message,
                'insight_id' => $insight->id,
                'url' => $url,
                'notification_type' => $type,
                'dedupe_key' => $dedupeKey,
            ]));
        }
    }

    private function superAdmins(): Collection
    {
        return User::role('super_admin')->where('is_active', true)->get();
    }

    private function editorUrl(Insight $insight): string
    {
        return EditorialResource::getUrl('workspace', ['record' => $insight]);
    }
}
