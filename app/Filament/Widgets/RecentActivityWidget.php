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
        'xl' => 6,
    ];

    protected static ?int $sort = 30;

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
        $activities = collect()
            ->merge($this->mapInsights())
            ->merge($this->mapPublications())
            ->merge($this->mapPrograms())
            ->merge($this->mapMessages())
            ->merge($this->mapCollaborations())
            ->sortByDesc('date')
            ->take(6)
            ->values();

        return [
            'activities' => $activities,
        ];
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
                action: 'updated Editorial',
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
                action: 'updated Publication',
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
                action: 'updated Program',
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
                userName: $message->name ?: 'Website Visitor',
                action: 'sent Contact Message',
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
                userName: $submission->name ?: 'Website Visitor',
                action: 'submitted Collaboration Request',
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
            'title' => $title ?: 'Untitled',
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
}
