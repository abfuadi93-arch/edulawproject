<?php

namespace App\Services\Editorial;

use App\Enums\EditorialActivityType;
use App\Models\Insight;
use App\Models\InsightEditorAssignment;
use App\Models\User;
use App\Notifications\Editorial\AssignmentCancelledNotification;
use App\Notifications\Editorial\EditorAssignedNotification;
use App\Notifications\Editorial\EditorialDatabaseNotification;
use App\Notifications\Editorial\EditorReassignedNotification;
use App\Notifications\Editorial\InsightApprovedNotification;
use App\Notifications\Editorial\InsightPublishedNotification;
use App\Notifications\Editorial\ReviewStartedNotification;
use Illuminate\Support\Collection;
use Throwable;

class InsightNotificationService
{
    public function __construct(private readonly InsightAuditService $audit) {}

    public function editorAssigned(Insight $insight, InsightEditorAssignment $assignment, User $actor): void
    {
        if ($assignment->editor) {
            $this->deliver($assignment->editor, new EditorAssignedNotification($insight, $assignment, $actor), $insight, $assignment, $actor);
        }
    }

    public function editorReassigned(Insight $insight, InsightEditorAssignment $oldAssignment, InsightEditorAssignment $newAssignment, User $actor): void
    {
        if ($oldAssignment->editor) {
            $this->deliver($oldAssignment->editor, new EditorReassignedNotification($insight, $newAssignment, $actor, true), $insight, $newAssignment, $actor);
        }

        if ($newAssignment->editor) {
            $this->deliver($newAssignment->editor, new EditorReassignedNotification($insight, $newAssignment, $actor, false), $insight, $newAssignment, $actor);
        }
    }

    public function assignmentCancelled(Insight $insight, InsightEditorAssignment $assignment, User $actor): void
    {
        if ($assignment->editor) {
            $this->deliver($assignment->editor, new AssignmentCancelledNotification($insight, $assignment, $actor), $insight, $assignment, $actor);
        }
    }

    public function reviewStarted(Insight $insight, InsightEditorAssignment $assignment, User $actor): void
    {
        if ($insight->creator) {
            $this->deliver($insight->creator, new ReviewStartedNotification($insight, $assignment, $actor), $insight, $assignment, $actor);
        }
    }

    public function insightApproved(Insight $insight, ?InsightEditorAssignment $assignment, User $actor): void
    {
        $this->recipients($insight->creator, $this->superAdmins())
            ->each(fn (User $user) => $this->deliver($user, new InsightApprovedNotification($insight, $assignment, $actor), $insight, $assignment, $actor));
    }

    public function insightPublished(Insight $insight, ?InsightEditorAssignment $assignment, User $actor): void
    {
        $this->recipients($insight->creator, collect([$assignment?->editor]))
            ->each(fn (User $user) => $this->deliver($user, new InsightPublishedNotification($insight, $assignment, $actor), $insight, $assignment, $actor));
    }

    private function deliver(
        User $recipient,
        EditorialDatabaseNotification $notification,
        Insight $insight,
        ?InsightEditorAssignment $assignment,
        User $actor,
    ): void {
        $dedupeKey = $notification->dedupeKey($recipient);

        if ($recipient->notifications()->where('data->dedupe_key', $dedupeKey)->exists()) {
            return;
        }

        try {
            $recipient->notify($notification);
            $this->audit->record($insight, EditorialActivityType::NotificationSent, $actor, "Notifikasi dikirim kepada {$recipient->name}.", [
                'assignment_id' => $assignment?->id,
                'metadata' => ['recipient_id' => $recipient->id, 'dedupe_key' => $dedupeKey],
            ]);
        } catch (Throwable $exception) {
            report($exception);
            $this->audit->record($insight, EditorialActivityType::NotificationFailed, $actor, "Notifikasi kepada {$recipient->name} gagal dikirim.", [
                'assignment_id' => $assignment?->id,
                'metadata' => ['recipient_id' => $recipient->id, 'error' => $exception->getMessage()],
            ]);
        }
    }

    private function superAdmins(): Collection
    {
        return User::role('super_admin')->where('is_active', true)->get();
    }

    private function recipients(?User $primary, Collection $additional): Collection
    {
        return collect([$primary])->merge($additional)->filter()->unique('id')->values();
    }
}
