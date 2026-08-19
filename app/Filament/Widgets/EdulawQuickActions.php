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

    protected int|string|array $columnSpan = [
        'md' => 6,
        'xl' => 12,
    ];

    protected static ?int $sort = -100;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return auth()->check();
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $roleName = $user?->getRoleNames()->first();

        return [
            'displayName' => $user?->name ?? 'Admin',
            'roleLabel' => $roleName ? Str::headline($roleName) : 'Edulaw Admin',
            'dateLabel' => now()->translatedFormat('l, d F Y'),
            'websiteUrl' => url('/'),
            'canCreateInsight' => (bool) $user?->can('create insights'),
            'canCreatePublication' => (bool) $user?->can('create publications'),
            'canCreateProgram' => (bool) $user?->can('create programs'),
            'insightCreateUrl' => $user?->can('create insights') ? InsightResource::getUrl('create') : null,
            'publicationCreateUrl' => $user?->can('create publications') ? PublicationResource::getUrl('create') : null,
            'programCreateUrl' => $user?->can('create programs') ? ProgramResource::getUrl('create') : null,
        ];
    }
}
