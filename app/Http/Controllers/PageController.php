<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Support\EdulawSite;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        $profiles = Author::query()
            ->with('user')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

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
