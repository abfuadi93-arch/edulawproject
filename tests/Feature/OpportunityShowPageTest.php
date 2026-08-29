<?php

use App\Models\Opportunity;

test('legacy opportunity detail URLs permanently redirect to the directory', function () {
    $opportunity = Opportunity::query()->create([
        'title' => 'Fellowship Riset Hukum',
        'slug' => 'fellowship-riset-hukum',
        'status' => 'open',
        'deadline' => now()->addDays(10)->toDateString(),
        'application_link' => 'https://example.test/daftar',
    ]);

    $this->get(route('opportunities.show', $opportunity->slug))
        ->assertRedirect(route('opportunities.index'))
        ->assertStatus(301);
});

test('unknown legacy opportunity detail URLs also retire safely without a thin page', function () {
    $this->get('/opportunities/peluang-yang-tidak-ada')
        ->assertRedirect(route('opportunities.index'))
        ->assertStatus(301);
});

test('opportunity poster accessors retain multiple posters and legacy fallback', function () {
    $opportunity = new Opportunity([
        'poster' => 'opportunities/poster-lama.jpg',
        'posters' => ['opportunities/poster-baru.jpg'],
    ]);

    expect($opportunity->poster_paths)->toBe([
        'opportunities/poster-baru.jpg',
    ])
        ->and($opportunity->poster_urls)->toHaveCount(1)
        ->and($opportunity->poster_url)->toContain('opportunities/poster-baru.jpg');
});

test('opportunity directory links directly to the official source', function () {
    $opportunity = Opportunity::query()->create([
        'title' => 'Kompetisi Peradilan Semu',
        'slug' => 'kompetisi-peradilan-semu',
        'organizer' => 'Penyelenggara Resmi',
        'eligibility' => ['Mahasiswa hukum'],
        'status' => 'open',
        'deadline' => now()->addDays(10)->toDateString(),
        'application_link' => 'https://example.test/daftar',
    ]);

    $this->get(route('opportunities.index'))
        ->assertOk()
        ->assertSee('href="'.$opportunity->application_link.'"', false)
        ->assertSee('target="_blank"', false)
        ->assertSee('rel="noopener noreferrer"', false)
        ->assertSee('Penyelenggara Resmi')
        ->assertSee('Mahasiswa hukum')
        ->assertSee('Lihat Informasi Resmi')
        ->assertDontSee('href="'.route('opportunities.show', $opportunity->slug).'"', false);
});

test('legacy singular opportunity path remains unavailable', function () {
    $this->get('/peluang/peluang-lama')->assertNotFound();
});
