<?php

namespace App\Services;

use App\Enums\EditorialCommentType;
use App\Filament\Resources\AssignedInsights\AssignedInsightResource;
use App\Filament\Resources\Editorial\EditorialResource;
use App\Filament\Resources\Insights\InsightResource;
use App\Models\Insight;
use App\Models\InsightEditorialNote;
use App\Models\User;
use App\Services\Editorial\InsightNotificationService as EditorialNotificationService;
use Filament\Actions\Action;
use Filament\Notifications\DatabaseNotification;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class InsightNotificationService
{
    public function notifySubmission(Insight $insight): void
    {
        $this->send($this->superAdmins(), $insight, 'submission', 'Naskah baru dikirim', $insight->title.' menunggu penugasan Editor.', $this->managerUrl(), "submission:{$insight->submitted_at?->timestamp}");
    }

    public function notifyAssignment(Insight $insight, ?User $previousEditor = null): void
    {
        $activeAssignment = $insight->editorAssignments()->active()->with('editor')->latest('id')->first();

        if (! $activeAssignment) {
            return;
        }

        $actor = $activeAssignment->assignedBy;

        if (! $actor) {
            return;
        }

        if ($previousEditor && (int) $previousEditor->id !== (int) $activeAssignment->editor_id) {
            $oldAssignment = $insight->editorAssignments()
                ->where('editor_id', $previousEditor->id)
                ->where('status', 'reassigned')
                ->with('editor')
                ->latest('id')
                ->first();

            if ($oldAssignment) {
                app(EditorialNotificationService::class)->editorReassigned($insight, $oldAssignment, $activeAssignment, $actor);

                return;
            }
        }

        app(EditorialNotificationService::class)->editorAssigned($insight, $activeAssignment, $actor);
    }

    public function notifyReviewStarted(Insight $insight): void
    {
        $this->send($this->writer($insight), $insight, 'review_started', 'Review dimulai', 'Editor mulai memeriksa '.$insight->title.'.', $this->writerUrl($insight), "review-started:{$insight->review_started_at?->timestamp}");
    }

    public function notifyCommentCreated(InsightEditorialNote $comment): void
    {
        $comment->loadMissing(['insight.creator', 'insight.assignedEditor']);
        $isAuthorResponse = $comment->type === EditorialCommentType::AuthorResponse;

        if ($isAuthorResponse) {
            $this->send($this->editor($comment->insight), $comment->insight, 'author_reply', 'Balasan dari Writer', $comment->note, $this->editorUrl(), "comment:{$comment->id}");

            return;
        }

        if ($comment->is_visible_to_writer && $comment->type !== EditorialCommentType::Internal) {
            $this->send($this->writer($comment->insight), $comment->insight, 'comment_created', 'Komentar editorial baru', $comment->note, $this->writerUrl($comment->insight), "comment:{$comment->id}");
        }
    }

    public function notifyRevisionRequested(Insight $insight): void
    {
        $this->send($this->writer($insight), $insight, 'revision_requested', 'Perbaikan diminta', 'Editor meminta perbaikan untuk '.$insight->title.'.', $this->writerUrl($insight), "revision-requested:{$insight->revision_requested_at?->timestamp}");
    }

    public function notifyRevisionSubmitted(Insight $insight): void
    {
        $this->send($this->editor($insight), $insight, 'revision_submitted', 'Perbaikan dikirim', 'Writer mengirim perbaikan putaran '.$insight->revision_round.'.', $this->editorUrl(), "revision-submitted:{$insight->revision_round}");
    }

    public function notifyCommentAddressed(InsightEditorialNote $comment): void
    {
        $this->send($this->editor($comment->insight), $comment->insight, 'comment_addressed', 'Komentar ditandai addressed', $comment->note, $this->editorUrl(), "comment-addressed:{$comment->id}:{$comment->addressed_at?->timestamp}");
    }

    public function notifyCommentReopened(InsightEditorialNote $comment): void
    {
        $this->send($this->writer($comment->insight), $comment->insight, 'comment_reopened', 'Komentar dibuka kembali', $comment->note, $this->writerUrl($comment->insight), "comment-reopened:{$comment->id}:{$comment->reopened_at?->timestamp}");
    }

    public function notifyApproval(Insight $insight): void
    {
        $this->send($this->writer($insight), $insight, 'approved', 'Naskah disetujui', $insight->title.' telah disetujui.', $this->writerUrl($insight), "approved:{$insight->approved_at?->timestamp}");
        $this->send($this->superAdmins(), $insight, 'approved', 'Naskah disetujui Editor', $insight->title.' telah mendapat persetujuan editorial.', $this->managerUrl(), "approved:{$insight->approved_at?->timestamp}");
        $this->send($this->superAdmins(), $insight, 'ready_to_publish', 'Naskah siap terbit', $insight->title.' telah disetujui Editor.', $this->managerUrl(), "ready:{$insight->approved_at?->timestamp}");
    }

    public function notifyPublication(Insight $insight): void
    {
        $this->send($this->writer($insight), $insight, 'published', 'Naskah diterbitkan', $insight->title.' sudah tayang.', route('insights.show', $insight->slug), "published:{$insight->published_at?->timestamp}");
    }

    public function notifyRejection(Insight $insight): void
    {
        $this->send($this->writer($insight), $insight, 'rejected', 'Naskah tidak dilanjutkan', $insight->rejection_reason ?: $insight->title, $this->writerUrl($insight), "rejected:{$insight->rejected_at?->timestamp}");
        $this->send($this->superAdmins(), $insight, 'rejection_recommendation', 'Rekomendasi penolakan', $insight->rejection_reason ?: $insight->title, $this->managerUrl(), "rejected:{$insight->rejected_at?->timestamp}");
    }

    public function notifyDeadlineChanged(Insight $insight, string $owner): void
    {
        $deadline = $insight->getAttribute("{$owner}_deadline");
        $users = $owner === 'editor' ? $this->editor($insight) : $this->writer($insight);
        $label = $owner === 'editor' ? 'Editor' : 'Writer';
        $url = $owner === 'editor' ? $this->editorUrl() : $this->writerUrl($insight);

        $this->send($users, $insight, 'deadline_changed', "Tenggat {$label} diperbarui", 'Tenggat: '.$deadline?->locale('id')->translatedFormat('d M Y, H:i'), $url, "deadline:{$owner}:{$deadline?->timestamp}");
    }

    public function notifyDeadlineApproaching(Insight $insight, string $owner): void
    {
        $users = ($owner === 'editor' ? $this->editor($insight) : $this->writer($insight))->merge($this->superAdmins())->unique('id');
        $this->send($users, $insight, 'deadline_approaching', 'Tenggat editorial mendekat', $insight->title, $owner === 'editor' ? $this->editorUrl() : $this->writerUrl($insight), "approaching:{$owner}:".now()->toDateString());
    }

    public function notifyDeadlineOverdue(Insight $insight, string $owner): void
    {
        $users = ($owner === 'editor' ? $this->editor($insight) : $this->writer($insight))->merge($this->superAdmins())->unique('id');
        $this->send($users, $insight, 'deadline_overdue', 'Tenggat editorial terlewati', $insight->title, $owner === 'editor' ? $this->editorUrl() : $this->writerUrl($insight), "overdue:{$owner}:".now()->toDateString());
    }

    private function send(Collection $users, Insight $insight, string $type, string $title, string $body, string $url, string $dedupeSuffix): void
    {
        foreach ($users->filter()->unique('id') as $user) {
            $dedupeKey = "insight:{$insight->id}:{$type}:{$dedupeSuffix}";

            if ($user->notifications()->where('data->dedupe_key', $dedupeKey)->exists()) {
                continue;
            }

            $message = Notification::make()
                ->title($title)
                ->body($body)
                ->icon('heroicon-o-newspaper')
                ->actions([
                    Action::make('open')->label('Buka')->url($url)->button(),
                ])
                ->getDatabaseMessage();

            $user->notify(new DatabaseNotification([
                ...$message,
                'insight_id' => $insight->id,
                'url' => $url,
                'notification_type' => $type,
                'read_status' => 'unread',
                'dedupe_key' => $dedupeKey,
            ]));
        }
    }

    private function superAdmins(): Collection
    {
        return User::role('super_admin')->where('is_active', true)->get();
    }

    private function editor(Insight $insight): Collection
    {
        return collect([$insight->assignedEditor])->filter();
    }

    private function writer(Insight $insight): Collection
    {
        return collect([$insight->creator])->filter();
    }

    private function managerUrl(): string
    {
        return EditorialResource::getUrl('index');
    }

    private function editorUrl(): string
    {
        return AssignedInsightResource::getUrl('index');
    }

    private function writerUrl(Insight $insight): string
    {
        return InsightResource::getUrl('edit', ['record' => $insight]);
    }
}
