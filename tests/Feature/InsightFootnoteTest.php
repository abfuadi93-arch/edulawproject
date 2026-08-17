<?php

use App\Filament\Resources\Insights\InsightResource;
use App\Filament\Resources\Insights\InsightResource\Pages\EditInsight;
use App\Filament\RichEditor\FootnoteRichContentPlugin;
use App\Models\Author;
use App\Models\Insight;
use App\Models\InsightCategory;
use App\Models\InsightFootnote;
use App\Models\User;
use App\Services\InsightFootnoteService;
use Database\Seeders\RolePermissionSeeder;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Livewire\Livewire;

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

test('built rich editor footnote module keeps its default export', function () {
    $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true, flags: JSON_THROW_ON_ERROR);
    $entry = $manifest['resources/js/filament/rich-editor-footnote.js'] ?? null;

    expect($entry)->not->toBeNull();

    $asset = file_get_contents(public_path('build/'.$entry['file']));

    expect($asset)->toMatch('/export\s*(?:default|\{[^}]*\bas\s+default\b[^}]*\})/');
});

test('saved footnote immediately appears in the writer form', function () {
    $this->seed(RolePermissionSeeder::class);

    $writer = User::query()->create([
        'name' => 'Penulis Catatan Kaki',
        'email' => 'footnote-writer@example.test',
        'password' => 'password',
        'is_active' => true,
    ]);
    $writer->assignRole('writer');

    $category = InsightCategory::query()->create([
        'name' => 'Kategori Catatan Kaki',
        'slug' => 'kategori-catatan-kaki',
    ]);
    $author = Author::query()->create([
        'user_id' => $writer->id,
        'name' => $writer->name,
        'slug' => 'penulis-catatan-kaki',
        'is_active' => true,
    ]);
    $insight = Insight::query()->create([
        'created_by' => $writer->id,
        'updated_by' => $writer->id,
        'insight_category_id' => $category->id,
        'title' => 'Form Catatan Kaki',
        'slug' => 'form-catatan-kaki',
        'content' => '<p>Isi awal.</p>',
        'status' => 'draft',
    ]);
    $insight->authors()->attach($author, ['author_order' => 1, 'role' => 'Penulis']);

    $uuid = (string) Str::uuid();
    $content = '<p>Pernyataan hukum<sup class="edulaw-footnote-ref" data-footnote-id="'.$uuid.'" data-footnote-number="1" data-footnote-content="Sumber hukum tersimpan.">1</sup>.</p>';

    $this->actingAs($writer);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $component = Livewire::test(EditInsight::class, ['record' => $insight->getRouteKey()])
        ->set('data.content', $content)
        ->call('save')
        ->assertHasNoErrors();

    $footnoteState = collect($component->get('data.footnotes'));

    expect($insight->fresh()->content)->toContain('data-footnote-id="'.$uuid.'"')
        ->and($insight->footnotes()->where('uuid', $uuid)->value('content'))->toBe('Sumber hukum tersimpan.')
        ->and($footnoteState->pluck('content'))->toContain('Sumber hukum tersimpan.');

    $this->get(InsightResource::getUrl('edit', ['record' => $insight]))
        ->assertOk()
        ->assertSee('Sumber hukum tersimpan.');
});
