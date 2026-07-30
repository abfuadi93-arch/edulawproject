<?php

dataset('indexable public pages', [
    'home' => ['home'],
    'about' => ['about'],
    'collaboration' => ['collaboration.index'],
    'contact' => ['contact.index'],
    'privacy' => ['privacy'],
    'terms' => ['terms'],
    'insights' => ['insights.index'],
    'publications' => ['publications.index'],
    'programs' => ['programs.index'],
    'program archive' => ['programs.archive'],
    'opportunities' => ['opportunities.index'],
    'multimedia' => ['multimedia.index'],
]);

test('public pages render one complete set of indexable SEO metadata', function (string $routeName) {
    $url = route($routeName);
    $html = $this->get($url)
        ->assertOk()
        ->getContent();

    expect(substr_count($html, '<title>'))->toBe(1)
        ->and(substr_count($html, '<meta name="description"'))->toBe(1)
        ->and(substr_count($html, '<meta name="robots"'))->toBe(1)
        ->and(substr_count($html, '<link rel="canonical"'))->toBe(1)
        ->and($html)->toContain('<meta name="robots" content="index,follow">')
        ->and($html)->toContain('<link rel="canonical" href="'.$url.'">');

    preg_match('/<title>([^<]+)<\/title>/', $html, $titleMatch);
    preg_match('/<meta name="description" content="([^"]+)">/', $html, $descriptionMatch);

    $title = html_entity_decode($titleMatch[1] ?? '', ENT_QUOTES | ENT_HTML5);
    $description = html_entity_decode($descriptionMatch[1] ?? '', ENT_QUOTES | ENT_HTML5);

    expect($title)->toEndWith(' | Edulaw Project')
        ->and(mb_strlen($description))->toBeGreaterThanOrEqual(120)
        ->toBeLessThanOrEqual(160)
        ->and($html)->toContain('<meta property="og:title" content="'.e($title).'">')
        ->and($html)->toContain('<meta name="twitter:title" content="'.e($title).'">');
})->with('indexable public pages');

test('search page is crawlable but excluded from the search index', function () {
    $this->get(route('search.index'))
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex,follow">', false)
        ->assertSee('<link rel="canonical" href="'.route('search.index').'">', false);
});
