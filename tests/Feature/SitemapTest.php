<?php

use App\Models\Author;
use App\Models\Insight;
use App\Models\Opportunity;
use App\Models\Program;
use App\Models\Publication;

test('sitemap contains substantive indexes but excludes opportunity detail routes', function () {
    $opportunity = Opportunity::query()->create([
        'title' => 'Fellowship Riset Hukum',
        'slug' => 'fellowship-riset-hukum',
        'type' => 'fellowship',
        'status' => 'open',
        'deadline' => now()->addWeek()->toDateString(),
        'application_link' => 'https://example.test/daftar',
    ]);

    $this->get(route('sitemap'))
        ->assertOk()
        ->assertHeader('content-type', 'application/xml; charset=UTF-8')
        ->assertSee(route('opportunities.index'), false)
        ->assertSee(route('insights.categories.show', 'law-governance'), false)
        ->assertSee(route('insights.categories.show', 'legal-101'), false)
        ->assertSee(route('insights.categories.show', 'regulatory-update'), false)
        ->assertSee(route('insights.categories.show', 'edulaw-insight'), false)
        ->assertSee(route('editorial-standards'), false)
        ->assertSee(route('corrections-policy'), false)
        ->assertDontSee(route('opportunities.show', $opportunity->slug), false);
});

test('sitemap contains only public content and contributing public authors', function () {
    $publishedInsight = Insight::query()->create([
        'title' => 'Insight Publik untuk Sitemap',
        'slug' => 'insight-publik-untuk-sitemap',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $draftInsight = Insight::query()->create([
        'title' => 'Draft Tidak untuk Sitemap',
        'slug' => 'draft-tidak-untuk-sitemap',
        'status' => 'draft',
    ]);

    $scheduledInsight = Insight::query()->create([
        'title' => 'Insight Terjadwal',
        'slug' => 'insight-terjadwal',
        'status' => 'published',
        'published_at' => now()->addDay(),
    ]);

    $publicAuthor = Author::query()->create([
        'name' => 'Penulis Publik',
        'slug' => 'penulis-publik',
        'is_active' => true,
        'show_in_contributor_section' => true,
    ]);
    $publicAuthor->insights()->attach($publishedInsight, ['author_order' => 1, 'role' => 'Author']);

    $inactiveAuthor = Author::query()->create([
        'name' => 'Penulis Nonaktif',
        'slug' => 'penulis-nonaktif',
        'is_active' => false,
        'show_in_contributor_section' => true,
    ]);
    $inactiveAuthor->insights()->attach($publishedInsight, ['author_order' => 1, 'role' => 'Author']);

    $authorWithoutContribution = Author::query()->create([
        'name' => 'Belum Berkontribusi',
        'slug' => 'belum-berkontribusi',
        'is_active' => true,
        'show_in_contributor_section' => true,
    ]);

    $hiddenContributor = Author::query()->create([
        'name' => 'Kontributor Tersembunyi',
        'slug' => 'kontributor-tersembunyi',
        'is_active' => true,
        'show_in_contributor_section' => false,
    ]);
    $hiddenContributor->insights()->attach($publishedInsight, ['author_order' => 1, 'role' => 'Author']);

    $technicalAuthor = Author::query()->create([
        'name' => 'Super Admin',
        'slug' => 'super-admin',
        'position' => 'admin',
        'is_active' => true,
        'show_in_contributor_section' => true,
    ]);
    $technicalAuthor->insights()->attach($publishedInsight, ['author_order' => 1, 'role' => 'Author']);

    $publicArchive = Program::query()->create([
        'name' => 'Arsip Program Publik',
        'slug' => 'arsip-program-publik',
        'status' => 'archived',
        'publication_status' => 'published',
    ]);

    $internalProgram = Program::query()->create([
        'name' => 'Program Internal',
        'slug' => 'program-internal',
        'status' => 'ongoing',
        'publication_status' => 'draft',
    ]);

    $response = $this->get(route('sitemap'))
        ->assertOk()
        ->assertSee(route('insights.show', $publishedInsight->slug), false)
        ->assertDontSee(route('insights.show', $draftInsight->slug), false)
        ->assertDontSee(route('insights.show', $scheduledInsight->slug), false)
        ->assertSee(route('profiles.show', $publicAuthor->slug), false)
        ->assertDontSee(route('profiles.show', $inactiveAuthor->slug), false)
        ->assertDontSee(route('profiles.show', $authorWithoutContribution->slug), false)
        ->assertDontSee(route('profiles.show', $hiddenContributor->slug), false)
        ->assertDontSee('/profil/super-admin', false)
        ->assertSee(route('programs.show', $publicArchive->slug), false)
        ->assertDontSee(route('programs.show', $internalProgram->slug), false);

    expect(simplexml_load_string($response->getContent()))->not->toBeFalse();
});

test('every sitemap location is a direct indexable canonical response', function () {
    $insight = Insight::query()->create([
        'title' => 'Editorial Canonical Sitemap',
        'slug' => 'editorial-canonical-sitemap',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    Publication::query()->create([
        'title' => 'Publikasi Canonical Sitemap',
        'slug' => 'publikasi-canonical-sitemap',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    Program::query()->create([
        'name' => 'Program Canonical Sitemap',
        'slug' => 'program-canonical-sitemap',
        'status' => 'ongoing',
        'publication_status' => 'published',
    ]);

    Opportunity::query()->create([
        'title' => 'Peluang Canonical Sitemap',
        'slug' => 'peluang-canonical-sitemap',
        'status' => 'open',
        'deadline' => now()->addWeek()->toDateString(),
        'application_link' => 'https://example.test/apply',
    ]);

    $author = Author::query()->create([
        'name' => 'Kontributor Canonical Sitemap',
        'slug' => 'kontributor-canonical-sitemap',
        'is_active' => true,
        'show_in_contributor_section' => true,
    ]);
    $author->insights()->attach($insight, ['author_order' => 1, 'role' => 'Author']);

    $xml = $this->get(route('sitemap'))->assertOk()->getContent();
    $sitemap = simplexml_load_string($xml);

    expect($sitemap)->not->toBeFalse();

    $locations = collect($sitemap->url)
        ->map(fn ($url): string => (string) $url->loc)
        ->values();

    expect($locations)->not->toBeEmpty();

    foreach ($locations as $location) {
        $this->get($location)
            ->assertOk()
            ->assertHeaderMissing('Location')
            ->assertDontSee('<meta name="robots" content="noindex', false)
            ->assertSee('<link rel="canonical" href="'.$location.'">', false);
    }

    $paths = $locations
        ->map(fn (string $location): string => (string) parse_url($location, PHP_URL_PATH));

    expect($paths->contains(fn (string $path): bool => str_starts_with($path, '/admin')))->toBeFalse()
        ->and($paths->contains('/login'))->toBeFalse()
        ->and($paths->contains('/search'))->toBeFalse()
        ->and($paths->contains('/publikasi'))->toBeFalse()
        ->and($paths->contains('/peluang'))->toBeFalse()
        ->and($paths->contains('/profil/super-admin'))->toBeFalse();
});
