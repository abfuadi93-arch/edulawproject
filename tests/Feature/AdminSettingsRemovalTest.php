<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

test('site settings and content blocks admin routes are not registered', function () {
    $adminUris = collect(Route::getRoutes())
        ->map(fn ($route): string => $route->uri())
        ->filter(fn (string $uri): bool => str_starts_with($uri, 'admin'))
        ->values();

    expect($adminUris->contains(fn (string $uri): bool => str_contains($uri, 'site-settings')))->toBeFalse()
        ->and($adminUris->contains(fn (string $uri): bool => str_contains($uri, 'content-blocks')))->toBeFalse();
});

test('main public pages render with static site configuration', function (string $path) {
    $this->get($path)->assertOk();
})->with([
    '/',
    '/insight',
    '/riset-publikasi',
    '/program',
    '/opportunities',
    '/multimedia',
]);

test('main public pages do not query dynamic settings or content block tables', function (string $path) {
    $queries = [];

    DB::listen(function ($query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $this->get($path)->assertOk();

    $legacyQueries = collect($queries)
        ->filter(fn (string $sql): bool => str_contains($sql, 'site_settings') || str_contains($sql, 'content_blocks'));

    expect($legacyQueries)->toBeEmpty();
})->with([
    '/',
    '/insight',
    '/riset-publikasi',
    '/program',
    '/opportunities',
    '/multimedia',
]);

test('admin entry points remain available behind authentication', function (string $path) {
    $this->get($path)->assertRedirect('/admin/login');
})->with([
    '/admin',
    '/admin/users',
]);
