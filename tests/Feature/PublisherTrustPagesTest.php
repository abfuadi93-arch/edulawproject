<?php

use App\Models\Author;
use App\Models\Insight;
use App\Models\User;

test('editorial standards and correction policy are indexable canonical pages linked from the footer', function () {
    $this->get(route('editorial-standards'))
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.route('editorial-standards').'">', false)
        ->assertSee('Prinsip Editorial')
        ->assertSee('Standar Sumber')
        ->assertSee('Konflik Kepentingan')
        ->assertSee('Teknologi dan AI');

    $this->get(route('corrections-policy'))
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.route('corrections-policy').'">', false)
        ->assertSee('Kesalahan Faktual')
        ->assertSee(route('contact.index'), false);

    $this->get(route('home'))
        ->assertSee(route('about'), false)
        ->assertSee(route('editorial-standards'), false)
        ->assertSee(route('corrections-policy'), false)
        ->assertSee(route('privacy'), false)
        ->assertSee(route('terms'), false)
        ->assertSee(route('contact.index'), false);
});

test('insight editorial metadata uses existing records and never invents an editor', function () {
    $author = Author::query()->create([
        'name' => 'Penulis Terverifikasi',
        'slug' => 'penulis-terverifikasi',
        'is_active' => true,
    ]);
    $editor = User::query()->create([
        'name' => 'Editor Terverifikasi',
        'email' => 'editor-terverifikasi@example.test',
        'password' => 'secret-password',
        'is_active' => true,
    ]);
    $insight = Insight::query()->create([
        'title' => 'Analisis dengan Metadata Editorial',
        'slug' => 'analisis-dengan-metadata-editorial',
        'content' => '<p>Isi analisis hukum.</p>',
        'status' => 'published',
        'published_at' => now()->subDay(),
        'reviewed_by' => $editor->id,
    ]);
    $insight->authors()->attach($author, ['author_order' => 1, 'role' => 'Author']);
    $insight->forceFill(['updated_at' => now()])->saveQuietly();

    $this->get(route('insights.show', $insight->slug))
        ->assertOk()
        ->assertSee('Metadata Editorial')
        ->assertSee('Ditulis oleh')
        ->assertSee('Penulis Terverifikasi')
        ->assertSee('Disunting oleh')
        ->assertSee('Editor Terverifikasi')
        ->assertSee('Tanggal terbit')
        ->assertSee('Terakhir diperbarui');
});

test('author joined date is optional and never inferred from record creation', function () {
    $withoutDate = Author::query()->create([
        'name' => 'Kontributor Tanpa Tanggal',
        'slug' => 'kontributor-tanpa-tanggal',
        'bio' => 'Bio kontributor.',
        'is_active' => true,
    ]);

    $this->get(route('profiles.show', $withoutDate->slug))
        ->assertOk()
        ->assertDontSee('Bergabung sejak');

    $withDate = Author::query()->create([
        'name' => 'Kontributor Dengan Tanggal',
        'slug' => 'kontributor-dengan-tanggal',
        'bio' => 'Bio kontributor.',
        'joined_at' => '2026-07-01',
        'is_active' => true,
    ]);

    $this->get(route('profiles.show', $withDate->slug))
        ->assertOk()
        ->assertSee('Bergabung sejak')
        ->assertSee('Jul 2026');
});
