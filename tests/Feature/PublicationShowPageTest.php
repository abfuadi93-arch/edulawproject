<?php

use App\Models\Publication;
use App\Models\PublicationType;
use Illuminate\Support\Facades\Storage;
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

    expect($html)
        ->toContain('property="og:title" content="Judul Riset Fullwidth | Edulaw Project"')
        ->toContain('property="og:type" content="article"')
        ->toContain('property="og:url" content="'.route('publications.show', $publication->slug).'"')
        ->toContain('name="twitter:card" content="summary_large_image"');
});

test('publication detail shows download action only in pdf preview card', function () {
    Storage::fake('public');

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

    Storage::disk('public')->put('publications/dokumen.pdf', '%PDF-1.4 test');

    $html = $this->get(route('publications.show', $publication->slug))
        ->assertOk()
        ->assertSee('Ringkasan Publikasi')
        ->getContent();

    expect(substr_count($html, 'Unduh Publikasi'))->toBe(1);
});

test('published publication pdf can be previewed and downloaded with safe disposition headers', function () {
    Storage::fake('public');
    Storage::disk('public')->put('publications/pdfs/rahasia-server.pdf', '%PDF-1.4 public');

    $publication = Publication::query()->create([
        'title' => 'Kajian Akses Publik',
        'slug' => 'kajian-akses-publik',
        'status' => 'published',
        'published_at' => now()->toDateString(),
        'pdf_file' => 'public/storage/publications/pdfs/rahasia-server.pdf',
    ]);

    $this->get(route('publications.preview', $publication->slug))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertHeader('content-disposition', 'inline; filename=kajian-akses-publik.pdf');

    $download = $this->get(route('publications.download', $publication->slug))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertHeader('content-disposition', 'attachment; filename=kajian-akses-publik.pdf');

    expect($download->headers->get('content-disposition'))
        ->not->toContain('rahasia-server')
        ->not->toContain(storage_path());
});

test('non-published publication files are not publicly accessible', function (string $status) {
    Storage::fake('public');
    Storage::disk('public')->put("publications/{$status}.pdf", '%PDF-1.4 private');

    $publication = Publication::query()->create([
        'title' => "Publikasi {$status}",
        'slug' => "publikasi-{$status}",
        'status' => $status,
        'pdf_file' => "publications/{$status}.pdf",
    ]);

    $this->get(route('publications.show', $publication->slug))->assertNotFound();
    $this->get(route('publications.preview', $publication->slug))->assertNotFound();
    $this->get(route('publications.download', $publication->slug))->assertNotFound();
})->with(['draft', 'reviewed']);

test('missing publication file returns not found without exposing its storage path', function () {
    Storage::fake('public');

    $publication = Publication::query()->create([
        'title' => 'Publikasi Tanpa File',
        'slug' => 'publikasi-tanpa-file',
        'status' => 'published',
        'published_at' => now()->toDateString(),
        'pdf_file' => 'publications/private/missing.pdf',
    ]);

    $response = $this->get(route('publications.download', $publication->slug))
        ->assertNotFound();

    $response->assertDontSee('publications/private/missing.pdf', false);
});
