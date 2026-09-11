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

test('unknown legacy opportunity detail URLs return not found', function () {
    $this->get('/opportunities/peluang-yang-tidak-ada')->assertNotFound();
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
        'additional_link_label' => 'Unduh Guidebook',
        'additional_link_url' => 'https://example.test/guidebook.pdf',
    ]);

    $this->get(route('opportunities.index'))
        ->assertOk()
        ->assertSee('href="'.$opportunity->application_link.'"', false)
        ->assertSee('target="_blank"', false)
        ->assertSee('rel="noopener noreferrer"', false)
        ->assertSee('Penyelenggara Resmi')
        ->assertSee('Mahasiswa hukum')
        ->assertSee('Lihat Informasi Resmi')
        ->assertSee('Unduh Guidebook')
        ->assertSee('href="'.$opportunity->additional_link_url.'"', false)
        ->assertSee('grid-cols-2', false)
        ->assertDontSee('href="'.route('opportunities.show', $opportunity->slug).'"', false);
});

test('opportunity directory hides an incomplete or unsafe additional link', function () {
    Opportunity::query()->create([
        'title' => 'Peluang dengan Tautan Tidak Lengkap',
        'slug' => 'peluang-dengan-tautan-tidak-lengkap',
        'status' => 'open',
        'deadline' => now()->addDays(10)->toDateString(),
        'application_link' => 'https://example.test/resmi',
        'additional_link_label' => 'Pendaftaran Internal',
        'additional_link_url' => 'javascript:alert(1)',
    ]);

    $this->get(route('opportunities.index'))
        ->assertOk()
        ->assertSee('Lihat Informasi Resmi')
        ->assertDontSee('Pendaftaran Internal')
        ->assertDontSee('javascript:alert(1)', false);
});

test('legacy singular opportunity path remains unavailable', function () {
    $this->get('/peluang/peluang-lama')->assertNotFound();
});
