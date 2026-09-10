<?php

use App\Http\Middleware\RedirectWwwToCanonicalHost;
use App\Models\Insight;
use App\Models\InsightCategory;
use App\Models\Publication;
use Illuminate\Http\Request;

test('legacy publication index redirects permanently in one hop to the canonical index', function () {
    $this->get('/publikasi')
        ->assertMovedPermanently()
        ->assertRedirect('/riset-publikasi');

    $this->get('/riset-publikasi')->assertOk();
});

test('legacy publication detail redirects permanently in one hop to the canonical detail', function () {
    $publication = Publication::query()->create([
        'title' => 'Publikasi Legacy',
        'slug' => 'publikasi-legacy',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $this->get('/publikasi/'.$publication->slug)
        ->assertMovedPermanently()
        ->assertRedirect('/riset-publikasi/'.$publication->slug);

    $this->get('/riset-publikasi/'.$publication->slug)->assertOk();
});

test('legacy opportunity index redirects permanently in one hop to the canonical index', function () {
    $this->get('/peluang')
        ->assertMovedPermanently()
        ->assertRedirect('/opportunities');

    $this->get('/opportunities')->assertOk();
});

test('legacy routes with trailing slashes redirect directly to their canonical destination', function () {
    $request = Request::create('http://localhost/publikasi/contoh/?utm_source=legacy');
    $response = app(RedirectWwwToCanonicalHost::class)
        ->handle($request, fn () => response('next'));

    expect($response->getStatusCode())->toBe(301)
        ->and($response->headers->get('Location'))
        ->toBe('http://localhost/riset-publikasi/contoh?utm_source=legacy');
});

test('host scheme and legacy path normalization share a single redirect', function () {
    config(['edulaw.site.url' => 'https://edulawproject.id']);

    $request = Request::create('http://www.edulawproject.id/publikasi/');
    $response = app(RedirectWwwToCanonicalHost::class)
        ->handle($request, fn () => response('next'));

    expect($response->getStatusCode())->toBe(301)
        ->and($response->headers->get('Location'))
        ->toBe('https://edulawproject.id/riset-publikasi');
});

test('legacy insight slug with a trailing slash redirects directly to its live canonical article', function () {
    $canonicalSlug = 'work-life-balance-di-era-hustle-culture-menakar-perlindungan-hukum-terhadap-hak-atas-kesehatan-mental';
    $legacySlug = 'worklife-balance-di-era-hustle-culture-menakar-perlindungan-hukum-terhadap-hak-atas-kesehatan-mental';

    Insight::query()->create([
        'title' => 'Work Life Balance',
        'slug' => $canonicalSlug,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $request = Request::create('http://localhost/insight/'.$legacySlug.'/');
    $response = app(RedirectWwwToCanonicalHost::class)
        ->handle($request, fn () => response('next'));

    expect($response->getStatusCode())->toBe(301)
        ->and($response->headers->get('Location'))
        ->toBe('http://localhost/insight/'.$canonicalSlug);

    $this->get('/insight/'.$canonicalSlug)->assertOk();
});

test('reported redundant parameters redirect while preserving meaningful filters', function (string $path, string $target) {
    $request = Request::create('https://edulawproject.id'.$path);
    $response = app(RedirectWwwToCanonicalHost::class)->handle($request, fn () => response('next'));
    expect($response->getStatusCode())->toBe(301)
        ->and($response->headers->get('Location'))->toBe('https://edulawproject.id'.$target);
})->with([
    ['/insight?page=1', '/insight'],
    ['/insight?featured=1&page=1', '/insight?featured=1'],
    ['/multimedia?video_page=1', '/multimedia'],
    ['/riset-publikasi?type=semua&view=list', '/riset-publikasi?view=list'],
    ['/insight/example?source=home-highlight', '/insight/example'],
    ['/insight/example?source=home-editor-pick&utm_source=test', '/insight/example?utm_source=test'],
]);

test('www trailing slash and tracking parameters normalize in one hop', function () {
    config(['edulaw.site.url' => 'https://edulawproject.id']);
    $request = Request::create('http://www.edulawproject.id/insight/example/?source=home-highlight&utm_source=test');
    $response = app(RedirectWwwToCanonicalHost::class)->handle($request, fn () => response('next'));
    expect($response->getStatusCode())->toBe(301)
        ->and($response->headers->get('Location'))
        ->toBe('https://edulawproject.id/insight/example?utm_source=test');
});

test('reported category aliases normalize host path and pagination in one redirect', function (string $alias, string $canonical) {
    config(['edulaw.site.url' => 'https://edulawproject.id']);
    $request = Request::create('http://www.edulawproject.id/insight/?archive=latest&category='.$alias.'&page=2');
    $response = app(RedirectWwwToCanonicalHost::class)->handle($request, fn () => response('next'));
    expect($response->getStatusCode())->toBe(301)
        ->and($response->headers->get('Location'))
        ->toBe('https://edulawproject.id/insight/kategori/'.$canonical.'?page=2');
    $category = InsightCategory::create([
        'name' => $canonical, 'slug' => $canonical, 'is_active' => true,
    ]);
    foreach (range(1, 13) as $number) {
        Insight::create([
            'title' => 'Artikel '.$number, 'slug' => 'artikel-'.$number,
            'insight_category_id' => $category->id,
            'status' => 'published', 'published_at' => now()->subDay(),
        ]);
    }
    $this->get('/insight/kategori/'.$canonical.'?page=2')->assertOk();
})->with([
    ['legal-101', 'legal-101'], ['law-101', 'legal-101'],
    ['law-governance', 'law-governance'], ['constitution-governance', 'law-governance'],
    ['legal-insight', 'edulaw-insight'], ['edulaw-insight', 'edulaw-insight'],
    ['regulatory-update', 'regulatory-update'],
]);
