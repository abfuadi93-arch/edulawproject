<?php

namespace App\Http\Controllers;

use App\Models\ContentBlock;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about', [
            'aboutHero' => ContentBlock::firstForArea('about.hero'),
            'aboutStats' => ContentBlock::forArea('about.stats'),
            'aboutLeaders' => ContentBlock::forArea('about.leaders'),
            'aboutManagers' => ContentBlock::forArea('about.managers'),
            'aboutTeamMembers' => ContentBlock::forArea('about.team'),
            'aboutWhy' => ContentBlock::firstForArea('about.why'),
            'aboutFocusIntro' => ContentBlock::firstForArea('about.focus_intro'),
            'aboutFocusAreas' => ContentBlock::forArea('about.focus'),
            'aboutTimelineIntro' => ContentBlock::firstForArea('about.timeline_intro'),
            'aboutTimeline' => ContentBlock::forArea('about.timeline'),
            'aboutTimelineMeta' => ContentBlock::forArea('about.timeline_meta'),
            'sharedCta' => ContentBlock::firstForArea('shared.cta'),
        ]);
    }
}
