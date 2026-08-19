<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CollaborationSubmissions\CollaborationSubmissionResource;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\Insights\InsightResource;
use App\Filament\Resources\ProgramResource;
use App\Filament\Resources\Publications\PublicationResource;
use App\Models\CollaborationSubmission;
use App\Models\ContactMessage;
use App\Models\Insight;
use App\Models\InsightEditorialActivity;
use App\Models\Program;
use App\Models\Publication;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RecentActivityWidget extends Widget
{
    protected string $view = 'filament.widgets.recent-activity';

    protected int|string|array $columnSpan = [
        'md' => 6,
        'xl' => 5,
    ];

    protected static ?int $sort = 10;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        $user = auth()->user();

        return (bool) $user && collect([
            'view insights',
            'view publications',
            'view programs',
            'view contact messages',
            'view collaboration submissions',
        ])->contains(fn (string $permission): bool => $user->can($permission));
    }

    protected function getViewData(): array
    {
        $editorialActivities = $this->mapEditorialAudit();
        $activities = collect()
            ->merge($editorialActivities->isNotEmpty() ? $editorialActivities : $this->mapInsights())
            ->merge($this->mapPublications())
            ->merge($this->mapPrograms())
            ->merge($this->mapMessages())
            ->merge($this->mapCollaborations())
            ->sortByDesc('date')
            ->take(5)
            ->values();

        return [
            'activities' => $activities,
        ];
    }

    private function mapEditorialAudit(): Collection
    {
        if (! auth()->user()?->can('view insights')) {
            return collect();
        }

        return InsightEditorialActivity::query()
            ->with(['actor', 'insight'])
            ->whereIn('insight_id', InsightResource::getEloquentQuery()->select('insights.id'))
            ->latest('created_at')
            ->take(5)
            ->get()
            ->filter(fn (InsightEditorialActivity $activity): bool => (bool) $activity->insight)
            ->map(fn (InsightEditorialActivity $activity): array => $this->activity(
                userName: $activity->actor?->name ?: 'Edulaw Admin',
                action: $this->editorialEventLabel($activity->event),
                title: $activity->insight?->title,
                date: $activity->created_at,
                tone: $this->editorialTone($activity),
                url: InsightResource::canEdit($activity->insight)
                    ? InsightResource::getUrl('edit', ['record' => $activity->insight])
                    : InsightResource::getUrl('index'),
            ));
    }

    private function mapInsights(): Collection
    {
        if (! auth()->user()?->can('view insights')) {
            return collect();
        }

        $query = InsightResource::getEloquentQuery()
            ->with(['creator', 'updater'])
            ->latest('updated_at');

        if (! InsightResource::canManageEditorialWorkflow()) {
            $query->where('status', 'draft');
        }

        return $query->take(3)
            ->get()
            ->map(fn (Insight $insight): array => $this->activity(
                userName: $this->actorName($insight->updater?->name, $insight->creator?->name),
                action: 'memperbarui Editorial',
                title: $insight->title,
                date: $insight->updated_at,
                tone: 'blue',
                url: InsightResource::getUrl('edit', ['record' => $insight->getKey()]),
            ));
    }

    private function mapPublications(): Collection
    {
        if (! auth()->user()?->can('view publications')) {
            return collect();
        }

        return Publication::query()
            ->with(['creator', 'updater'])
            ->latest('updated_at')
            ->take(2)
            ->get()
            ->map(fn (Publication $publication): array => $this->activity(
                userName: $this->actorName($publication->updater?->name, $publication->creator?->name),
                action: 'memperbarui Publikasi',
                title: $publication->title,
                date: $publication->updated_at,
                tone: 'green',
                url: PublicationResource::getUrl('edit', ['record' => $publication->getKey()]),
            ));
    }

    private function mapPrograms(): Collection
    {
        if (! auth()->user()?->can('view programs')) {
            return collect();
        }

        return Program::query()
            ->with(['creator', 'updater'])
            ->latest('updated_at')
            ->take(2)
            ->get()
            ->map(fn (Program $program): array => $this->activity(
                userName: $this->actorName($program->updater?->name, $program->creator?->name),
                action: 'memperbarui Program',
                title: $program->name,
                date: $program->updated_at,
                tone: 'orange',
                url: ProgramResource::getUrl('edit', ['record' => $program->getKey()]),
            ));
    }

    private function mapMessages(): Collection
    {
        if (! auth()->user()?->can('view contact messages')) {
            return collect();
        }

        return ContactMessage::query()
            ->latest('updated_at')
            ->take(2)
            ->get()
            ->map(fn (ContactMessage $message): array => $this->activity(
                userName: $message->name ?: 'Pengunjung Website',
                action: 'mengirim pesan kontak',
                title: $message->subject ?: $message->name,
                date: $message->updated_at,
                tone: 'red',
                url: ContactMessageResource::getUrl('edit', ['record' => $message->getKey()]),
            ));
    }

    private function mapCollaborations(): Collection
    {
        if (! auth()->user()?->can('view collaboration submissions')) {
            return collect();
        }

        return CollaborationSubmission::query()
            ->latest('updated_at')
            ->take(2)
            ->get()
            ->map(fn (CollaborationSubmission $submission): array => $this->activity(
                userName: $submission->name ?: 'Pengunjung Website',
                action: 'mengirim permintaan kolaborasi',
                title: $submission->subject ?: $submission->name,
                date: $submission->updated_at,
                tone: 'purple',
                url: CollaborationSubmissionResource::getUrl('edit', ['record' => $submission->getKey()]),
            ));
    }

    private function activity(string $userName, string $action, ?string $title, mixed $date, string $tone, string $url): array
    {
        return [
            'userName' => $userName,
            'initials' => $this->initials($userName),
            'action' => $action,
            'title' => $title ?: 'Tanpa judul',
            'date' => $date,
            'time' => $date?->diffForHumans(),
            'tone' => $tone,
            'url' => $url,
        ];
    }

    private function actorName(?string ...$names): string
    {
        return collect($names)->filter()->first() ?: 'Edulaw Admin';
    }

    private function initials(string $name): string
    {
        return Str::of($name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $word): string => Str::upper(Str::substr($word, 0, 1)))
            ->join('') ?: 'EA';
    }

    private function editorialEventLabel(string $event): string
    {
        return match ($event) {
            'draft_created' => 'membuat draft Insight',
            'submitted_for_review',
            'submission_submitted' => 'mengirim naskah untuk review',
            'resubmitted_for_review' => 'mengirim ulang naskah',
            'editor_assigned' => 'menugaskan Editor',
            'editor_changed' => 'mengganti Editor',
            'assignment_accepted' => 'menerima penugasan Editor',
            'assignment_completed' => 'menyelesaikan penugasan Editor',
            'editor_note_saved' => 'menyimpan catatan editorial',
            'review_started' => 'memulai review naskah',
            'revision_requested' => 'meminta revisi',
            'revision_submitted' => 'mengirim hasil revisi',
            'insight_approved' => 'menyetujui naskah',
            'published',
            'insight_published' => 'menerbitkan Insight',
            'notification_sent' => 'mengirim notifikasi editorial',
            'workflow_stage_changed' => 'memindahkan tahap editorial',
            'archived' => 'mengarsipkan Insight',
            default => Str::headline($event),
        };
    }

    private function editorialTone(InsightEditorialActivity $activity): string
    {
        return match ($activity->to_status) {
            'published' => 'green',
            'archived' => 'gray',
            'draft' => 'orange',
            default => match (true) {
                str_contains($activity->event, 'published'),
                str_contains($activity->event, 'approved') => 'green',
                str_contains($activity->event, 'revision') => 'orange',
                str_contains($activity->event, 'editor'),
                str_contains($activity->event, 'assignment') => 'purple',
                default => 'blue',
            },
        };
    }
}
