<?php

use App\Models\Author;
use App\Models\Insight;
use App\Models\InsightCategory;
use App\Models\PageVisit;

test('published insight index and detail pages render', function () {
    $category = InsightCategory::query()->create([
        'name' => 'Edulaw Insight',
        'slug' => 'edulaw-insight',
        'is_active' => true,
    ]);

    $insight = Insight::query()->create([
        'insight_category_id' => $category->id,
        'title' => 'Membaca Hukum Secara Publik',
        'slug' => 'membaca-hukum-secara-publik',
        'excerpt' => 'Tulisan editorial tentang hukum publik.',
        'content' => '<p>Konten editorial yang bisa dibuka oleh pembaca.</p>',
        'status' => 'published',
        'published_at' => now(),
        'reading_time' => 4,
    ]);

    $this->get(route('insights.index'))
        ->assertOk()
        ->assertSee('Membaca Hukum Secara Publik')
        ->assertSee('Edulaw Insight')
        ->assertSee('Jelajahi Artikel')
        ->assertSee('href="#editorial-terbaru"', false)
        ->assertSee('Ajukan Kolaborasi')
        ->assertSee(route('collaboration.index'), false)
        ->assertDontSee('Jelajahi Arsip')
        ->assertDontSee('Baca Editorial Terbaru')
        ->assertSee('Editorial Edulaw')
        ->assertSee('Baca Selengkapnya');

    $html = $this->get(route('insights.show', $insight->slug))
        ->assertOk()
        ->assertSee('Membaca Hukum Secara Publik')
        ->assertSee('Artikel Editorial')
        ->assertSee('Bagikan Insight')
        ->assertSee('WhatsApp')
        ->assertSee('Telegram')
        ->assertSee('X/Twitter')
        ->assertSee('Facebook')
        ->assertSee('LinkedIn')
        ->assertSee('Email')
        ->assertSee('Instagram')
        ->assertSee('Salin Link')
        ->assertSee(asset('images/hero/hero-edulaw.jpg'), false)
        ->assertSee('edulaw-readable insight-article-body', false)
        ->getContent();

    expect($html)
        ->toContain('property="og:title" content="Membaca Hukum Secara Publik - Edulaw Project"')
        ->toContain('property="og:type" content="article"')
        ->toContain('property="og:url" content="'.route('insights.show', $insight->slug).'"')
        ->toContain('name="twitter:card" content="summary_large_image"');
});

test('legacy insight slug redirects permanently to canonical slug', function () {
    $category = InsightCategory::query()->create([
        'name' => 'Edulaw Insight',
        'slug' => 'edulaw-insight',
        'is_active' => true,
    ]);

    $insight = Insight::query()->create([
        'insight_category_id' => $category->id,
        'title' => 'Work-Life Balance di Era Hustle Culture',
        'slug' => 'work-life-balance-di-era-hustle-culture-menakar-perlindungan-hukum-terhadap-hak-atas-kesehatan-mental',
        'excerpt' => 'Tulisan tentang perlindungan kesehatan mental.',
        'content' => '<p>Konten editorial tentang work-life balance.</p>',
        'status' => 'published',
        'published_at' => now(),
        'reading_time' => 5,
    ]);

    $this->get(route('insights.show', 'worklife-balance-di-era-hustle-culture-menakar-perlindungan-hukum-terhadap-hak-atas-kesehatan-mental'))
        ->assertStatus(301)
        ->assertRedirect(route('insights.show', $insight->slug));
});

test('latest featured insight is excluded from editorial picks once it is already used', function () {
    $category = InsightCategory::query()->create([
        'name' => 'Edulaw Insight',
        'slug' => 'edulaw-insight',
        'is_active' => true,
    ]);

    $featuredInsight = Insight::query()->create([
        'insight_category_id' => $category->id,
        'title' => 'Editorial Pilihan Redaksi',
        'slug' => 'editorial-pilihan-redaksi',
        'content' => '<p>Editorial unggulan.</p>',
        'status' => 'published',
        'published_at' => now(),
        'featured' => true,
    ]);

    foreach (range(1, 5) as $position) {
        Insight::query()->create([
            'insight_category_id' => $category->id,
            'title' => "Editorial Terbaru {$position}",
            'slug' => "editorial-terbaru-{$position}",
            'content' => '<p>Editorial terbaru.</p>',
            'status' => 'published',
            'published_at' => now()->subMinutes($position),
        ]);
    }

    $olderFeaturedInsight = Insight::query()->create([
        'insight_category_id' => $category->id,
        'title' => 'Editorial Pilihan Berikutnya',
        'slug' => 'editorial-pilihan-berikutnya',
        'content' => '<p>Editorial pilihan berikutnya.</p>',
        'status' => 'published',
        'published_at' => now()->subDays(10),
        'featured' => true,
    ]);

    $this->get(route('insights.index'))
        ->assertOk()
        ->assertViewHas('editorialPicks', function ($picks) use ($featuredInsight, $olderFeaturedInsight): bool {
            return ! $picks->contains('id', $featuredInsight->id)
                && $picks->contains('id', $olderFeaturedInsight->id);
        });
});

test('editorial picks do not fallback to ordinary latest articles', function () {
    $category = InsightCategory::query()->create([
        'name' => 'Edulaw Insight',
        'slug' => 'edulaw-insight',
        'is_active' => true,
    ]);

    Insight::query()->create([
        'insight_category_id' => $category->id,
        'title' => 'Editorial Featured Sudah Dipakai',
        'slug' => 'editorial-featured-sudah-dipakai',
        'content' => '<p>Editorial featured yang tampil sebagai pembuka.</p>',
        'status' => 'published',
        'published_at' => now(),
        'featured' => true,
    ]);

    foreach (range(1, 6) as $position) {
        Insight::query()->create([
            'insight_category_id' => $category->id,
            'title' => "Editorial Biasa {$position}",
            'slug' => "editorial-biasa-{$position}",
            'content' => '<p>Editorial biasa tanpa kurasi redaksi.</p>',
            'status' => 'published',
            'published_at' => now()->subMinutes($position),
            'featured' => false,
        ]);
    }

    $this->get(route('insights.index'))
        ->assertOk()
        ->assertDontSee('Pilihan Editor')
        ->assertViewHas('editorialPicks', fn ($picks): bool => $picks->isEmpty());
});

test('editorial category section is capped to four compact blocks', function () {
    $categories = collect([
        ['name' => 'Regulatory Update', 'slug' => 'regulatory-update'],
        ['name' => 'Edulaw Insight', 'slug' => 'edulaw-insight'],
        ['name' => 'Legal 101', 'slug' => 'legal-101'],
        ['name' => 'Law & Governance', 'slug' => 'law-governance'],
        ['name' => 'Teknologi Hukum', 'slug' => 'teknologi-hukum'],
    ])->map(fn (array $category, int $index): InsightCategory => InsightCategory::query()->create([
        'name' => $category['name'],
        'slug' => $category['slug'],
        'description' => "Deskripsi {$category['name']}.",
        'is_active' => true,
        'sort_order' => $index + 1,
    ]));

    $offset = 0;
    foreach (range(1, 5) as $round) {
        foreach ($categories as $category) {
            Insight::query()->create([
                'insight_category_id' => $category->id,
                'title' => "{$category->name} Artikel {$round}",
                'slug' => "{$category->slug}-artikel-{$round}",
                'content' => '<p>Konten editorial kategori.</p>',
                'status' => 'published',
                'published_at' => now()->subHours($offset++),
            ]);
        }
    }

    $html = $this->get(route('insights.index'))
        ->assertOk()
        ->assertSee('Jelajahi Berdasarkan Kategori')
        ->assertSee('Lihat Semua Kategori')
        ->getContent();

    expect(substr_count($html, 'data-editorial-category-block='))
        ->toBe(4);

    expect(substr_count($html, 'data-category-featured='))
        ->toBe(4);

    expect(substr_count($html, 'data-category-list-item='))
        ->toBeLessThanOrEqual(8);
});

test('most read insights are ordered by page visits from the last 30 days', function () {
    $category = InsightCategory::query()->create([
        'name' => 'Opini Publik',
        'slug' => 'opini-publik',
        'is_active' => true,
    ]);

    foreach (range(1, 6) as $position) {
        Insight::query()->create([
            'insight_category_id' => $category->id,
            'title' => "Editorial Terbaru Tanpa Kunjungan {$position}",
            'slug' => "editorial-terbaru-tanpa-kunjungan-{$position}",
            'content' => '<p>Editorial terbaru tanpa kunjungan.</p>',
            'status' => 'published',
            'published_at' => now()->subMinutes($position),
        ]);
    }

    foreach (range(1, 5) as $position) {
        Insight::query()->create([
            'insight_category_id' => $category->id,
            'title' => "Editorial Cadangan Pilihan {$position}",
            'slug' => "editorial-cadangan-pilihan-{$position}",
            'content' => '<p>Editorial cadangan pilihan editor.</p>',
            'status' => 'published',
            'published_at' => now()->subDays($position),
        ]);
    }

    $lessRead = Insight::query()->create([
        'insight_category_id' => $category->id,
        'title' => 'Editorial Lebih Sedikit Dibaca',
        'slug' => 'editorial-lebih-sedikit-dibaca',
        'content' => '<p>Editorial lebih sedikit dibaca.</p>',
        'status' => 'published',
        'published_at' => now()->subDays(7),
    ]);

    $mostRead = Insight::query()->create([
        'insight_category_id' => $category->id,
        'title' => 'Editorial Paling Banyak Dibaca',
        'slug' => 'editorial-paling-banyak-dibaca',
        'content' => '<p>Editorial paling banyak dibaca.</p>',
        'status' => 'published',
        'published_at' => now()->subDays(8),
    ]);

    foreach ([[$lessRead, 2], [$mostRead, 4]] as [$insight, $visits]) {
        foreach (range(1, $visits) as $index) {
            PageVisit::query()->create([
                'visitor_id' => "reader-{$insight->id}-{$index}",
                'method' => 'GET',
                'path' => "insight/{$insight->slug}",
                'full_url' => route('insights.show', $insight->slug),
                'route_name' => 'insights.show',
                'status_code' => 200,
                'visited_at' => now(),
            ]);
        }
    }

    $response = $this->get(route('insights.index'))
        ->assertOk()
        ->assertSee('Paling Banyak Dibaca')
        ->assertSee('4 kali dibaca');

    $html = $response->getContent();

    expect($html)
        ->toContain('data-most-read-item')
        ->toContain('Editorial Paling Banyak Dibaca')
        ->toContain('Editorial Lebih Sedikit Dibaca');

    expect(strpos($html, 'Editorial Paling Banyak Dibaca'))
        ->toBeLessThan(strpos($html, 'Editorial Lebih Sedikit Dibaca'));

    $response
        ->assertViewHas('popularInsights', function ($insights) use ($mostRead, $lessRead): bool {
            return $insights->pluck('id')->all() === [$mostRead->id, $lessRead->id];
        });
});

test('most read section renders five visited insights', function () {
    $category = InsightCategory::query()->create([
        'name' => 'Opini Publik',
        'slug' => 'opini-publik',
        'is_active' => true,
    ]);

    foreach (range(1, 6) as $position) {
        Insight::query()->create([
            'insight_category_id' => $category->id,
            'title' => "Editorial Terbaru Non-Ranking {$position}",
            'slug' => "editorial-terbaru-non-ranking-{$position}",
            'content' => '<p>Editorial terbaru non-ranking.</p>',
            'status' => 'published',
            'published_at' => now()->subMinutes($position),
        ]);
    }

    foreach (range(1, 5) as $position) {
        Insight::query()->create([
            'insight_category_id' => $category->id,
            'title' => "Editorial Cadangan Non-Ranking {$position}",
            'slug' => "editorial-cadangan-non-ranking-{$position}",
            'content' => '<p>Editorial cadangan non-ranking.</p>',
            'status' => 'published',
            'published_at' => now()->subDays($position),
        ]);
    }

    $insights = collect(range(1, 5))->map(function (int $position) use ($category): Insight {
        $insight = Insight::query()->create([
            'insight_category_id' => $category->id,
            'title' => "Most Read Editorial {$position}",
            'slug' => "most-read-editorial-{$position}",
            'content' => "<p>Most read editorial {$position}.</p>",
            'status' => 'published',
            'published_at' => now()->subDays($position + 6),
        ]);

        PageVisit::query()->create([
            'visitor_id' => "most-read-reader-{$position}",
            'method' => 'GET',
            'path' => "insight/{$insight->slug}",
            'full_url' => route('insights.show', $insight->slug),
            'route_name' => 'insights.show',
            'status_code' => 200,
            'visited_at' => now(),
        ]);

        return $insight;
    });

    $response = $this->get(route('insights.index'))->assertOk();

    $insights->each(fn (Insight $insight) => $response->assertSee($insight->title));

    expect(substr_count($response->getContent(), 'data-most-read-item'))->toBe(5);
});

test('most read section stays hidden when no visit data exists', function () {
    $category = InsightCategory::query()->create([
        'name' => 'Opini Publik',
        'slug' => 'opini-publik',
        'is_active' => true,
    ]);

    foreach (range(1, 8) as $position) {
        Insight::query()->create([
            'insight_category_id' => $category->id,
            'title' => "Editorial Tanpa Data Kunjungan {$position}",
            'slug' => "editorial-tanpa-data-kunjungan-{$position}",
            'content' => '<p>Editorial ini belum memiliki data kunjungan.</p>',
            'status' => 'published',
            'published_at' => now()->subMinutes($position),
        ]);
    }

    $this->get(route('insights.index'))
        ->assertOk()
        ->assertDontSee('Paling Banyak Dibaca')
        ->assertViewHas('popularInsights', fn ($insights): bool => $insights->isEmpty());
});

test('published insight without publish date remains hidden', function () {
    $category = InsightCategory::query()->create([
        'name' => 'Edulaw Insight',
        'slug' => 'edulaw-insight',
        'is_active' => true,
    ]);

    $insight = Insight::query()->create([
        'insight_category_id' => $category->id,
        'title' => 'Tulisan Belum Bertanggal',
        'slug' => 'tulisan-belum-bertanggal',
        'content' => '<p>Belum memiliki tanggal publikasi.</p>',
        'status' => 'published',
        'published_at' => null,
    ]);

    $this->get(route('insights.show', $insight->slug))
        ->assertNotFound();
});

test('editorial index excludes drafts and keeps category and search filters', function () {
    $firstCategory = InsightCategory::query()->create([
        'name' => 'Legal 101',
        'slug' => 'legal-101',
        'is_active' => true,
    ]);
    $secondCategory = InsightCategory::query()->create([
        'name' => 'Law & Governance',
        'slug' => 'law-governance',
        'is_active' => true,
    ]);

    $matching = Insight::query()->create([
        'insight_category_id' => $firstCategory->id,
        'title' => 'Panduan Hak Warga Negara',
        'slug' => 'panduan-hak-warga-negara',
        'content' => '<p>Penjelasan hak warga negara.</p>',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $other = Insight::query()->create([
        'insight_category_id' => $secondCategory->id,
        'title' => 'Tata Kelola Pemerintahan',
        'slug' => 'tata-kelola-pemerintahan',
        'content' => '<p>Analisis tata kelola.</p>',
        'status' => 'published',
        'published_at' => now()->subMinute(),
    ]);
    Insight::query()->create([
        'insight_category_id' => $firstCategory->id,
        'title' => 'Editorial Masih Draft',
        'slug' => 'editorial-masih-draft',
        'content' => '<p>Belum terbit.</p>',
        'status' => 'draft',
    ]);

    $this->get(route('insights.index', ['category' => $firstCategory->slug]))
        ->assertOk()
        ->assertViewHas('insights', fn ($insights) => $insights->pluck('id')->all() === [$matching->id]);

    $this->get(route('insights.index', ['q' => 'Tata Kelola']))
        ->assertOk()
        ->assertViewHas('insights', fn ($insights) => $insights->pluck('id')->all() === [$other->id]);
});

test('editorial archive pagination remains available', function () {
    $category = InsightCategory::query()->create([
        'name' => 'Edulaw Insight',
        'slug' => 'edulaw-insight',
        'is_active' => true,
    ]);

    foreach (range(1, 10) as $position) {
        Insight::query()->create([
            'insight_category_id' => $category->id,
            'title' => "Editorial Arsip {$position}",
            'slug' => "editorial-arsip-{$position}",
            'content' => '<p>Konten arsip.</p>',
            'status' => 'published',
            'published_at' => now()->subMinutes($position),
        ]);
    }

    $this->get(route('insights.index', ['archive' => 'latest', 'page' => 2]))
        ->assertOk()
        ->assertSee('Halaman 2 dari 2')
        ->assertViewHas('insights', fn ($insights) => $insights->currentPage() === 2 && $insights->count() === 1);
});

test('active editorial contributor links to public profile with published count', function () {
    $category = InsightCategory::query()->create([
        'name' => 'Edulaw Insight',
        'slug' => 'edulaw-insight',
        'is_active' => true,
    ]);
    $insight = Insight::query()->create([
        'insight_category_id' => $category->id,
        'title' => 'Tulisan Kontributor Aktif',
        'slug' => 'tulisan-kontributor-aktif',
        'content' => '<p>Konten kontributor.</p>',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $author = Author::query()->create([
        'name' => 'Nadia Peneliti',
        'slug' => 'nadia-peneliti',
        'position' => 'Peneliti Hukum',
        'photo' => 'authors/foto-yang-tidak-tersedia.jpg',
        'is_active' => true,
    ]);
    $author->insights()->attach($insight->id, ['author_order' => 1, 'role' => 'Penulis']);

    $this->get(route('insights.index'))
        ->assertOk()
        ->assertSee('Kontributor Editorial')
        ->assertSee('Peneliti Hukum')
        ->assertSee('1 tulisan terbit')
        ->assertSee('Lihat Semua Kontributor')
        ->assertSee('onerror="this.remove()"', false)
        ->assertSee('aria-hidden="true">N</span>', false)
        ->assertSee(route('profiles.show', $author->slug), false);
});

test('editorial contributor labels use public author position instead of auth roles', function () {
    $category = InsightCategory::query()->create([
        'name' => 'Edulaw Insight',
        'slug' => 'edulaw-insight',
        'is_active' => true,
    ]);

    $authors = collect([
        ['name' => 'Nora Publik', 'slug' => 'nora-publik', 'title' => 'admin', 'expected' => 'Kontributor Editorial'],
        ['name' => 'Redaksi Publik', 'slug' => 'redaksi-publik', 'position' => 'Redaksi Edulaw', 'expected' => 'Tim Editorial'],
        ['name' => 'Riset Publik', 'slug' => 'riset-publik', 'position' => 'Tim Riset', 'expected' => 'Tim Riset Edulaw'],
        ['name' => 'Kontributor Publik', 'slug' => 'kontributor-publik', 'position' => 'Kontributoe', 'expected' => 'Kontributor Editorial'],
    ])->map(function (array $authorData, int $index) use ($category): Author {
        $insight = Insight::query()->create([
            'insight_category_id' => $category->id,
            'title' => "Tulisan Kontributor Publik {$index}",
            'slug' => "tulisan-kontributor-publik-{$index}",
            'content' => '<p>Konten kontributor publik.</p>',
            'status' => 'published',
            'published_at' => now()->subMinutes($index),
        ]);

        $author = Author::query()->create([
            'name' => $authorData['name'],
            'slug' => $authorData['slug'],
            'title' => $authorData['title'] ?? null,
            'position' => $authorData['position'] ?? null,
            'is_active' => true,
        ]);

        $author->insights()->attach($insight->id, ['author_order' => 1, 'role' => 'admin']);

        return $author;
    });

    $response = $this->get(route('insights.index'))->assertOk();
    $html = $response->getContent();

    expect($html)
        ->toContain('Tim Editorial')
        ->toContain('Tim Riset Edulaw')
        ->not->toContain('>admin<')
        ->not->toContain('>Kontributoe<');

    $authors->each(fn (Author $author) => $response->assertSee(route('profiles.show', $author->slug), false));
});

test('editorial contributor grid is capped to eight profiles', function () {
    $category = InsightCategory::query()->create([
        'name' => 'Edulaw Insight',
        'slug' => 'edulaw-insight',
        'is_active' => true,
    ]);

    $insight = Insight::query()->create([
        'insight_category_id' => $category->id,
        'title' => 'Tulisan Bersama Kontributor',
        'slug' => 'tulisan-bersama-kontributor',
        'content' => '<p>Konten editorial bersama.</p>',
        'status' => 'published',
        'published_at' => now(),
    ]);

    foreach (range(1, 9) as $position) {
        $author = Author::query()->create([
            'name' => "Kontributor {$position}",
            'slug' => "kontributor-{$position}",
            'position' => 'Penulis Hukum',
            'sort_order' => $position,
            'is_active' => true,
        ]);

        $author->insights()->attach($insight->id, ['author_order' => $position, 'role' => 'Penulis']);
    }

    $html = $this->get(route('insights.index'))
        ->assertOk()
        ->getContent();

    expect(substr_count($html, 'data-editorial-contributor='))->toBe(8);
});

test('empty optional editorial sections stay hidden and featured article is not repeated in latest list', function () {
    $category = InsightCategory::query()->create([
        'name' => 'Edulaw Insight',
        'slug' => 'edulaw-insight',
        'is_active' => true,
    ]);
    $featured = Insight::query()->create([
        'insight_category_id' => $category->id,
        'title' => 'Editorial Utama Tunggal',
        'slug' => 'editorial-utama-tunggal',
        'content' => '<p>Konten editorial utama.</p>',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $response = $this->get(route('insights.index'))
        ->assertOk()
        ->assertDontSee('Paling Banyak Dibaca')
        ->assertDontSee('Kontributor Editorial');

    expect($response->getContent())
        ->toContain('data-featured-editorial="'.$featured->id.'"')
        ->not->toContain('data-latest-editorial="'.$featured->id.'"');
});
