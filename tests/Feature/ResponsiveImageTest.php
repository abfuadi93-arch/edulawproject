<?php

use App\Support\ResponsiveImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('serves cached webp variants for local CMS images', function () {
    Storage::fake('public');
    UploadedFile::fake()
        ->image('cover.jpg', 1200, 800)
        ->storeAs('insights', 'responsive-cover.jpg', 'public');

    $source = Storage::disk('public')->url('insights/responsive-cover.jpg');
    $srcset = ResponsiveImage::srcset($source, [320, 640]);

    expect($srcset)
        ->not->toBeNull()
        ->toContain(' 320w', ' 640w', '/media/image/');

    preg_match('/([^,\s]+) 320w/', (string) $srcset, $matches);
    $response = $this->get($matches[1])->assertOk();
    $variantPath = $response->baseResponse->getFile()->getPathname();
    $dimensions = getimagesize($variantPath);

    expect($dimensions)
        ->not->toBeFalse()
        ->and($dimensions[0])->toBe(320)
        ->and($dimensions['mime'])->toBe('image/webp');

    $response
        ->assertHeader('Content-Type', 'image/webp')
        ->assertHeader('X-Content-Type-Options', 'nosniff');

    expect($response->headers->get('Cache-Control'))
        ->toContain('public', 'max-age=31536000', 'immutable');

    $etag = $response->headers->get('ETag');
    expect($etag)->not->toBeNull();

    $this->withHeader('If-None-Match', $etag)
        ->get($matches[1])
        ->assertNotModified();
});

it('does not proxy remote images or accept arbitrary variant requests', function () {
    expect(ResponsiveImage::srcset('https://i.ytimg.com/vi/example/maxresdefault.jpg', [320]))
        ->toBeNull();

    $this->get(route('media.variant', ['token' => 'invalid', 'width' => 320]))
        ->assertNotFound();

    $descriptor = base64_encode(json_encode([
        'scope' => 'public',
        'path' => '../.env',
        'version' => 1,
    ], JSON_THROW_ON_ERROR));
    $token = rtrim(strtr($descriptor, '+/', '-_'), '=');

    $this->get(route('media.variant', ['token' => $token, 'width' => 320]))
        ->assertNotFound();
});

it('adds responsive candidates to local homepage images', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('srcset="', false)
        ->assertSee('/media/image/', false)
        ->assertSee('fetchpriority="high"', false)
        ->assertSee('sizes="100vw"', false);
});
