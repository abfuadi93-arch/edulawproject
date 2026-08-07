<?php

use App\Filament\Resources\Insights\InsightResource;
use App\Models\Author;
use App\Models\Insight;
use Illuminate\Support\Facades\DB;
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

test('insight admin resource exposes editorial workflow statuses and maps legacy values', function () {
    expect(InsightResource::statusOptions())->toBe([
        'draft' => 'Draft',
        'review' => 'Sedang Direview',
        'published' => 'Terbit',
    ])
        ->and(InsightResource::statusLabel('submitted'))->toBe('Sedang Direview')
        ->and(InsightResource::statusLabel('revision_requested'))->toBe('Draft')
        ->and(InsightResource::statusLabel('archived'))->toBe('Diarsipkan')
        ->and(InsightResource::statusLabel('published'))->toBe('Terbit')
        ->and(InsightResource::statusLabel('reviewed'))->toBe('Sedang Direview');
});

test('simplification migration safely maps legacy insight statuses', function () {
    DB::table('insights')->insert([
        ['title' => 'Legacy Submitted', 'slug' => 'legacy-submitted', 'status' => 'submitted', 'created_at' => now(), 'updated_at' => now()],
        ['title' => 'Legacy Revision', 'slug' => 'legacy-revision', 'status' => 'revision_requested', 'created_at' => now(), 'updated_at' => now()],
        ['title' => 'Legacy Archived', 'slug' => 'legacy-archived', 'status' => 'archived', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $migration = require database_path('migrations/2026_08_07_000001_simplify_insight_editorial_workflow.php');
    $migration->up();

    expect(DB::table('insights')->where('slug', 'legacy-submitted')->value('status'))->toBe('review')
        ->and(DB::table('insights')->where('slug', 'legacy-revision')->value('status'))->toBe('draft')
        ->and(DB::table('insights')->where('slug', 'legacy-archived')->value('status'))->toBe('archived')
        ->and(DB::table('insights')->where('slug', 'legacy-archived')->value('archived_at'))->not->toBeNull();
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
            'content' => null,
            'cover_image' => null,
            'excerpt' => null,
        ]);

        $this->fail('Published insight tanpa cover dan excerpt seharusnya gagal.');
    } catch (ValidationException $exception) {
        expect($exception->errors())
            ->toHaveKeys(['cover_image', 'excerpt']);
    }
});

test('insights schema exposes simplified editorial fields and existing placement fields', function () {
    expect(Schema::hasColumns('insights', [
        'status',
        'assigned_editor_id',
        'assigned_at',
        'reviewed_by',
        'reviewed_at',
        'editor_notes',
        'published_at',
        'archived_at',
        'featured',
        'editor_pick',
        'sort_order',
    ]))->toBeTrue();

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
        ->toBe(['cover', 'excerpt', 'penulis', 'hapus H1 dari isi artikel'])
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

test('insight admin readiness filters match the placement badge rules', function () {
    $author = Author::query()->create([
        'name' => 'Penulis Filter',
        'slug' => 'penulis-filter',
        'is_active' => true,
    ]);

    $ready = Insight::query()->create([
        'title' => 'Artikel Siap Tayang',
        'slug' => 'artikel-siap-tayang',
        'cover_image' => 'insights/siap.webp',
        'excerpt' => 'Ringkasan artikel siap tayang.',
        'content' => '<h2>Bagian Utama</h2><p>Isi.</p>',
    ]);
    $ready->authors()->attach($author, ['author_order' => 1, 'role' => 'Penulis']);

    $withoutCover = Insight::query()->create([
        'title' => 'Artikel Tanpa Cover',
        'slug' => 'artikel-tanpa-cover',
        'excerpt' => 'Ringkasan tersedia.',
        'content' => '<h2>Bagian Utama</h2><p>Isi.</p>',
    ]);
    $withoutCover->authors()->attach($author, ['author_order' => 1, 'role' => 'Penulis']);

    $withInvalidHeading = Insight::query()->create([
        'title' => 'Artikel dengan H1',
        'slug' => 'artikel-dengan-h1',
        'cover_image' => 'insights/h1.webp',
        'excerpt' => 'Ringkasan tersedia.',
        'content' => '<h1>Heading Tidak Valid</h1><p>Isi.</p>',
    ]);
    $withInvalidHeading->authors()->attach($author, ['author_order' => 1, 'role' => 'Penulis']);

    expect(InsightResource::applyPublishReadyFilter(Insight::query())->pluck('id')->all())
        ->toBe([$ready->id])
        ->and(InsightResource::applyNotPublishReadyFilter(Insight::query())->pluck('id')->sort()->values()->all())
        ->toBe(collect([$withoutCover->id, $withInvalidHeading->id])->sort()->values()->all());
});

test('insight duplication generates a unique draft slug', function () {
    $insight = Insight::query()->create([
        'title' => 'Editorial untuk Duplikasi',
        'slug' => 'editorial-untuk-duplikasi',
    ]);

    expect(InsightResource::uniqueDuplicateSlug($insight))->toBe('editorial-untuk-duplikasi-salinan');

    Insight::query()->create([
        'title' => 'Editorial Salinan',
        'slug' => 'editorial-untuk-duplikasi-salinan',
    ]);

    expect(InsightResource::uniqueDuplicateSlug($insight))->toBe('editorial-untuk-duplikasi-salinan-2');
});
