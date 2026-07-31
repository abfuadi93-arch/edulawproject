<?php

use App\Filament\Resources\Insights\InsightResource;
use App\Models\Author;
use App\Models\Insight;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

test('insight admin resource derives excerpt and seo fallback from content', function () {
    $content = '<p>Paragraf awal artikel berisi uraian panjang tentang hukum publik, demokrasi, dan kebijakan negara yang ditulis untuk pembaca umum dengan bahasa yang mudah dipahami.</p>';

    $data = InsightResource::prepareFormDataForPersistence([
        'title' => 'Membaca Hukum Publik',
        'slug' => '',
        'content' => $content,
        'cover_image' => 'insights/hukum-publik.jpg',
        'seo_title' => null,
        'seo_description' => null,
        'og_image' => null,
    ]);

    expect($data['slug'])->toBe('membaca-hukum-publik')
        ->and($data['excerpt'])->toStartWith('Paragraf awal artikel')
        ->and(mb_strlen($data['excerpt']))->toBeLessThanOrEqual(220)
        ->and($data['seo_title'])->toBe('Membaca Hukum Publik')
        ->and($data['seo_description'])->toStartWith('Paragraf awal artikel')
        ->and(mb_strlen($data['seo_description']))->toBeLessThanOrEqual(180)
        ->and($data['og_image'])->toBe('insights/hukum-publik.jpg')
        ->and($data['reading_time'])->toBe(1);
});

test('insight admin resource exposes simplified statuses and maps legacy values', function () {
    expect(InsightResource::statusOptions())->toBe([
        'draft' => 'Draft',
        'reviewed' => 'Reviewed',
        'published' => 'Published',
    ])
        ->and(InsightResource::statusLabel('submitted'))->toBe('Reviewed')
        ->and(InsightResource::statusLabel('archived'))->toBe('Draft')
        ->and(InsightResource::statusLabel('published'))->toBe('Published');
});

test('insight admin resource preserves a manually curated excerpt', function () {
    $data = InsightResource::prepareFormDataForPersistence([
        'title' => 'Editorial dengan Ringkasan Manual',
        'content' => '<p>Isi artikel yang lebih panjang daripada ringkasannya.</p>',
        'excerpt' => 'Ringkasan editorial yang ditulis khusus oleh redaksi.',
    ]);

    expect($data['excerpt'])->toBe('Ringkasan editorial yang ditulis khusus oleh redaksi.');
});

test('insight admin resource rejects h1 inside article content', function () {
    expect(InsightResource::contentContainsH1('<H1 class="title">Bagian Artikel</H1>'))->toBeTrue()
        ->and(InsightResource::contentContainsH1('<h2>Bagian Artikel</h2>'))->toBeFalse();

    expect(fn () => InsightResource::prepareFormDataForPersistence([
        'title' => 'Artikel Tidak Valid',
        'content' => '<h1>Judul di Body</h1><p>Isi.</p>',
    ]))->toThrow(ValidationException::class, 'Isi artikel tidak boleh menggunakan H1');
});

test('published insight requires cover and excerpt during persistence', function () {
    try {
        InsightResource::prepareFormDataForPersistence([
            'title' => 'Artikel Siap Terbit',
            'status' => 'published',
            'content' => '<h2>Bagian Utama</h2><p>Isi artikel.</p>',
            'cover_image' => null,
            'excerpt' => null,
        ]);

        $this->fail('Published insight tanpa cover dan excerpt seharusnya gagal.');
    } catch (ValidationException $exception) {
        expect($exception->errors())
            ->toHaveKeys(['cover_image', 'excerpt']);
    }
});

test('insights schema and model expose backward compatible editorial placement fields', function () {
    expect(Schema::hasColumns('insights', ['featured', 'editor_pick', 'sort_order']))->toBeTrue();

    $insight = Insight::query()->create([
        'title' => 'Editorial Tanpa Pengaturan Kurasi',
        'slug' => 'editorial-tanpa-pengaturan-kurasi',
    ]);

    $insight = $insight->fresh();

    expect($insight->editor_pick)->toBeFalse()
        ->and($insight->featured)->toBeFalse()
        ->and($insight->sort_order)->toBe(0);
});

test('insight admin resource reports publication readiness issues', function () {
    $insight = Insight::query()->create([
        'title' => 'Editorial dalam Pemeriksaan',
        'slug' => 'editorial-dalam-pemeriksaan',
        'content' => '<h1>Heading Tidak Valid</h1><p>Isi.</p>',
    ]);

    expect(InsightResource::publishReadinessIssues($insight))
        ->toBe(['gambar utama', 'ringkasan', 'penulis', 'hapus H1 dari isi artikel'])
        ->and(InsightResource::isPublishReady($insight))->toBeFalse();

    $author = Author::query()->create([
        'name' => 'Redaksi Pemeriksa',
        'slug' => 'redaksi-pemeriksa',
        'is_active' => true,
    ]);

    $insight->update([
        'cover_image' => 'insights/editorial.webp',
        'excerpt' => 'Ringkasan editorial yang telah diperiksa.',
        'content' => '<h2>Heading Valid</h2><p>Isi.</p>',
    ]);
    $insight->authors()->attach($author, ['author_order' => 1, 'role' => 'Penulis']);

    expect(InsightResource::isPublishReady($insight->fresh()))->toBeTrue();
});
