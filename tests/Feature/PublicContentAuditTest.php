<?php

use App\Models\Insight;
use App\Models\Multimedia;
use Illuminate\Support\Facades\Artisan;

it('audits published records without changing content or including drafts and scheduled articles', function () {
    $thin = Insight::create([
        'title' => 'Artikel perlu tinjauan', 'slug' => 'artikel-perlu-tinjauan',
        'content' => 'Ringkasan singkat.', 'status' => 'published', 'published_at' => now()->subDay(),
    ]);
    Insight::create([
        'title' => 'Draft', 'slug' => 'draft-audit', 'content' => '', 'status' => 'draft',
    ]);
    Insight::create([
        'title' => 'Terjadwal', 'slug' => 'terjadwal-audit', 'content' => '',
        'status' => 'published', 'published_at' => now()->addDay(),
    ]);
    $before = $thin->fresh()->getAttributes();

    Artisan::call('edulaw:audit-public-content', ['--json' => true]);
    $report = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);

    expect($report['totals']['insight'])->toBe(1)
        ->and($report['review_count'])->toBe(1)
        ->and($report['findings'][0]['slug'])->toBe($thin->slug)
        ->and($report['findings'][0]['indexable'])->toBeFalse()
        ->and($report['findings'][0]['reasons'])->toContain('Belum memiliki penulis yang tercatat.')
        ->and($thin->fresh()->getAttributes())->toBe($before);
});

it('hides empty multimedia channels and their navigation but keeps populated channels', function () {
    $this->get(route('multimedia.index'))->assertOk()
        ->assertDontSee('id="album-foto"', false)
        ->assertDontSee('href="#album-foto"', false)
        ->assertDontSee('id="shorts-reels"', false)
        ->assertDontSee('href="#shorts-reels"', false);

    Multimedia::create([
        'title' => 'Dokumentasi aktual', 'type' => 'gallery', 'platform' => 'google_photos',
        'media_url' => 'https://photos.google.com/example', 'status' => 'published',
    ]);
    $this->get(route('multimedia.index'))->assertOk()
        ->assertSee('id="album-foto"', false)
        ->assertSee('href="#album-foto"', false)
        ->assertSee('Dokumentasi aktual');
});
