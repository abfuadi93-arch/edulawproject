<?php

use App\Http\Middleware\RedirectWwwToCanonicalHost;
use Illuminate\Http\Request;

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

test('valid pagination is indexable and canonicalizes to itself', function () {
    $pageTwoUrl = route('insights.index', ['page' => 2]);

    $this->get($pageTwoUrl)
        ->assertOk()
        ->assertSee('<meta name="robots" content="index,follow">', false)
        ->assertSee('<link rel="canonical" href="'.$pageTwoUrl.'">', false);
});

test('page one query is treated as a duplicate of the clean index URL', function () {
    $this->get(route('insights.index', ['page' => 1]))
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex,follow">', false)
        ->assertSee('<link rel="canonical" href="'.route('insights.index').'">', false);
});

test('administrative html is excluded from crawling and indexing', function () {
    $this->get('/admin/login')
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex,nofollow">', false);
});

test('www requests redirect permanently to the configured canonical host', function () {
    config([
        'app.url' => 'http://edulawproject.id',
        'edulaw.site.url' => 'https://edulawproject.id',
    ]);

    $this->get('https://www.edulawproject.id/insight?utm_source=google')
        ->assertMovedPermanently()
        ->assertRedirect('https://edulawproject.id/insight?utm_source=google');
});

test('http canonical host requests redirect directly to https', function () {
    config(['edulaw.site.url' => 'https://edulawproject.id']);

    $this->get('http://edulawproject.id/insight?utm_source=google')
        ->assertMovedPermanently()
        ->assertRedirect('https://edulawproject.id/insight?utm_source=google');
});

test('http www requests redirect directly to the canonical https origin', function () {
    config(['edulaw.site.url' => 'https://edulawproject.id']);

    $this->get('http://www.edulawproject.id/insight')
        ->assertMovedPermanently()
        ->assertRedirect('https://edulawproject.id/insight');
});

test('trailing slash requests redirect once to the slashless canonical URL', function () {
    $request = Request::create('http://localhost/insight/?page=2');
    $response = app(RedirectWwwToCanonicalHost::class)
        ->handle($request, fn () => response('next'));

    expect($response->getStatusCode())->toBe(301)
        ->and($response->headers->get('Location'))->toBe('http://localhost/insight?page=2');
});

test('host scheme and trailing slash normalization share a single redirect', function () {
    config(['edulaw.site.url' => 'https://edulawproject.id']);

    $this->get('http://www.edulawproject.id/insight/?page=2')
        ->assertMovedPermanently()
        ->assertRedirect('https://edulawproject.id/insight?page=2');
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

test('robots file protects internal routes while allowing meta robots and pagination to be crawled', function () {
    $this->get(route('robots'))
        ->assertOk()
        ->assertHeader('content-type', 'text/plain; charset=UTF-8')
        ->assertSee('Disallow: /admin', false)
        ->assertSee('Disallow: /search', false)
        ->assertDontSee('Disallow: /*?page=', false)
        ->assertDontSee('Disallow: /*?sort=', false)
        ->assertDontSee('Disallow: /*?author=', false)
        ->assertDontSee('Disallow: /*?category=', false)
        ->assertSee('Sitemap: https://edulawproject.id/sitemap.xml', false);
});
