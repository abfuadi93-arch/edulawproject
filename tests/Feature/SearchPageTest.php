<?php

use App\Models\Author;
use App\Models\Insight;
use App\Models\Publication;

it('shows matching public content and excludes drafts and scheduled results', function () {
    $public = Insight::create([
        'title' => 'Konstitusi Analisis Publik',
        'slug' => 'konstitusi-analisis-publik',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    Insight::create([
        'title' => 'Konstitusi Naskah Draft',
        'slug' => 'konstitusi-naskah-draft',
        'status' => 'draft',
    ]);
    Publication::create([
        'title' => 'Konstitusi Publikasi Terjadwal',
        'slug' => 'konstitusi-publikasi-terjadwal',
        'status' => 'published',
        'published_at' => now()->addWeek(),
    ]);

    $this->get(route('search.index', ['q' => 'Konstitusi']))
        ->assertOk()
        ->assertSee('Konstitusi Analisis Publik')
        ->assertSee(route('insights.show', $public->slug))
        ->assertDontSee('Konstitusi Naskah Draft')
        ->assertDontSee('Konstitusi Publikasi Terjadwal')
        ->assertDontSee('pencarian statis')
        ->assertSee('noindex,follow')
        ->assertSee('<html lang="id">', false);

    $this->get(route('search.index', ['q' => ['Konstitusi']]))
        ->assertOk()
        ->assertSee('Masukkan kata kunci untuk mulai mencari.');
});

it('keeps an editorial byline but omits the URL of a nonpublic profile', function () {
    $author = Author::create(['name' => 'Redaksi Edulaw', 'slug' => 'redaksi-edulaw', 'is_active' => true]);
    $insight = Insight::create([
        'title' => 'Artikel Redaksi',
        'slug' => 'artikel-redaksi',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $insight->authors()->attach($author);

    $this->get(route('insights.show', $insight->slug))
        ->assertOk()
        ->assertSee('Redaksi Edulaw')
        ->assertDontSee(route('profiles.show', $author->slug), false);
});
