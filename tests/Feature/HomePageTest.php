<?php

use App\Models\Author;
use App\Models\Insight;
use App\Models\Multimedia;
use App\Models\Opportunity;
use App\Models\Program;
use App\Models\Publication;
use Illuminate\Support\Facades\Route;

it('opens the homepage without leaking template expressions or invalid links', function () {
    $response = $this->get(route('home'));
    $response
        ->assertOk()
        ->assertDontSee('{{ insight.created_date }}', false)
        ->assertDontSee('{{ insight.author?.role_name }}', false)
        ->assertDontSee('{{ insight?.title }}', false)
        ->assertDontSee('{{ insight?.excerpt }}', false)
        ->assertDontSee('{{ publication.type }}', false)
        ->assertDontSee('{{ publication.title }}', false)
        ->assertDontSee('{{ publication.excerpt }}', false)
        ->assertDontSee('{{ publication.download_count }}', false)
        ->assertSee(route('insights.index'), false)
        ->assertSee(route('publications.index'), false)
        ->assertSee(route('programs.index'), false)
        ->assertSee(route('collaboration.index'), false)
        ->assertSee(route('privacy'), false)
        ->assertSee(route('terms'), false);

    preg_match_all('/\shref="([^"]*)"/i', $response->getContent(), $matches);

    expect($matches[1])
        ->not->toContain('', '#')
        ->each->not->toStartWith('javascript:');
});

it('places Tentang after Multimedia in the primary navigation', function () {
    $response = $this->get(route('home'));
    $document = new DOMDocument;
    $document->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
    $xpath = new DOMXPath($document);
    $items = collect($xpath->query('//nav[@aria-label="Navigasi utama"]/a'))
        ->map(fn (DOMNode $node): string => trim($node->textContent))
        ->values()
        ->all();

    expect($items)->toBe([
        'Program',
        'Editorial',
        'Riset & Publikasi',
        'Opportunities',
        'Multimedia',
        'Tentang',
    ]);
});

it('shows compact empty states and omits unavailable publication statistics', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Insight terbaru sedang disiapkan.')
        ->assertSee('Lihat Semua Insight')
        ->assertSee('Publikasi sedang disiapkan.')
        ->assertSee('Program terbaru sedang disiapkan.')
        ->assertDontSee('Peluang aktif dengan tenggat terdekat')
        ->assertDontSee('Belajar Hukum Melalui Beragam Format')
        ->assertDontSee('Total Unduhan')
        ->assertDontSee('Dokumen Tersedia')
        ->assertDontSee('Terpopuler (30 hari)')
        ->assertDontSee('Unduh atau buka publikasi');
});

it('renders available homepage data safely without inventing missing metadata', function () {
    $insight = Insight::query()->create([
        'title' => 'Analisis Hukum <script>alert("x")</script>',
        'slug' => 'analisis-hukum-aman',
        'excerpt' => 'Ringkasan analisis yang tersedia.',
        'content' => 'Isi analisis.',
        'status' => 'published',
        'published_at' => now()->subDay(),
        'reading_time' => 7,
        'featured' => true,
    ]);

    $publication = Publication::query()->create([
        'title' => 'Kajian Kebijakan Digital',
        'slug' => 'kajian-kebijakan-digital',
        'excerpt' => 'Ringkasan kajian.',
        'status' => 'published',
        'published_at' => now()->toDateString(),
        'featured' => true,
    ]);

    $program = Program::query()->create([
        'name' => 'Kelas Hukum Publik',
        'slug' => 'kelas-hukum-publik',
        'short_description' => 'Kelas untuk memahami hukum publik.',
        'event_date' => now()->addWeek(),
        'status' => 'upcoming',
        'publication_status' => 'published',
        'featured' => true,
    ]);

    $response = $this->get(route('home'));
    $document = new DOMDocument;
    $document->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
    $xpath = new DOMXPath($document);

    $response
        ->assertOk()
        ->assertSee($insight->title)
        ->assertSee(route('insights.show', $insight->slug), false)
        ->assertSee($publication->title)
        ->assertSee(route('publications.show', $publication->slug), false)
        ->assertSee($program->name)
        ->assertSee(route('programs.show', $program->slug), false)
        ->assertDontSee('<script>alert("x")</script>', false)
        ->assertDontSee('Unduh atau buka publikasi')
        ->assertDontSee('PDF')
        ->assertDontSee('12 hlm');

    expect($xpath->query('//article[@data-home-publication]//a//a')->length)->toBe(0);
});

it('prioritizes ongoing programs and limits the homepage program section to three active records', function () {
    $ongoing = Program::query()->create([
        'name' => 'Program Sedang Berjalan',
        'slug' => 'program-sedang-berjalan',
        'event_date' => now()->subDay(),
        'end_date' => now()->addDay(),
        'status' => 'ongoing',
        'publication_status' => 'published',
    ]);

    $upcomingPrograms = collect(range(1, 4))->map(fn (int $position) => Program::query()->create([
        'name' => "Program Mendatang {$position}",
        'slug' => "program-mendatang-{$position}",
        'event_date' => now()->addDays($position),
        'status' => 'upcoming',
        'publication_status' => 'published',
    ]));

    $archived = Program::query()->create([
        'name' => 'Program Arsip Beranda',
        'slug' => 'program-arsip-beranda',
        'event_date' => now()->subMonth(),
        'status' => 'archived',
        'publication_status' => 'published',
    ]);

    $response = $this->get(route('home'));
    $html = $response->getContent();
    $response
        ->assertOk()
        ->assertSeeInOrder([
            $ongoing->name,
            $upcomingPrograms[0]->name,
            $upcomingPrograms[1]->name,
        ])
        ->assertDontSee($upcomingPrograms[2]->name)
        ->assertDontSee($upcomingPrograms[3]->name)
        ->assertDontSee($archived->name)
        ->assertSee(route('programs.index'), false)
        ->assertSee(route('programs.show', $ongoing->slug), false);

    expect(substr_count($html, 'data-home-program'))->toBe(3);
});

it('shows at most four published insights without duplicating the featured article', function () {
    $featured = Insight::query()->create([
        'title' => 'Insight Utama Beranda',
        'slug' => 'insight-utama-beranda',
        'status' => 'published',
        'published_at' => now()->subMonth(),
        'featured' => true,
    ]);

    collect(range(1, 4))->each(fn (int $position) => Insight::query()->create([
        'title' => "Insight Published {$position}",
        'slug' => "insight-published-{$position}",
        'status' => 'published',
        'published_at' => now()->subDays($position),
    ]));

    $draft = Insight::query()->create([
        'title' => 'Insight Draft Beranda',
        'slug' => 'insight-draft-beranda',
        'status' => 'draft',
        'published_at' => now(),
    ]);

    $reviewed = Insight::query()->create([
        'title' => 'Insight Reviewed Beranda',
        'slug' => 'insight-reviewed-beranda',
        'status' => 'reviewed',
        'published_at' => now(),
    ]);

    $response = $this->get(route('home'));
    $html = $response->getContent();
    $featuredHref = 'href="'.route('insights.show', $featured->slug).'"';

    $response
        ->assertOk()
        ->assertSee($featured->title)
        ->assertDontSee($draft->title)
        ->assertDontSee($reviewed->title)
        ->assertSee(route('insights.index'), false);

    expect(substr_count($html, '<article data-home-insight '))->toBe(4)
        ->and(substr_count($html, 'data-home-insight-featured'))->toBe(1)
        ->and(substr_count($html, 'data-home-insight-compact'))->toBe(3)
        ->and(substr_count($html, $featuredHref))->toBe(1);
});

it('limits publications to four published records and excludes non-published records', function () {
    $published = collect(range(1, 5))->map(fn (int $position) => Publication::query()->create([
        'title' => "Publikasi Published {$position}",
        'slug' => "publikasi-published-{$position}",
        'status' => 'published',
        'published_at' => now()->subDays($position)->toDateString(),
    ]));

    $draft = Publication::query()->create([
        'title' => 'Publikasi Draft Beranda',
        'slug' => 'publikasi-draft-beranda',
        'status' => 'draft',
    ]);

    $reviewed = Publication::query()->create([
        'title' => 'Publikasi Reviewed Beranda',
        'slug' => 'publikasi-reviewed-beranda',
        'status' => 'reviewed',
    ]);

    $response = $this->get(route('home'));
    $html = $response->getContent();

    $response
        ->assertOk()
        ->assertSee($published[0]->title)
        ->assertSee($published[1]->title)
        ->assertSee($published[2]->title)
        ->assertSee($published[3]->title)
        ->assertDontSee($published[4]->title)
        ->assertDontSee($draft->title)
        ->assertDontSee($reviewed->title)
        ->assertSee(route('publications.index'), false)
        ->assertSee(route('publications.show', $published[0]->slug), false);

    expect(substr_count($html, 'data-home-publication'))->toBe(4);
});

it('renders the about section followed by the active collaboration call to action', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSeeInOrder([
            'Untuk Siapa Edulaw',
            'Program Edulaw',
            'Edulaw Insight Terbaru',
            'Riset &amp; Publikasi Pilihan',
            'Tentang Edulaw',
            'Bangun ruang literasi hukum bersama Edulaw Project.',
        ], false)
        ->assertSee('Tentang Edulaw')
        ->assertSee('Ruang belajar dan riset hukum untuk kepentingan publik.')
        ->assertSee(route('about'), false)
        ->assertSee('Ajukan Kerja Sama')
        ->assertSee(route('collaboration.index'), false)
        ->assertDontSee('Open Submission')
        ->assertDontSee('Kanal pengiriman tulisan belum dibuka.');
});

it('uses canonical hero calls to action and valid contextual audience anchors', function () {
    Multimedia::query()->create([
        'title' => 'Video untuk Anchor Multimedia',
        'type' => 'video',
        'platform' => 'youtube',
        'media_url' => 'https://youtube.com/watch?v=anchor12345',
        'status' => 'published',
    ]);

    $response = $this->get(route('home'));
    $html = $response->getContent();
    $document = new DOMDocument;
    $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
    $xpath = new DOMXPath($document);

    $response
        ->assertOk()
        ->assertSee('Jelajahi Editorial')
        ->assertSee('Lihat Program')
        ->assertSee('Ajukan Kolaborasi')
        ->assertSee('id="program-edulaw"', false)
        ->assertSee('id="edulaw-insight"', false)
        ->assertSee('id="riset-publikasi"', false)
        ->assertSee('id="multimedia"', false);

    expect($xpath->query('//*[@data-home-hero]//a[@href="'.route('insights.index').'"]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-home-hero]//a[@href="'.route('programs.index').'"]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-home-hero]//a[@href="'.route('collaboration.index').'"]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-home-audience-card]/a[@href="#program-edulaw"]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-home-audience-card]/a[@href="#riset-publikasi"]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-home-audience-card]/a[@href="#edulaw-insight"]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-home-audience-card]/a[@href="#multimedia"]')->length)->toBe(1);
});

it('shows all dynamic credibility statistics using only the correct public record statuses', function () {
    Insight::query()->create([
        'title' => 'Insight Kredibilitas',
        'slug' => 'insight-kredibilitas',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    Insight::query()->create([
        'title' => 'Insight Draf Statistik',
        'slug' => 'insight-draf-statistik',
        'status' => 'draft',
    ]);

    Publication::query()->create([
        'title' => 'Publikasi Kredibilitas',
        'slug' => 'publikasi-kredibilitas',
        'status' => 'published',
        'published_at' => now()->toDateString(),
    ]);
    Publication::query()->create([
        'title' => 'Publikasi Reviewed Statistik',
        'slug' => 'publikasi-reviewed-statistik',
        'status' => 'reviewed',
    ]);

    Multimedia::query()->create([
        'title' => 'Video Statistik Publik',
        'slug' => 'video-statistik-publik',
        'type' => 'video',
        'platform' => 'youtube',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    Multimedia::query()->create([
        'title' => 'Video Statistik Draf',
        'slug' => 'video-statistik-draf',
        'type' => 'video',
        'platform' => 'youtube',
        'status' => 'draft',
    ]);

    Program::query()->create([
        'name' => 'Program Terlaksana Statistik',
        'slug' => 'program-terlaksana-statistik',
        'status' => 'archived',
        'publication_status' => 'published',
    ]);
    Program::query()->create([
        'name' => 'Program Arsip Draf Statistik',
        'slug' => 'program-arsip-draf-statistik',
        'status' => 'archived',
        'publication_status' => 'draft',
    ]);

    Author::query()->create([
        'name' => 'Kontributor Aktif Statistik',
        'slug' => 'kontributor-aktif-statistik',
        'is_active' => true,
    ]);
    Author::query()->create([
        'name' => 'Kontributor Nonaktif Statistik',
        'slug' => 'kontributor-nonaktif-statistik',
        'is_active' => false,
    ]);

    Opportunity::query()->create([
        'title' => 'Peluang Statistik Aktif',
        'slug' => 'peluang-statistik-aktif',
        'status' => 'open',
        'deadline' => today()->addWeek(),
    ]);
    Opportunity::query()->create([
        'title' => 'Peluang Statistik Kedaluwarsa',
        'slug' => 'peluang-statistik-kedaluwarsa',
        'status' => 'open',
        'deadline' => today()->subDay(),
    ]);

    $response = $this->get(route('home'))->assertOk();
    $html = $response->getContent();
    $document = new DOMDocument;
    $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
    $xpath = new DOMXPath($document);

    expect(substr_count($html, 'data-home-stat='))->toBe(6);

    foreach (['Insight Terbit', 'Program Edulaw', 'Riset & Publikasi', 'Konten Multimedia', 'Kontributor Aktif', 'Peluang Aktif'] as $label) {
        $response->assertSee('data-home-stat="'.e($label).'"', false);
        expect(trim($xpath->query('//*[@data-home-stat="'.$label.'"]/dd')->item(0)?->textContent ?? ''))->toBe('1');
    }
});

it('keeps every dynamic credibility statistic visible when its value is zero', function () {
    Insight::query()->create([
        'title' => 'Satu-satunya Statistik',
        'slug' => 'satu-satunya-statistik',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('home'))->assertOk();

    expect(substr_count($response->getContent(), 'data-home-stat='))->toBe(6);
    $response
        ->assertSee('data-home-stat="Insight Terbit"', false)
        ->assertSee('data-home-stat="Riset &amp; Publikasi"', false)
        ->assertSee('data-home-stat="Peluang Aktif"', false);
});

it('keeps identity, SEO, footer legal links, and dynamic copyright robust', function () {
    $response = $this->get(route('home'));
    $html = $response->getContent();

    $response
        ->assertOk()
        ->assertSee('Edulaw Project')
        ->assertSee('rel="canonical" href="'.route('home').'"', false)
        ->assertSee('property="og:url" content="'.route('home').'"', false)
        ->assertSee('application/ld+json', false)
        ->assertSee(route('privacy'), false)
        ->assertSee(route('terms'), false)
        ->assertSee('© '.now()->year.' Edulaw Project', false)
        ->assertDontSee('Disclaimer')
        ->assertDontSee('PT Edu Kreasi Nusantara');

    preg_match_all('/\shref="([^"]*)"/i', $html, $matches);

    expect($matches[1])->not->toContain('', '#');
});

it('only exposes the available public legal pages from the footer', function () {
    $this->get(route('privacy'))
        ->assertOk()
        ->assertSee('Kebijakan Privasi');

    $this->get(route('terms'))
        ->assertOk()
        ->assertSee('Syarat &amp; Ketentuan', false);

    expect(Route::has('disclaimer'))->toBeFalse()
        ->and(Route::has('writer-guidelines'))->toBeFalse();
});

it('does not fail when optional site identity and contact settings are null', function () {
    config([
        'edulaw.site.short_description' => null,
        'edulaw.site.tagline' => null,
        'edulaw.site.footer_logo' => null,
        'edulaw.contact.email' => null,
        'edulaw.contact.whatsapp_label' => null,
        'edulaw.contact.whatsapp_url' => null,
        'edulaw.contact.location' => null,
        'edulaw.social.instagram_url' => null,
        'edulaw.social.linkedin_url' => null,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Edulaw Project')
        ->assertDontSee('mailto:', false)
        ->assertDontSee('wa.me', false);
});

it('shows the four intended audience groups in the final homepage order', function () {
    $response = $this->get(route('home'));
    $html = $response->getContent();

    $response
        ->assertOk()
        ->assertSeeInOrder([
            'Untuk Siapa Edulaw',
            'Mahasiswa Hukum',
            'Peneliti &amp; Akademisi',
            'Praktisi &amp; Profesional',
            'Masyarakat &amp; Komunitas',
            'Program Edulaw',
        ], false);

    expect(substr_count($html, 'data-home-audience-card'))->toBe(4);
});

it('shows at most three open opportunities by nearest deadline with direct external links', function () {
    $opportunities = collect(range(1, 5))->map(fn (int $position) => Opportunity::query()->create([
        'title' => "Peluang Aktif {$position}",
        'slug' => "peluang-aktif-{$position}",
        'type' => 'fellowship',
        'excerpt' => "Ringkasan peluang {$position}.",
        'deadline' => now()->addDays($position)->toDateString(),
        'application_link' => "https://example.test/apply/{$position}",
        'status' => 'open',
    ]));

    $expired = Opportunity::query()->create([
        'title' => 'Peluang Kedaluwarsa',
        'slug' => 'peluang-kedaluwarsa',
        'deadline' => now()->subDay()->toDateString(),
        'application_link' => 'https://example.test/expired',
        'status' => 'open',
    ]);

    $closed = Opportunity::query()->create([
        'title' => 'Peluang Ditutup',
        'slug' => 'peluang-ditutup',
        'deadline' => now()->addDay()->toDateString(),
        'application_link' => 'https://example.test/closed',
        'status' => 'closed',
    ]);

    $invalid = Opportunity::query()->create([
        'title' => 'Peluang Tanpa Tautan Eksternal',
        'slug' => 'peluang-tanpa-tautan-eksternal',
        'deadline' => now()->addDay()->toDateString(),
        'application_link' => '/peluang/internal',
        'status' => 'open',
    ]);

    $response = $this->get(route('home'));
    $html = $response->getContent();

    $response
        ->assertOk()
        ->assertSeeInOrder($opportunities->take(3)->pluck('title')->all())
        ->assertDontSee($opportunities[3]->title)
        ->assertDontSee($opportunities[4]->title)
        ->assertDontSee($expired->title)
        ->assertDontSee($closed->title)
        ->assertDontSee($invalid->title)
        ->assertSee('href="https://example.test/apply/1"', false)
        ->assertSee('target="_blank"', false)
        ->assertSee('rel="noopener noreferrer"', false);

    expect(substr_count($html, 'data-home-opportunity>'))->toBe(3)
        ->and(substr_count($html, 'data-home-opportunity-fallback'))->toBe(3);
});

it('keeps a single expired open opportunity compact and deprioritizes it behind current deadlines', function () {
    $expired = Opportunity::query()->create([
        'title' => 'Peluang Open Lewat Tenggat',
        'slug' => 'peluang-open-lewat-tenggat',
        'type' => 'fellowship',
        'deadline' => now()->subDay()->toDateString(),
        'application_link' => 'https://example.test/expired-open',
        'status' => 'open',
    ]);

    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee($expired->title)
        ->assertSee('Tenggat telah lewat')
        ->assertSee('max-w-4xl', false);
});

it('shows one featured and at most three secondary multimedia teasers', function () {
    $items = collect(range(1, 4))->map(fn (int $position) => Multimedia::query()->create([
        'title' => "Multimedia Terbaru {$position}",
        'slug' => "multimedia-terbaru-{$position}",
        'type' => 'video',
        'description' => "Deskripsi multimedia {$position}.",
        'media_url' => "https://video.example.test/watch/{$position}",
        'platform' => 'youtube',
        'published_at' => now()->subDays($position),
        'status' => 'published',
    ]));

    $draft = Multimedia::query()->create([
        'title' => 'Multimedia Draft',
        'slug' => 'multimedia-draft',
        'type' => 'video',
        'media_url' => 'https://video.example.test/draft',
        'published_at' => now(),
        'status' => 'draft',
    ]);

    $withoutLink = Multimedia::query()->create([
        'title' => 'Multimedia Tanpa Tautan',
        'slug' => 'multimedia-tanpa-tautan',
        'type' => 'video',
        'media_url' => '',
        'platform' => 'youtube',
        'published_at' => now(),
        'status' => 'published',
    ]);

    $response = $this->get(route('home'));
    $html = $response->getContent();

    $response
        ->assertOk()
        ->assertSeeInOrder($items->pluck('title')->all())
        ->assertDontSee($draft->title)
        ->assertDontSee($withoutLink->title)
        ->assertSee(route('multimedia.index'), false)
        ->assertSee('Belajar Hukum Melalui Beragam Format')
        ->assertSee('Lihat Semua Multimedia')
        ->assertDontSee('<iframe', false)
        ->assertDontSee('autoplay', false);

    $multimediaSection = substr(
        $html,
        strpos($html, '<section id="multimedia"'),
        strpos($html, '<section id="tentang-edulaw"') - strpos($html, '<section id="multimedia"'),
    );

    expect(substr_count($html, 'data-home-multimedia '))->toBe(4)
        ->and(substr_count($html, 'data-home-multimedia-featured'))->toBe(1)
        ->and(substr_count($html, 'data-home-multimedia-secondary'))->toBe(3)
        ->and($multimediaSection)->toContain('from-slate-950/95')
        ->and($multimediaSection)->toContain('bg-slate-950/55')
        ->and($multimediaSection)->toContain('h-20 w-28')
        ->and($multimediaSection)->not->toContain('home-empty-state');
});

it('hides the homepage multimedia section when there is no published youtube video', function () {
    Multimedia::query()->create([
        'title' => 'Multimedia Draft Saja',
        'type' => 'video',
        'platform' => 'youtube',
        'media_url' => 'https://youtube.com/watch?v=draftonly1',
        'status' => 'draft',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Belajar Hukum Melalui Beragam Format')
        ->assertDontSee('data-home-multimedia', false);
});

it('provides semantic landmarks, a single page heading, and accessible mobile navigation controls', function () {
    $response = $this->get(route('home'));
    $html = $response->getContent();
    $document = new DOMDocument;
    $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
    $xpath = new DOMXPath($document);

    $response
        ->assertOk()
        ->assertSee('href="#main-content"', false)
        ->assertSee('id="main-content"', false)
        ->assertSee('aria-label="Navigasi utama"', false)
        ->assertSee('aria-label="Navigasi mobile"', false)
        ->assertSee('aria-controls="mobile-navigation"', false)
        ->assertSee('id="mobile-navigation"', false)
        ->assertSee(':aria-expanded="mobileMenu.toString()"', false)
        ->assertSee('@keydown.escape.window', false)
        ->assertSee('focus-visible:outline', false)
        ->assertSee('fetchpriority="high"', false)
        ->assertSee('decoding="async"', false);

    expect(substr_count($html, '<h1'))->toBe(1)
        ->and(substr_count($html, '<main'))->toBe(1)
        ->and(substr_count($html, '<header'))->toBe(1)
        ->and(substr_count($html, '<footer'))->toBe(1)
        ->and($xpath->query('//img[not(@alt) or normalize-space(@alt) = ""]')->length)->toBe(0);
});
