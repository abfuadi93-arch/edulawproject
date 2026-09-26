<?php

use App\Models\Multimedia;

it('renders saved editorial summaries on the six regular video cards', function () {
    Multimedia::create([
        'title' => 'DIKSI #14', 'type' => 'video', 'platform' => 'youtube',
        'media_url' => 'https://youtu.be/abc123XYZ_9', 'status' => 'published', 'featured' => true,
    ]);

    foreach (range(8, 13) as $number) {
        Multimedia::create([
            'title' => "DIKSI #{$number}", 'type' => 'video', 'platform' => 'youtube',
            'media_url' => 'https://youtu.be/abc123XYZ_9', 'status' => 'published',
            'description' => "<p>Ringkasan editorial seri {$number}.</p><p>Hukum &amp; masyarakat.</p>",
        ]);
    }

    $response = $this->get(route('multimedia.index'))->assertOk();
    expect(substr_count($response->getContent(), 'data-media-summary'))->toBe(6);
    foreach (range(8, 13) as $number) {
        $response->assertSee("Ringkasan editorial seri {$number}. Hukum & masyarakat.");
    }
    $response->assertDontSee('<p>Ringkasan editorial', false);
});

it('omits empty summaries without a placeholder and escapes summary markup', function () {
    foreach ([null, '', '<p>&nbsp;</p>', '&lt;img src=x onerror=alert(1)&gt;'] as $description) {
        $html = $this->blade('<x-multimedia.media-card :item="$item" show-summary />', [
            'item' => new Multimedia([
                'title' => 'Video', 'type' => 'video', 'platform' => 'youtube',
                'media_url' => 'https://youtu.be/abc123XYZ_9', 'description' => $description,
            ]),
        ]);
        $html->assertDontSee('<img src=x onerror=alert(1)>', false);
        if ($description !== '&lt;img src=x onerror=alert(1)&gt;') {
            $html->assertDontSee('data-media-summary', false);
        }
    }
});
