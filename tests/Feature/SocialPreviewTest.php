<?php

use App\Models\Insight;
use App\Support\SocialPreviewImage;
use Illuminate\Support\Facades\Blade;

test('public page previews have complete Open Graph data and real fallback dimensions', function (string $routeName) {
    $html = $this->get(route($routeName))->assertOk()->getContent();
    foreach (['og:title', 'og:description', 'og:url', 'og:image', 'og:image:alt', 'og:image:width', 'og:image:height', 'og:image:type'] as $property) {
        expect(preg_match('/<meta property="'.preg_quote($property, '/').'" content="[^"]+">/', $html))->toBe(1);
        expect(substr_count($html, 'property="'.$property.'"'))->toBe(1);
    }
    $size = getimagesize(public_path('images/hero/hero-edulaw.jpg'));
    expect($html)->toContain('property="og:image:width" content="'.$size[0].'"')
        ->toContain('property="og:image:height" content="'.$size[1].'"');
})->with(['home', 'about', 'insights.index', 'publications.index', 'programs.index', 'programs.archive', 'opportunities.index', 'multimedia.index', 'contact.index', 'collaboration.index', 'privacy', 'terms', 'editorial-standards', 'corrections-policy', 'search.index']);

test('preview images on the canonical origin use https directly even with old stored http URLs', function () {
    config(['edulaw.site.url' => 'https://edulawproject.id']);
    $insight = Insight::query()->create([
        'title' => 'Artikel Preview', 'slug' => 'artikel-preview', 'status' => 'published',
        'published_at' => now()->subDay(), 'content' => 'Analisis kebijakan publik.',
        'og_image' => 'http://www.edulawproject.id/storage/seo/preview.jpg',
    ]);
    $this->get('https://edulawproject.id/insight/'.$insight->slug)->assertOk()
        ->assertSee('<meta property="og:image" content="https://edulawproject.id/storage/seo/preview.jpg">', false)
        ->assertSee('<meta property="og:image:secure_url" content="https://edulawproject.id/storage/seo/preview.jpg">', false)
        ->assertSee('<meta name="twitter:image" content="https://edulawproject.id/storage/seo/preview.jpg">', false);
});

test('external http images are not falsely advertised as secure and remote sizes are not invented', function () {
    $html = Blade::render('<x-seo title="Example" description="Description" image="http://external.example/image.jpg" />');
    expect($html)->toContain('property="og:image" content="http://external.example/image.jpg"')
        ->not->toContain('property="og:image:secure_url"')
        ->not->toContain('property="og:image:width"');
});

test('blank unsafe and protocol relative image values produce usable preview URLs', function () {
    foreach ([null, '', 'javascript:alert(1)', 'data:image/png;base64,AAAA'] as $value) {
        expect(SocialPreviewImage::metadata($value)['url'])->toBe(asset('images/hero/hero-edulaw.jpg'));
    }
    expect(SocialPreviewImage::metadata('//cdn.example.org/cover.jpg')['url'])->toBe('https://cdn.example.org/cover.jpg');
});
