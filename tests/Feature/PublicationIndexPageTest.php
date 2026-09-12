<?php

use App\Models\Publication;

test('featured publication uses a square cover and gives the content the wider column', function () {
    Publication::query()->create([
        'title' => 'Publikasi Utama A4',
        'slug' => 'publikasi-utama-a4',
        'status' => 'published',
        'featured' => true,
        'cover_image' => 'publications/covers/missing-cover.jpg',
        'published_at' => now(),
    ]);

    $this->get(route('publications.index'))
        ->assertOk()
        ->assertSee('lg:grid-cols-[365px_minmax(0,1fr)]', false)
        ->assertSee('aspect-square', false)
        ->assertSee('lg:size-[317px]', false)
        ->assertSee('onerror="this.remove()"', false);
});

test('publication catalog paginates grid and list views in complete sets of twelve', function (string $view) {
    foreach (range(1, 13) as $position) {
        Publication::query()->create([
            'title' => "Publikasi Katalog {$position}",
            'slug' => "publikasi-katalog-{$position}",
            'status' => 'published',
            'published_at' => now()->subMinutes($position),
        ]);
    }

    $this->get(route('publications.index', ['view' => $view]))
        ->assertOk()
        ->assertSee('aria-label="Navigasi halaman riset dan publikasi"', false)
        ->assertSee('class="mt-7 flex flex-wrap items-center justify-center gap-2"', false)
        ->assertSee('#publication-catalog', false)
        ->assertViewHas('publications', fn ($publications): bool => $publications->perPage() === 12
            && $publications->currentPage() === 1
            && $publications->count() === 12
            && $publications->total() === 13);

    $this->get(route('publications.index', ['view' => $view, 'page' => 2]))
        ->assertOk()
        ->assertViewHas('publications', fn ($publications): bool => $publications->perPage() === 12
            && $publications->currentPage() === 2
            && $publications->count() === 1);
})->with(['grid', 'list']);
