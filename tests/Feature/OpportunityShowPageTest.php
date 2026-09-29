<?php

use App\Models\Opportunity;

test('opportunity detail page renders public information and official actions', function () {
    $opportunity = Opportunity::query()->create([
        'title' => 'Fellowship Riset Hukum',
        'slug' => 'fellowship-riset-hukum',
        'type' => 'fellowship',
        'organizer' => 'Lembaga Riset Hukum',
        'excerpt' => 'Program fellowship untuk peneliti hukum muda.',
        'description' => '<p>Program pengembangan kapasitas riset hukum.</p>',
        'format' => 'hybrid',
        'location' => 'Jakarta',
        'eligibility' => ['Mahasiswa hukum', 'Peneliti muda'],
        'benefits' => ['Mentoring', 'Dukungan riset'],
        'status' => 'open',
        'deadline' => now()->addDays(10)->toDateString(),
        'application_link' => 'https://example.test/daftar',
    ]);

    $response = $this->get(route('opportunities.show', $opportunity->slug))
        ->assertOk()
        ->assertSee('Fellowship Riset Hukum')
        ->assertSee('Tentang Peluang')
        ->assertSee('Detail Peluang')
        ->assertSee('Kriteria peserta')
        ->assertSee('Yang diperoleh')
        ->assertSee('Bagikan Peluang')
        ->assertSee('href="https://example.test/daftar"', false)
        ->assertSee('rel="noopener noreferrer"', false);

    expect($response->headers->get('Cache-Control'))
        ->toContain('private')
        ->toContain('no-store');
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
        'second_link_label' => 'Form Pendaftaran',
        'second_link_url' => 'https://example.test/form',
    ]);

    $this->get(route('opportunities.index'))
        ->assertOk()
        ->assertSee('href="'.$opportunity->application_link.'"', false)
        ->assertSee('target="_blank"', false)
        ->assertSee('rel="noopener noreferrer"', false)
        ->assertSee('Penyelenggara Resmi')
        ->assertSee('Mahasiswa hukum')
        ->assertSee('Informasi Resmi')
        ->assertSee('Unduh Guidebook')
        ->assertSee('Form Pendaftaran')
        ->assertSee('href="https://example.test/form"', false)
        ->assertSee('href="'.$opportunity->additional_link_url.'"', false)
        ->assertDontSee('Lihat Detail')
        ->assertSee('data-card-detail-link', false)
        ->assertSee('href="'.route('opportunities.show', $opportunity->slug).'"', false);
});

test('archived opportunity detail is not public while closed detail remains readable', function () {
    $closed = Opportunity::query()->create([
        'title' => 'Peluang yang Telah Ditutup',
        'slug' => 'peluang-yang-telah-ditutup',
        'status' => 'closed',
    ]);
    $archived = Opportunity::query()->create([
        'title' => 'Peluang Arsip Internal',
        'slug' => 'peluang-arsip-internal',
        'status' => 'archived',
    ]);

    $this->get(route('opportunities.show', $closed->slug))
        ->assertOk()
        ->assertSee('Sudah Ditutup');
    $this->get(route('opportunities.show', $archived->slug))->assertNotFound();
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
        'second_link_label' => 'Tautan Kedua Tidak Aman',
        'second_link_url' => 'javascript:alert(2)',
    ]);

    $this->get(route('opportunities.index'))
        ->assertOk()
        ->assertSee('Informasi Resmi')
        ->assertDontSee('Pendaftaran Internal')
        ->assertDontSee('Tautan Kedua Tidak Aman')
        ->assertDontSee('javascript:alert(2)', false)
        ->assertDontSee('javascript:alert(1)', false);
});

test('legacy singular opportunity path remains unavailable', function () {
    $this->get('/peluang/peluang-lama')->assertNotFound();
});

test('email application links render on regular and featured opportunity cards', function () {
    $opportunity = Opportunity::query()->create([
        'title' => 'Junior Researcher',
        'slug' => 'junior-researcher-mailto',
        'status' => 'open',
        'application_link' => 'https://juristresia.com',
        'additional_link_label' => 'Kirim Lamaran',
        'additional_link_url' => 'mailto:contact@juristresia.com',
        'second_link_label' => 'Email Pertanyaan',
        'second_link_url' => 'mailto:contact@juristresia.com?subject=Pertanyaan',
    ]);

    expect($opportunity->additional_url)->toBe('mailto:contact@juristresia.com');

    foreach (['card', 'featured-card'] as $component) {
        $this->blade('<x-opportunities.'.$component.' :opportunity="$opportunity" />', ['opportunity' => $opportunity])
            ->assertSee('href="mailto:contact@juristresia.com"', false)
            ->assertSee('href="mailto:contact@juristresia.com?subject=Pertanyaan"', false);
    }
});
