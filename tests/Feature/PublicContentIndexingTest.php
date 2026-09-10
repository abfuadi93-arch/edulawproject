<?php

use App\Models\Author;
use App\Models\Insight;
use App\Models\Multimedia;
use App\Models\Program;
use App\Models\Publication;

test('published but thin detail pages are accessible without being advertised for indexing', function () {
    $insight = Insight::query()->create([
        'title' => 'Editorial Belum Lengkap',
        'slug' => 'editorial-belum-lengkap',
        'content' => 'Catatan editorial yang belum lengkap.',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $publication = Publication::query()->create([
        'title' => 'Publikasi Belum Lengkap',
        'slug' => 'publikasi-belum-lengkap',
        'excerpt' => 'Abstrak singkat.',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $program = Program::query()->create([
        'name' => 'Program Belum Lengkap',
        'slug' => 'program-belum-lengkap',
        'description' => 'Dokumentasi singkat.',
        'status' => 'archived',
        'publication_status' => 'published',
    ]);
    $video = Multimedia::query()->create([
        'title' => 'Video Belum Lengkap',
        'slug' => 'video-belum-lengkap',
        'type' => 'video',
        'platform' => 'youtube',
        'media_url' => 'https://www.youtube.com/watch?v=oMjVH5Rbn5k',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $author = Author::query()->create([
        'name' => 'Kontributor Belum Lengkap',
        'slug' => 'kontributor-belum-lengkap',
        'is_active' => true,
        'show_in_contributor_section' => true,
    ]);
    $author->insights()->attach($insight, ['author_order' => 1, 'role' => 'Author']);

    foreach ([
        route('insights.show', $insight->slug),
        route('publications.show', $publication->slug),
        route('programs.show', $program->slug),
        route('multimedia.show', $video->slug),
        route('profiles.show', $author->slug),
    ] as $url) {
        $this->get($url)
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex,follow">', false);
    }

    $sitemap = $this->get(route('sitemap'))->assertOk();
    foreach ([
        route('insights.show', $insight->slug),
        route('publications.show', $publication->slug),
        route('programs.show', $program->slug),
        route('multimedia.show', $video->slug),
        route('profiles.show', $author->slug),
    ] as $url) {
        $sitemap->assertDontSee($url, false);
    }
});

test('out of range archive pagination returns a real not found response', function (string $url) {
    $this->get($url)->assertNotFound();
})->with([
    'editorial' => fn () => route('insights.index', ['page' => 999]),
    'publications' => fn () => route('publications.index', ['page' => 999]),
    'program archive' => fn () => route('programs.archive', ['page' => 999]),
    'opportunities' => fn () => route('opportunities.index', ['page' => 999]),
    'multimedia' => fn () => route('multimedia.index', ['video_page' => 999]),
]);
