<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Insights\InsightResource;
use App\Filament\Resources\ProgramResource;
use App\Filament\Resources\Publications\PublicationResource;
use App\Models\Insight;
use App\Models\Program;
use App\Models\Publication;
use Filament\Widgets\Widget;
use Illuminate\Support\Str;

class EdulawQuickActions extends Widget
{
    protected string $view = 'filament.widgets.edulaw-quick-actions';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = -30;

    protected function getViewData(): array
    {
        $user = auth()->user();
        $userName = $user?->name ?? 'Admin';

        return [
            'userName' => $userName,
            'userInitials' => Str::of($userName)
                ->explode(' ')
                ->filter()
                ->map(fn (string $part): string => Str::substr($part, 0, 1))
                ->take(2)
                ->implode(''),
            'userAvatarUrl' => $user?->avatar_url,
            'insightCreateUrl' => InsightResource::getUrl('create'),
            'publicationCreateUrl' => PublicationResource::getUrl('create'),
            'programCreateUrl' => ProgramResource::getUrl('create'),
            'insightIndexUrl' => InsightResource::getUrl('index'),
            'websiteUrl' => url('/'),
            'publishedInsights' => Insight::where('status', 'published')->count(),
            'activePrograms' => Program::whereIn('status', ['upcoming', 'ongoing'])->count(),
            'publishedPublications' => Publication::where('status', 'published')->count(),
        ];
    }
}
