<?php

namespace App\Http\Controllers;

use App\Support\EdulawSite;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about', [
            'aboutHero' => EdulawSite::block('about.hero'),
            'aboutStats' => EdulawSite::blocks('about.stats'),
            'aboutLeaders' => EdulawSite::blocks('about.leaders'),
            'aboutManagers' => EdulawSite::blocks('about.managers'),
            'aboutTeamMembers' => EdulawSite::blocks('about.team'),
            'aboutWhy' => EdulawSite::block('about.why'),
            'aboutFocusIntro' => EdulawSite::block('about.focus_intro'),
            'aboutFocusAreas' => EdulawSite::blocks('about.focus'),
            'aboutTimelineIntro' => EdulawSite::block('about.timeline_intro'),
            'aboutTimeline' => EdulawSite::blocks('about.timeline'),
            'aboutTimelineMeta' => EdulawSite::blocks('about.timeline_meta'),
            'sharedCta' => EdulawSite::block('shared.cta'),
        ]);
    }
}
