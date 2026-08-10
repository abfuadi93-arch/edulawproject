<?php

use App\Filament\RichEditor\FootnoteRichContentPlugin;
use App\Models\Insight;
use App\Models\InsightFootnote;
use App\Services\InsightFootnoteService;
use Illuminate\Support\Str;

test('footnotes are synchronized from stable markers and numbered by appearance', function () {
    $firstUuid = (string) Str::uuid();
    $secondUuid = (string) Str::uuid();
    $orphanUuid = (string) Str::uuid();

    $insight = Insight::query()->create([
        'title' => 'Naskah dengan Catatan Kaki',
        'slug' => 'naskah-dengan-catatan-kaki',
        'content' => '<p>A<sup data-footnote-id="'.$secondUuid.'" data-footnote-number="8" data-footnote-content="Catatan kedua &amp; aman">8</sup> B<sup data-footnote-id="'.$firstUuid.'" data-footnote-number="3">3</sup></p>',
    ]);

    $insight->footnotes()->create([
        'uuid' => $firstUuid,
        'content' => 'Catatan pertama yang sudah tersimpan.',
        'sort_order' => 9,
    ]);
    $insight->footnotes()->create([
        'uuid' => $orphanUuid,
        'content' => 'Catatan tanpa penanda.',
        'sort_order' => 10,
    ]);

    app(InsightFootnoteService::class)->sync($insight);

    $footnotes = $insight->footnotes()->get();
    $content = $insight->fresh()->content;

    expect($footnotes)->toHaveCount(2)
        ->and($footnotes->pluck('uuid')->all())->toBe([$secondUuid, $firstUuid])
        ->and($footnotes->pluck('sort_order')->all())->toBe([1, 2])
        ->and($footnotes->first()->content)->toBe('Catatan kedua & aman')
        ->and(InsightFootnote::query()->where('uuid', $orphanUuid)->exists())->toBeFalse()
        ->and($content)->toContain('data-footnote-id="'.$secondUuid.'"')
        ->and($content)->toContain('data-footnote-number="1"')
        ->and($content)->toContain('data-footnote-number="2"')
        ->and($content)->not->toContain('data-footnote-content');
});

test('removing either side cleans up its orphan safely', function () {
    $uuid = (string) Str::uuid();
    $insight = Insight::query()->create([
        'title' => 'Sinkronisasi Dua Arah',
        'slug' => 'sinkronisasi-dua-arah',
        'content' => '<p>Teks<sup data-footnote-id="'.$uuid.'" data-footnote-number="1">1</sup></p>',
    ]);
    $footnote = $insight->footnotes()->create([
        'uuid' => $uuid,
        'content' => 'Isi catatan.',
        'sort_order' => 1,
    ]);

    $footnote->delete();
    app(InsightFootnoteService::class)->sync($insight);

    expect($insight->fresh()->content)->not->toContain('data-footnote-id')
        ->and($insight->footnotes()->exists())->toBeFalse();

    $newUuid = (string) Str::uuid();
    $insight->update(['content' => '<p>Teks tanpa penanda.</p>']);
    $insight->footnotes()->create([
        'uuid' => $newUuid,
        'content' => 'Catatan yang ditinggalkan.',
        'sort_order' => 1,
    ]);

    app(InsightFootnoteService::class)->sync($insight);

    expect($insight->footnotes()->exists())->toBeFalse();
});

test('public insight renders linked academic footnotes and escapes their plain text', function () {
    $uuid = (string) Str::uuid();
    $insight = Insight::query()->create([
        'title' => 'Artikel dengan Referensi',
        'slug' => 'artikel-dengan-referensi',
        'content' => '<p>Pernyataan hukum<sup data-footnote-id="'.$uuid.'" data-footnote-number="9">9</sup>.</p>',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $insight->footnotes()->create([
        'uuid' => $uuid,
        'content' => '<script>alert("xss")</script> Sumber hukum.',
        'sort_order' => 1,
    ]);

    $this->get(route('insights.show', $insight->slug))
        ->assertOk()
        ->assertSee('Catatan Kaki')
        ->assertSee('href="#fn-1"', false)
        ->assertSee('id="fn-1"', false)
        ->assertSee('href="#fnref-1"', false)
        ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt; Sumber hukum.', false)
        ->assertDontSee('<script>alert("xss")</script>', false);
});

test('public insight omits the footnote section when no valid markers exist', function () {
    $insight = Insight::query()->create([
        'title' => 'Artikel Tanpa Catatan',
        'slug' => 'artikel-tanpa-catatan',
        'content' => '<p>Isi artikel tanpa catatan kaki.</p>',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(route('insights.show', $insight->slug))
        ->assertOk()
        ->assertDontSee('id="insight-footnotes-heading"', false);
});

test('rich editor plugin exposes the footnote action tool and tiptap extension', function () {
    $plugin = new FootnoteRichContentPlugin;

    expect(collect($plugin->getEditorTools())->map->getName()->all())->toContain('footnote')
        ->and(collect($plugin->getEditorActions())->map->getName()->all())->toContain('footnote')
        ->and($plugin->getTipTapPhpExtensions())->toHaveCount(1);
});
