<?php

use App\Models\Insight;
use App\Models\InsightCategory;

dataset('permanent insight categories', [
    'law and governance' => ['Law & Governance', 'law-governance'],
    'legal 101' => ['Legal 101', 'legal-101'],
    'regulatory update' => ['Regulatory Update', 'regulatory-update'],
    'edulaw insight' => ['Edulaw Insight', 'edulaw-insight'],
]);

test('core editorial categories have permanent indexable landing pages', function (string $name, string $slug) {
    $category = InsightCategory::query()->create([
        'name' => $name,
        'slug' => $slug,
        'is_active' => true,
    ]);
    $insight = Insight::query()->create([
        'insight_category_id' => $category->id,
        'title' => "Artikel Utama {$name}",
        'slug' => "artikel-utama-{$slug}",
        'excerpt' => "Ringkasan khusus untuk kanal {$name}.",
        'content' => '<p>Konten editorial kategori.</p>',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $url = route('insights.categories.show', $slug);
    $response = $this->get($url)
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.$url.'">', false)
        ->assertSee('<meta name="robots" content="index,follow">', false)
        ->assertSee($insight->title)
        ->assertSee('Kanal Terkait')
        ->assertDontSee('Tentang '.$name)
        ->assertDontSee('Jelajahi Insight')
        ->assertSee(asset('images/hero/insight-category-pattern.webp'), false)
        ->assertSee('application/ld+json', false)
        ->assertViewHas('definition', function (array $definition) use ($name): bool {
            $wordCount = str_word_count($definition['introduction']);

            return $definition['name'] === $name
                && $wordCount >= 100
                && $wordCount <= 250;
        });

    $html = $response->getContent();

    expect(substr_count($html, '<h1'))->toBe(1)
        ->and($html)->toContain(' | Edulaw Project</title>')
        ->and(substr_count($html, 'href="'.route('insights.categories.show', 'law-governance').'"')
            + substr_count($html, 'href="'.route('insights.categories.show', 'legal-101').'"')
            + substr_count($html, 'href="'.route('insights.categories.show', 'regulatory-update').'"')
            + substr_count($html, 'href="'.route('insights.categories.show', 'edulaw-insight').'"'))
        ->toBeGreaterThanOrEqual(3);
})->with('permanent insight categories');

test('legacy category query redirects permanently to its clean category URL', function () {
    $this->get(route('insights.index', [
        'category' => 'regulatory-update',
        'archive' => 'latest',
    ]))
        ->assertMovedPermanently()
        ->assertRedirect(route('insights.categories.show', 'regulatory-update'));

    $this->get(route('insights.index', [
        'category' => 'legal-101',
        'page' => 2,
    ]))
        ->assertMovedPermanently()
        ->assertRedirect(route('insights.categories.show', [
            'categorySlug' => 'legal-101',
            'page' => 2,
        ]));
});

test('editorial index links directly to all permanent core category URLs', function () {
    foreach ([
        'Law & Governance' => 'law-governance',
        'Legal 101' => 'legal-101',
        'Regulatory Update' => 'regulatory-update',
        'Edulaw Insight' => 'edulaw-insight',
    ] as $name => $slug) {
        InsightCategory::query()->create([
            'name' => $name,
            'slug' => $slug,
            'is_active' => true,
        ]);
    }

    $response = $this->get(route('insights.index'))->assertOk();

    foreach (['law-governance', 'legal-101', 'regulatory-update', 'edulaw-insight'] as $slug) {
        $response->assertSee(route('insights.categories.show', $slug), false);
    }
});

test('editorial index only exposes curated active categories in configured order', function () {
    foreach ([
        ['name' => 'Hukum Keluarga', 'slug' => 'hukum-keluarga', 'sort_order' => 20, 'is_active' => true, 'show' => true],
        ['name' => 'Hak Digital', 'slug' => 'hak-digital', 'sort_order' => 10, 'is_active' => true, 'show' => true],
        ['name' => 'Kategori Tersembunyi', 'slug' => 'kategori-tersembunyi', 'sort_order' => 1, 'is_active' => true, 'show' => false],
        ['name' => 'Kategori Nonaktif', 'slug' => 'kategori-nonaktif', 'sort_order' => 0, 'is_active' => false, 'show' => true],
    ] as $data) {
        InsightCategory::query()->create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => "Deskripsi {$data['name']}.",
            'sort_order' => $data['sort_order'],
            'is_active' => $data['is_active'],
            'show_on_editorial_index' => $data['show'],
        ]);
    }

    $this->get(route('insights.index'))
        ->assertOk()
        ->assertViewHas('insightChannels', function ($channels): bool {
            return $channels->pluck('label')->all() === ['Hak Digital', 'Hukum Keluarga']
                && $channels->pluck('description')->all() === [
                    'Deskripsi Hak Digital.',
                    'Deskripsi Hukum Keluarga.',
                ];
        })
        ->assertDontSee('Kategori Tersembunyi')
        ->assertDontSee('Kategori Nonaktif');
});

test('category pagination is indexable with a self canonical and navigable previous and next links', function () {
    $category = InsightCategory::query()->create([
        'name' => 'Legal 101',
        'slug' => 'legal-101',
        'is_active' => true,
    ]);

    foreach (range(1, 25) as $position) {
        Insight::query()->create([
            'insight_category_id' => $category->id,
            'title' => "Panduan Hukum {$position}",
            'slug' => "panduan-hukum-{$position}",
            'content' => '<p>Konten panduan hukum.</p>',
            'status' => 'published',
            'published_at' => now()->subMinutes($position),
        ]);
    }

    $baseUrl = route('insights.categories.show', 'legal-101');
    $pageTwoUrl = $baseUrl.'?page=2';
    $html = $this->get($pageTwoUrl)
        ->assertOk()
        ->assertSee('<meta name="robots" content="index,follow">', false)
        ->assertSee('<link rel="canonical" href="'.$pageTwoUrl.'">', false)
        ->assertSee('<link rel="prev" href="'.$baseUrl.'">', false)
        ->assertSee('<link rel="next" href="'.$baseUrl.'?page=3">', false)
        ->assertSee('Halaman 2 dari 3')
        ->assertSee('aria-label="Navigasi halaman kategori editorial"', false)
        ->assertSee('class="mt-7 flex flex-wrap items-center justify-center gap-2"', false)
        ->assertViewHas('insights', fn ($insights): bool => $insights->perPage() === 12 && $insights->count() === 12)
        ->getContent();

    expect($html)
        ->toContain('<title>Legal 101: Panduan Dasar-Dasar Hukum - Halaman 2 | Edulaw Project</title>')
        ->toContain('Panduan Hukum 13')
        ->not->toContain('Panduan Hukum 1</h3>');

    $this->get($baseUrl.'?page=99')->assertNotFound();
});

test('arbitrary tag and category paths are not generated as thin landing pages', function () {
    $this->get('/insight/kategori/kategori-tipis')->assertNotFound();
    $this->get('/insight/tag/istilah-tunggal')->assertNotFound();
});
