<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Support\EdulawSite;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        $profilePriority = [
            'founder' => 1,
            'co_founder' => 2,
            'manager' => 3,
            'team' => 4,
        ];

        $technicalProfile = function (Author $author): bool {
            $position = Str::of((string) $author->position)
                ->lower()
                ->squish()
                ->toString();
            $name = Str::of((string) $author->name)
                ->lower()
                ->squish()
                ->toString();

            return in_array($position, ['admin', 'superadmin', 'user'], true)
                || in_array($name, ['redaksi edulaw', 'super admin'], true);
        };

        $profiles = Author::query()
            ->with('user')
            ->where('is_active', true)
            ->when(
                Schema::hasColumn('authors', 'sort_order'),
                fn ($query) => $query
                    ->orderByRaw('CASE WHEN sort_order IS NULL THEN 1 ELSE 0 END')
                    ->orderBy('sort_order')
            )
            ->orderBy('name')
            ->get()
            ->reject($technicalProfile)
            ->sortBy(function (Author $author) use ($profilePriority): string {
                $role = $author->profile_role_key ?: 'team';
                $priority = $profilePriority[$role] ?? $profilePriority['team'];
                $sortOrder = $author->sort_order ?? 999999;

                return sprintf('%02d-%06d-%s', $priority, $sortOrder, $author->name);
            })
            ->unique(fn (Author $author): string => $this->profileLookupKey($author->name))
            ->values();
        $organizationProfiles = $profiles
            ->filter(fn (Author $author): bool => $author->show_in_organization !== false)
            ->values();

        return view('pages.about', [
            'aboutHero' => EdulawSite::block('about.hero'),
            'aboutStats' => EdulawSite::blocks('about.stats'),
            'aboutWhy' => EdulawSite::block('about.why'),
            'aboutFocusIntro' => EdulawSite::block('about.focus_intro'),
            'aboutFocusAreas' => EdulawSite::blocks('about.focus'),
            'aboutTimelineIntro' => EdulawSite::block('about.timeline_intro'),
            'aboutTimeline' => EdulawSite::blocks('about.timeline'),
            'aboutTimelineMeta' => EdulawSite::blocks('about.timeline_meta'),
            'aboutProfiles' => $profiles
                ->keyBy(fn (Author $author): string => $this->profileLookupKey($author->name)),
            'aboutProfilesByRole' => $profiles
                ->groupBy(fn (Author $author): string => $author->profile_role_key ?: 'team')
                ->map(fn ($profiles) => $profiles->values()),
            'aboutOrganizationProfilesByRole' => $organizationProfiles
                ->groupBy(fn (Author $author): string => $author->profile_role_key ?: 'team')
                ->map(fn ($profiles) => $profiles->values()),
            'sharedCta' => EdulawSite::block('shared.cta'),
        ]);
    }

    private function profileLookupKey(string $name): string
    {
        return Str::of($name)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/i', ' ')
            ->squish()
            ->toString();
    }
}
