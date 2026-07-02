<?php

use App\Models\Author;
use App\Models\Insight;
use App\Models\InsightCategory;
use App\Models\Publication;
use App\Models\PublicationType;

test('active public profile page shows biography writings and publications', function () {
    $author = Author::query()->create([
        'name' => 'Aulia Rahman',
        'slug' => 'aulia-rahman',
        'bio' => 'Penulis hukum tata negara dan kebijakan publik.',
        'institution' => 'Edulaw Project',
        'position' => 'Researcher',
        'profile_type' => 'internal_author',
        'is_active' => true,
    ]);

    $category = InsightCategory::query()->create([
        'name' => 'Konstitusi',
        'slug' => 'konstitusi',
        'is_active' => true,
    ]);

    $type = PublicationType::query()->create([
        'name' => 'Policy Brief',
        'slug' => 'policy-brief',
        'is_active' => true,
    ]);

    $publishedInsight = Insight::query()->create([
        'insight_category_id' => $category->id,
        'title' => 'Membaca Konstitusi Secara Publik',
        'slug' => 'membaca-konstitusi-secara-publik',
        'excerpt' => 'Tulisan tentang literasi konstitusi.',
        'status' => 'published',
        'published_at' => now(),
        'reading_time' => 5,
    ]);

    $draftInsight = Insight::query()->create([
        'insight_category_id' => $category->id,
        'title' => 'Catatan Draft Tidak Tampil',
        'slug' => 'catatan-draft-tidak-tampil',
        'status' => 'draft',
    ]);

    $publication = Publication::query()->create([
        'publication_type_id' => $type->id,
        'title' => 'Policy Brief Literasi Konstitusi',
        'slug' => 'policy-brief-literasi-konstitusi',
        'excerpt' => 'Publikasi tentang literasi konstitusi.',
        'status' => 'published',
        'published_at' => now()->toDateString(),
    ]);

    $author->insights()->attach($publishedInsight->id, ['author_order' => 1, 'role' => 'Author']);
    $author->insights()->attach($draftInsight->id, ['author_order' => 2, 'role' => 'Author']);
    $author->publications()->attach($publication->id, ['author_order' => 1, 'role' => 'Author']);

    $this->get(route('profiles.show', $author->slug))
        ->assertOk()
        ->assertSee('Aulia Rahman')
        ->assertSee('Penulis hukum tata negara dan kebijakan publik.')
        ->assertSee('Membaca Konstitusi Secara Publik')
        ->assertSee('Policy Brief Literasi Konstitusi')
        ->assertDontSee('Catatan Draft Tidak Tampil');
});

test('inactive public profile page is not visible', function () {
    $author = Author::query()->create([
        'name' => 'Profil Nonaktif',
        'slug' => 'profil-nonaktif',
        'is_active' => false,
    ]);

    $this->get(route('profiles.show', $author->slug))
        ->assertNotFound();
});
