<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CollaborationSubmissions\CollaborationSubmissionResource;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\Insights\InsightResource;
use App\Filament\Resources\ProgramResource;
use App\Models\CollaborationSubmission;
use App\Models\ContactMessage;
use App\Models\Insight;
use App\Models\Program;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class RequiresAttentionWidget extends Widget
{
    protected string $view = 'filament.widgets.requires-attention';

    protected int|string|array $columnSpan = [
        'md' => 6,
        'xl' => 6,
    ];

    protected static ?int $sort = -5;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        $user = auth()->user();

        return (bool) $user && collect([
            'view insights',
            'view programs',
            'view contact messages',
            'view collaboration submissions',
        ])->contains(fn (string $permission): bool => $user->can($permission));
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $counts = Cache::remember('dashboard.requires-attention', now()->addMinutes(3), fn (): array => [
            'draft_editorials' => Insight::query()->where('status', 'draft')->count(),
            'reviewed_editorials' => Insight::query()->where('status', 'reviewed')->count(),
            'upcoming_programs' => Program::query()
                ->whereIn('status', ['upcoming', 'ongoing'])
                ->whereBetween('event_date', [today(), now()->addDays(7)->endOfDay()])
                ->count(),
            'unread_messages' => ContactMessage::query()->where('status', 'new')->count(),
            'new_collaborations' => CollaborationSubmission::query()->where('status', 'new')->count(),
        ]);

        $items = collect([
            $user?->can('view insights') ? [
                'label' => 'Draft editorials',
                'count' => $counts['draft_editorials'],
                'tone' => 'blue',
                'icon' => 'heroicon-o-pencil-square',
                'url' => InsightResource::getUrl('index'),
            ] : null,
            $user?->can('view insights') ? [
                'label' => 'Reviewed editorials waiting for publication',
                'count' => $counts['reviewed_editorials'],
                'tone' => 'green',
                'icon' => 'heroicon-o-check-badge',
                'url' => InsightResource::getUrl('index'),
            ] : null,
            $user?->can('view programs') ? [
                'label' => 'Programs starting within seven days',
                'count' => $counts['upcoming_programs'],
                'tone' => 'orange',
                'icon' => 'heroicon-o-calendar-days',
                'url' => ProgramResource::getUrl('index'),
            ] : null,
            $user?->can('view contact messages') ? [
                'label' => 'Unread contact messages',
                'count' => $counts['unread_messages'],
                'tone' => 'red',
                'icon' => 'heroicon-o-envelope',
                'url' => ContactMessageResource::getUrl('index'),
            ] : null,
            $user?->can('view collaboration submissions') ? [
                'label' => 'New collaboration requests',
                'count' => $counts['new_collaborations'],
                'tone' => 'red',
                'icon' => 'heroicon-o-hand-raised',
                'url' => CollaborationSubmissionResource::getUrl('index'),
            ] : null,
        ])->filter(fn (?array $item): bool => $item !== null && $item['count'] > 0)->values();

        return [
            'items' => $items,
            'pendingCount' => $items->sum('count'),
        ];
    }
}
