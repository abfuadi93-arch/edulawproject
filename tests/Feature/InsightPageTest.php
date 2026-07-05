<?php

use App\Models\Insight;
use App\Models\InsightCategory;

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
        ->assertSee('Edulaw Insight');

    $this->get(route('insights.show', $insight->slug))
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
        ->assertSee('edulaw-readable insight-article-body', false);
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
