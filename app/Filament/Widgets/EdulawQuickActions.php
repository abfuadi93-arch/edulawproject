<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Insights\InsightResource;
use App\Filament\Resources\ProgramResource;
use App\Filament\Resources\Publications\PublicationResource;
use Filament\Widgets\Widget;
use Illuminate\Support\Str;

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
        $roleName = $user?->getRoleNames()->first();
        $displayName = $roleName
            ? Str::headline($roleName)
            : ($user?->name ?? 'Admin');

        return [
            'displayName' => $displayName,
            'canCreateInsight' => (bool) $user?->can('create insights'),
            'canCreatePublication' => (bool) $user?->can('create publications'),
            'canCreateProgram' => (bool) $user?->can('create programs'),
            'insightCreateUrl' => $user?->can('create insights') ? InsightResource::getUrl('create') : null,
            'publicationCreateUrl' => $user?->can('create publications') ? PublicationResource::getUrl('create') : null,
            'programCreateUrl' => $user?->can('create programs') ? ProgramResource::getUrl('create') : null,
        ];
    }
}
