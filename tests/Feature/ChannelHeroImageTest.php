<?php

it('serves a local responsive hero with a matching single preload', function (string $route, string $channel) {
    $html = $this->get(route($route))->assertOk()->getContent();
    $document = new DOMDocument;
    @$document->loadHTML($html);
    $xpath = new DOMXPath($document);
    $image = $xpath->query('//section[@data-uniform-channel-hero]/img')->item(0);
    $preloads = $xpath->query('//head/link[@rel="preload" and @as="image"]');

    expect($image)->toBeInstanceOf(DOMElement::class)
        ->and($preloads->length)->toBe(1)
        ->and($image->getAttribute('src'))->toBe(asset("images/hero/channels/{$channel}-1600.webp"))
        ->and($image->getAttribute('loading'))->toBe('eager')
        ->and($image->getAttribute('fetchpriority'))->toBe('high')
        ->and($preloads->item(0)->getAttribute('href'))->toBe($image->getAttribute('src'))
        ->and($preloads->item(0)->getAttribute('imagesrcset'))->toBe($image->getAttribute('srcset'))
        ->and($preloads->item(0)->getAttribute('imagesizes'))->toBe($image->getAttribute('sizes'))
        ->and($image->getAttribute('sizes'))->toBe('100vw')
        ->and($html)->not->toContain('images.unsplash.com');

    foreach ([640, 960, 1600] as $width) {
        $path = "images/hero/channels/{$channel}-{$width}.webp";
        $dimensions = getimagesize(public_path($path));
        expect($image->getAttribute('srcset'))->toContain(asset($path)." {$width}w")
            ->and($dimensions[0])->toBe($width)
            ->and($dimensions['mime'])->toBe('image/webp');
    }
})->with([
    ['programs.index', 'programs'],
    ['opportunities.index', 'opportunities'],
    ['multimedia.index', 'multimedia'],
    ['publications.index', 'publications'],
]);
