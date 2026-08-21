<?php

use App\Http\Middleware\RedirectWwwToCanonicalHost;
use App\Models\Insight;
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
