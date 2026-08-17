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

test('filter parameters are noindex and canonicalize to the clean index URL', function () {
    $this->get(route('insights.index', ['author' => 'penulis-publik', 'sort' => 'latest']))
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex,follow">', false)
        ->assertSee('<link rel="canonical" href="'.route('insights.index').'">', false)
        ->assertDontSee('rel="canonical" href="'.route('insights.index').'?author=', false);
});

test('administrative html is excluded from crawling and indexing', function () {
    $this->get('/admin/login')
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex,nofollow">', false);
});

test('www requests redirect permanently to the configured canonical host', function () {
    config(['app.url' => 'https://edulawproject.id']);

    $this->get('https://www.edulawproject.id/insight?utm_source=google')
        ->assertMovedPermanently()
        ->assertRedirect('https://edulawproject.id/insight?utm_source=google');
});

test('canonical host requests are not redirected', function () {
    config(['app.url' => 'https://edulawproject.id']);

    $this->get('https://edulawproject.id/')
        ->assertOk()
        ->assertSee('<link rel="canonical" href="https://edulawproject.id">', false);
});

test('public email links opt out of Cloudflare email rewriting', function () {
    $this->get(route('contact.index'))
        ->assertOk()
        ->assertSee('<!--email_off-->', false)
        ->assertSee('href="mailto:edulawproject@gmail.com"', false)
        ->assertSee('<!--/email_off-->', false);
});

test('robots file is available with sitemap and parameter exclusions', function () {
    $this->get(route('robots'))
        ->assertOk()
        ->assertHeader('content-type', 'text/plain; charset=UTF-8')
        ->assertSee('Disallow: /admin', false)
        ->assertSee('Disallow: /search', false)
        ->assertSee('Disallow: /*?page=', false)
        ->assertSee('Disallow: /*?sort=', false)
        ->assertSee('Disallow: /*?author=', false)
        ->assertSee('Disallow: /*?category=', false)
        ->assertSee('Sitemap: https://edulawproject.id/sitemap.xml', false);
});
