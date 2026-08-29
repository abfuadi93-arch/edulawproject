<?php

test('robots allows public discovery and reserves content from AI training', function () {
    $response = $this->get('/robots.txt')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

    $robots = str_replace("\r\n", "\n", trim($response->getContent()));
    $lines = collect(explode("\n", $robots));
    $disallowedPaths = $lines
        ->filter(fn (string $line): bool => str_starts_with($line, 'Disallow:'))
        ->map(fn (string $line): string => trim(substr($line, strlen('Disallow:'))))
        ->values()
        ->all();

    expect($lines->all())
        ->toContain(
            'User-agent: *',
            'Content-Signal: search=yes, ai-train=no, use=reference',
            'Allow: /',
            'Sitemap: https://edulawproject.id/sitemap.xml',
        )
        ->and($disallowedPaths)->toBe([
            '/admin',
            '/login',
            '/register',
            '/forgot-password',
            '/reset-password',
            '/password/',
            '/email/',
            '/filament/',
            '/search',
        ])
        ->and($disallowedPaths)->not->toContain(
            '/insight',
            '/program',
            '/riset-publikasi',
            '/opportunities',
            '/multimedia',
            '/profil',
        );
});

test('the sitemap referenced by robots remains available', function () {
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
});
