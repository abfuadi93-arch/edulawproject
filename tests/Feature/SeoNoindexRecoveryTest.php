<?php

use App\Models\Author;
use App\Models\Insight;
use App\Models\Program;
use App\Models\Publication;
use App\Support\PublicContentIndexability;
use Illuminate\Support\Facades\Storage;

test('concise supported pages and their canonical URLs are included in the sitemap', function () {
    Storage::fake('public');
    Storage::disk('public')->put('publications/papers/research.pdf', '%PDF-1.4 test');
    $publication = Publication::query()->create([
        'title' => 'Mandat Presiden', 'slug' => 'mandat-presiden',
        'description' => '<p>&nbsp;</p>',
        'excerpt' => 'Kajian ini mengusulkan mandat administratif untuk mengatasi kekosongan pengaturan Presiden berhalangan sementara.',
        'pdf_file' => 'publications/papers/research.pdf', 'cover_image' => 'cover.jpg',
        'status' => 'published', 'published_at' => now()->subDay(),
    ]);
    $program = Program::query()->create([
        'name' => 'Simulasi Konstitusi', 'slug' => 'simulasi-konstitusi',
        'description' => 'Peserta mempraktikkan penyusunan permohonan dan argumentasi dalam simulasi persidangan konstitusi.',
        'learning_points' => ['Menyusun permohonan pengujian undang-undang', 'Menguji kedudukan hukum pemohon'],
        'event_date' => now()->addWeek(), 'publication_status' => 'published', 'status' => 'upcoming',
    ]);
    $insight = Insight::query()->create([
        'title' => 'Kontribusi Publik', 'slug' => 'kontribusi-publik', 'status' => 'published',
        'content' => str_repeat('Analisis hukum dan kebijakan publik berbasis bukti. ', 50), 'published_at' => now()->subDay(),
    ]);
    $author = Author::query()->create([
        'name' => 'Peneliti Hukum', 'slug' => 'peneliti-hukum', 'bio' => 'Peneliti hukum tata negara dengan fokus pada pembatasan kekuasaan eksekutif.',
        'is_active' => true, 'show_in_contributor_section' => true,
    ]);
    $author->insights()->attach($insight);
    $sitemap = $this->get(route('sitemap'))->assertOk();
    foreach ([route('publications.show', $publication->slug), route('programs.show', $program->slug), route('profiles.show', $author->slug)] as $url) {
        $this->get($url)->assertOk()->assertSee('<meta name="robots" content="index,follow">', false)
            ->assertSee('<link rel="canonical" href="'.$url.'">', false);
        $sitemap->assertSee($url, false);
    }
    $this->get(route('publications.show', $publication->slug))->assertSee($publication->excerpt);
    $this->get(route('publications.download', $publication->slug))->assertHeader('X-Robots-Tag', 'noindex, noarchive');
    $publication->update(['status' => 'draft']);
    $program->update(['publication_status' => 'draft']);
    $author->update(['is_active' => false]);
    foreach ([route('publications.show', $publication->slug), route('programs.show', $program->slug), route('profiles.show', $author->slug)] as $url) {
        $this->get($url)->assertNotFound();
        $this->get(route('sitemap'))->assertDontSee($url, false);
    }
});

test('a dated external publication may be concise but placeholders and missing documents stay excluded', function () {
    Storage::fake('public');
    $publication = new Publication([
        'title' => 'Kajian Konstitusi', 'slug' => 'kajian-konstitusi',
        'excerpt' => 'Kajian ini membandingkan kepastian hukum dan keadilan dalam pembentukan peraturan darurat.',
        'external_url' => 'https://example.org/journal/article', 'published_at' => now()->subDay(),
    ]);
    expect(PublicContentIndexability::publication($publication))->toBeTrue();
    $publication->excerpt = '<p>Konten sedang disiapkan.</p>';
    expect(PublicContentIndexability::publication($publication))->toBeFalse();
    $publication->excerpt = $publication->title;
    expect(PublicContentIndexability::publication($publication))->toBeFalse();
    $publication->excerpt = 'Kajian ini membahas mekanisme pengujian peraturan darurat oleh Mahkamah Konstitusi.';
    $publication->external_url = null;
    $publication->pdf_file = 'publications/missing.pdf';
    expect(PublicContentIndexability::publication($publication))->toBeFalse();
    expect(PublicContentIndexability::author(new Author(['name' => 'Tim', 'slug' => 'tim', 'bio' => 'Peneliti hukum konstitusi.']), 0, 0))->toBeFalse();
});

test('all-type normalization combines legacy host path and preserves search and pagination', function () {
    config(['edulaw.site.url' => 'https://edulawproject.id']);
    $this->get('http://www.edulawproject.id/publikasi/?type=semua&page=2&q=konstitusi&view=list')
        ->assertMovedPermanently()
        ->assertRedirect('https://edulawproject.id/riset-publikasi?page=2&q=konstitusi&view=list');
});

test('tracking is ignored but meaningful filters and out of range pagination are preserved', function () {
    foreach (range(1, 13) as $i) {
        Publication::query()->create(['title' => "Riset {$i}", 'slug' => "riset-{$i}", 'status' => 'published']);
    }
    $this->get(route('publications.index', ['page' => 2, 'source' => 'newsletter', 'utm_source' => 'email', 'view' => 'list']))
        ->assertOk()->assertSee('<meta name="robots" content="index,follow">', false)
        ->assertSee('<link rel="canonical" href="'.route('publications.index', ['page' => 2]).'">', false);
    $this->get(route('publications.index', ['q' => 'Riset', 'utm_source' => 'email']))
        ->assertOk()->assertSee('<meta name="robots" content="noindex,follow">', false);
    $this->get(route('publications.index', ['page' => 999, 'view' => 'list']))->assertNotFound();
});

test('program evidence does not make empty or placeholder descriptions indexable', function () {
    $program = new Program([
        'name' => 'Pelatihan Argumentasi', 'slug' => 'pelatihan-argumentasi',
        'description' => 'Peserta berlatih menyusun argumentasi hukum dan mengevaluasi sanggahan melalui simulasi.',
        'method' => 'Simulasi debat dengan umpan balik fasilitator.',
        'output' => 'Peserta menyusun naskah argumentasi dan mempraktikkannya.',
    ]);
    expect(PublicContentIndexability::program($program))->toBeTrue();
    $program->method = null;
    $program->output = null;
    expect(PublicContentIndexability::program($program))->toBeFalse();
    $program->event_date = now()->addWeek();
    $program->organizer_name = 'Komunitas Peneliti';
    $program->location = 'Yogyakarta';
    expect(PublicContentIndexability::program($program))->toBeTrue();
    foreach (['<p>&nbsp;</p>', 'Informasi sedang disiapkan.', $program->name] as $description) {
        $program->description = $description;
        expect(PublicContentIndexability::program($program))->toBeFalse();
    }
});

test('a scheduled publication with supporting evidence is still not public or in the sitemap', function () {
    $publication = Publication::query()->create([
        'title' => 'Riset Terjadwal', 'slug' => 'riset-terjadwal',
        'excerpt' => 'Kajian ini mengevaluasi batas kewenangan eksekutif dalam keadaan darurat.',
        'external_url' => 'https://example.org/research',
        'status' => 'published', 'published_at' => now()->addWeek(),
    ]);
    $url = route('publications.show', $publication->slug);
    $this->get($url)->assertNotFound();
    $this->get(route('sitemap'))->assertOk()->assertDontSee($url, false);
});
