<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Insights\InsightResource;
use App\Filament\Resources\ProgramResource;
use App\Filament\Resources\Publications\PublicationResource;
use App\Models\Insight;
use App\Models\Program;
use App\Models\Publication;
use Filament\Widgets\Widget;

class EdulawQuickActions extends Widget
{
    protected string $view = 'filament.widgets.edulaw-quick-actions';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -30;

    public static function canView(): bool
    {
        return auth()->check();
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $userName = $user?->name ?? 'Admin';

        return [
            'userName' => $userName,
            'canCreateInsight' => (bool) $user?->can('create insights'),
            'canCreatePublication' => (bool) $user?->can('create publications'),
            'canCreateProgram' => (bool) $user?->can('create programs'),
            'canViewInsights' => (bool) $user?->can('view insights'),
            'canViewPublications' => (bool) $user?->can('view publications'),
            'canViewPrograms' => (bool) $user?->can('view programs'),
            'insightCreateUrl' => $user?->can('create insights') ? InsightResource::getUrl('create') : null,
            'publicationCreateUrl' => $user?->can('create publications') ? PublicationResource::getUrl('create') : null,
            'programCreateUrl' => $user?->can('create programs') ? ProgramResource::getUrl('create') : null,
            'insightIndexUrl' => $user?->can('view insights') ? InsightResource::getUrl('index') : null,
            'websiteUrl' => url('/'),
            'publishedInsights' => Insight::where('status', 'published')->count(),
            'activePrograms' => Program::whereIn('status', ['upcoming', 'ongoing'])->count(),
            'publishedPublications' => Publication::where('status', 'published')->count(),
        ];
    }
}
