<?php

use App\Models\Multimedia;

test('reported videos have discoverable indexable watch pages', function (string $id) {
    $video = Multimedia::create([
        'title' => 'Diskusi Hukum '.$id, 'type' => 'video', 'platform' => 'youtube',
        'media_url' => 'https://www.youtube.com/watch?v='.$id,
        'description' => str_repeat('Video ini membahas aturan, konteks kebijakan, contoh penerapan, dan dampaknya bagi masyarakat. ', 15),
        'status' => 'published', 'published_at' => now()->subDay(),
    ]);
    $html = $this->get($video->watch_url)->assertOk()
        ->assertSee('src="https://www.youtube.com/embed/'.$id.'"', false)
        ->assertSee('loading="eager"', false)
        ->assertSee('<meta name="robots" content="index,follow">', false)
        ->assertSee('<link rel="canonical" href="'.$video->watch_url.'">', false)
        ->assertSee('VideoObject')->getContent();
    expect(substr_count($html, '<iframe'))->toBe(1);
    $this->get(route('multimedia.index'))->assertOk()->assertSee('href="'.$video->watch_url.'"', false);
    $this->get(route('sitemap'))->assertOk()->assertSee($video->watch_url, false);

    $video->update(['status' => 'draft']);
    $this->get($video->watch_url)->assertNotFound();
    $this->get(route('sitemap'))->assertDontSee($video->watch_url, false);
})->with(['oMjVH5Rbn5k', '2ATZEA_sqdQ']);

test('thin youtube entries link to the source and stay out of the sitemap', function () {
    $video = Multimedia::create([
        'title' => 'Video Tanpa Ringkasan Editorial',
        'type' => 'video',
        'platform' => 'youtube',
        'media_url' => 'https://www.youtube.com/watch?v=oMjVH5Rbn5k',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('multimedia.index'))
        ->assertOk()
        ->assertSee('href="'.$video->media_url.'"', false)
        ->assertSee('target="_blank" rel="noopener noreferrer"', false)
        ->assertDontSee('href="'.$video->watch_url.'"', false);

    $this->get($video->watch_url)
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex,follow">', false);

    $this->get(route('sitemap'))
        ->assertOk()
        ->assertDontSee($video->watch_url, false);
});

test('future and invalid videos do not have public watch pages', function () {
    foreach (['future', 'invalid'] as $kind) {
        $video = Multimedia::create([
            'title' => $kind, 'type' => 'video', 'platform' => 'youtube',
            'media_url' => $kind === 'invalid' ? 'https://example.com' : 'https://youtu.be/oMjVH5Rbn5k',
            'status' => 'published', 'published_at' => $kind === 'future' ? now()->addDay() : now(),
        ]);
        $this->get(route('multimedia.show', $video->slug))->assertNotFound();
    }
});
