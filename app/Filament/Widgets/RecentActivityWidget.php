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

class RecentActivityWidget extends Widget
{
    protected string $view = 'filament.widgets.recent-activity';

    protected int|string|array $columnSpan = [
        'md' => 6,
        'xl' => 6,
    ];

    protected static ?int $sort = 11;

    protected function getViewData(): array
    {
        $activities = collect()
            ->merge($this->mapInsights())
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

    private function mapInsights(): Collection
    {
        return Insight::query()
            ->latest('updated_at')
            ->take(3)
            ->get()
            ->map(fn (Insight $insight): array => [
                'label' => 'Insight diperbarui',
                'title' => $insight->title,
                'date' => $insight->updated_at,
                'time' => $insight->updated_at?->diffForHumans(),
                'tone' => 'primary',
                'url' => InsightResource::getUrl('edit', ['record' => $insight->getKey()]),
            ]);
    }

    private function mapPublications(): Collection
    {
        return Publication::query()
            ->latest('updated_at')
            ->take(2)
            ->get()
            ->map(fn (Publication $publication): array => [
                'label' => 'Publikasi diperbarui',
                'title' => $publication->title,
                'date' => $publication->updated_at,
                'time' => $publication->updated_at?->diffForHumans(),
                'tone' => 'success',
                'url' => PublicationResource::getUrl('edit', ['record' => $publication->getKey()]),
            ]);
    }

    private function mapPrograms(): Collection
    {
        return Program::query()
            ->latest('updated_at')
            ->take(2)
            ->get()
            ->map(fn (Program $program): array => [
                'label' => 'Program diperbarui',
                'title' => $program->name,
                'date' => $program->updated_at,
                'time' => $program->updated_at?->diffForHumans(),
                'tone' => 'warning',
                'url' => ProgramResource::getUrl('edit', ['record' => $program->getKey()]),
            ]);
    }

    private function mapMessages(): Collection
    {
        return ContactMessage::query()
            ->latest('updated_at')
            ->take(2)
            ->get()
            ->map(fn (ContactMessage $message): array => [
                'label' => 'Pesan kontak masuk',
                'title' => $message->subject ?: $message->name,
                'date' => $message->updated_at,
                'time' => $message->updated_at?->diffForHumans(),
                'tone' => 'danger',
                'url' => ContactMessageResource::getUrl('edit', ['record' => $message->getKey()]),
            ]);
    }

    private function mapCollaborations(): Collection
    {
        return CollaborationSubmission::query()
            ->latest('updated_at')
            ->take(2)
            ->get()
            ->map(fn (CollaborationSubmission $submission): array => [
                'label' => 'Pengajuan kolaborasi',
                'title' => $submission->name,
                'date' => $submission->updated_at,
                'time' => $submission->updated_at?->diffForHumans(),
                'tone' => 'purple',
                'url' => CollaborationSubmissionResource::getUrl('edit', ['record' => $submission->getKey()]),
            ]);
    }
}
