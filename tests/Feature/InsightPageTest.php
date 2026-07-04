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
        ->assertSee('Artikel Editorial');
});
