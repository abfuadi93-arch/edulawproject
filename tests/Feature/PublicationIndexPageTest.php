<?php

use App\Models\Publication;

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
