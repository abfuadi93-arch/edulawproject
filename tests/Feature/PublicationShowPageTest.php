<?php

use App\Models\Publication;
use App\Models\PublicationType;
use Illuminate\Support\Str;

test('publication detail hero contains eyebrow and title without body metadata', function () {
    $type = PublicationType::query()->create([
        'name' => 'Policy Brief',
        'slug' => 'policy-brief',
        'is_active' => true,
    ]);

    $publication = Publication::query()->create([
        'publication_type_id' => $type->id,
        'title' => 'Judul Riset Fullwidth',
        'slug' => 'judul-riset-fullwidth',
        'excerpt' => 'Ringkasan lama tidak tampil di hero.',
        'status' => 'published',
        'published_at' => now()->toDateString(),
        'external_url' => 'https://example.test/publikasi.pdf',
    ]);

    $html = $this->get(route('publications.show', $publication->slug))
        ->assertOk()
        ->assertSee('Publikasi &amp; Riset', false)
        ->assertSee('Detail Publikasi')
        ->assertSee('Preview PDF')
        ->assertSee('Buka Sumber Publikasi')
        ->assertSee('Bagikan Publikasi')
        ->assertSee('WhatsApp')
        ->assertSee('Telegram')
        ->assertSee('X/Twitter')
        ->assertSee('Facebook')
        ->assertSee('LinkedIn')
        ->assertSee('Email')
        ->assertSee('Instagram')
        ->assertSee('Salin Link')
        ->getContent();

    $hero = Str::between($html, '<main class="publication-show">', '<section class="publication-body">');

    expect($hero)
        ->toContain('Judul Riset Fullwidth')
        ->toContain('Policy Brief')
        ->not->toContain('Ringkasan lama tidak tampil di hero.')
        ->not->toContain('Baca Ringkasan')
        ->not->toContain('Unduh Publikasi');

    expect($html)
        ->not->toContain('Publikasi Terkait')
        ->not->toContain('Belum ada publikasi terkait')
        ->not->toContain('Gunakan untuk Diskusi');
});

test('publication detail shows download action only in pdf preview card', function () {
    $type = PublicationType::query()->create([
        'name' => 'Policy Brief',
        'slug' => 'policy-brief-download',
        'is_active' => true,
    ]);

    $publication = Publication::query()->create([
        'publication_type_id' => $type->id,
        'title' => 'Dokumen Dengan PDF',
        'slug' => 'dokumen-dengan-pdf',
        'description' => '<p>Ringkasan spesifik untuk publikasi ini.</p>',
        'status' => 'published',
        'published_at' => now()->toDateString(),
        'pdf_file' => 'publications/dokumen.pdf',
    ]);

    $html = $this->get(route('publications.show', $publication->slug))
        ->assertOk()
        ->assertSee('Ringkasan Publikasi')
        ->getContent();

    expect(substr_count($html, 'Unduh Publikasi'))->toBe(1);
});
