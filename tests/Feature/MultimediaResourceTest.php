<?php

use App\Filament\Resources\Multimedia\MultimediaResource;
use App\Filament\Resources\Multimedia\Pages\CreateMultimedia;
use App\Filament\Resources\Multimedia\Pages\EditMultimedia;
use App\Models\Multimedia;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

function multimediaAdmin(): User
{
    $user = User::query()->create([
        'name' => 'Admin Multimedia',
        'email' => 'admin-multimedia-'.Str::random(8).'@example.test',
        'password' => 'secret-password',
        'is_active' => true,
    ]);
    $user->assignRole(Role::findOrCreate('super_admin'));

    return $user;
}

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('create and edit forms use the simplified shared multimedia schema', function () {
    $user = multimediaAdmin();
    $record = Multimedia::query()->create([
        'title' => 'Video untuk Diedit',
        'type' => 'video',
        'platform' => 'youtube',
        'media_url' => 'https://www.youtube.com/watch?v=abc123XYZ_9',
    ]);

    Livewire::actingAs($user)
        ->test(CreateMultimedia::class)
        ->assertFormFieldExists('title')
        ->assertFormFieldExists('description')
        ->assertFormFieldExists('type')
        ->assertFormFieldExists('platform')
        ->assertFormFieldExists('media_url')
        ->assertFormFieldDoesNotExist('slug')
        ->assertFormFieldDoesNotExist('embed_url')
        ->assertFormFieldDoesNotExist('duration')
        ->assertFormFieldDoesNotExist('serial')
        ->assertFormFieldDoesNotExist('topic')
        ->assertFormFieldDoesNotExist('display_section');

    Livewire::actingAs($user)
        ->test(EditMultimedia::class, ['record' => $record->getRouteKey()])
        ->assertOk()
        ->assertFormFieldDoesNotExist('slug')
        ->assertFormFieldDoesNotExist('embed_url');
});

test('youtube video can be saved without an exposed slug or thumbnail', function () {
    $user = multimediaAdmin();

    Livewire::actingAs($user)
        ->test(CreateMultimedia::class)
        ->fillForm([
            'title' => 'Video Konstitusi Terbaru',
            'description' => 'Ringkasan video.',
            'type' => 'video',
            'platform' => 'youtube',
            'media_url' => 'https://youtu.be/abc123XYZ_9',
            'status' => 'published',
            'featured' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $record = Multimedia::query()->where('title', 'Video Konstitusi Terbaru')->firstOrFail();

    expect($record->slug)->toBe('video-konstitusi-terbaru')
        ->and($record->published_at)->not->toBeNull()
        ->and($record->featured)->toBeTrue();
});

test('shorts and reels can be saved without a thumbnail', function () {
    Storage::fake('public');
    Http::fake([
        'https://www.instagram.com/reel/no-thumbnail/' => Http::response(
            '<html><head><meta property="og:image" content="https://scontent.cdninstagram.com/reel-cover.jpg"></head></html>'
        ),
        'https://scontent.cdninstagram.com/reel-cover.jpg' => Http::response(
            'fake-jpeg-content',
            200,
            ['Content-Type' => 'image/jpeg']
        ),
    ]);

    $user = multimediaAdmin();

    Livewire::actingAs($user)
        ->test(CreateMultimedia::class)
        ->fillForm([
            'title' => 'Reel Tanpa Thumbnail',
            'description' => 'Konten singkat dari Instagram.',
            'type' => 'reels',
            'platform' => 'instagram',
            'media_url' => 'https://www.instagram.com/reel/no-thumbnail/',
            'status' => 'draft',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $record = Multimedia::query()->where('title', 'Reel Tanpa Thumbnail')->firstOrFail();

    expect($record->thumbnail)->toStartWith('multimedia/thumbnails/short-'.$record->getKey().'-')
        ->and($record->type)->toBe('reels')
        ->and($record->platform)->toBe('instagram');

    Storage::disk('public')->assertExists($record->thumbnail);
});

test('media URL is required and lightly validated for its selected platform', function () {
    $user = multimediaAdmin();

    Livewire::actingAs($user)
        ->test(CreateMultimedia::class)
        ->fillForm([
            'title' => 'URL Tidak Sesuai',
            'type' => 'video',
            'platform' => 'youtube',
            'media_url' => 'https://www.instagram.com/reel/example/',
            'status' => 'draft',
        ])
        ->call('create')
        ->assertHasFormErrors(['media_url']);
});

test('instagram reels and google photos albums use the existing string columns', function () {
    $reel = Multimedia::query()->create([
        'title' => 'Reel Instagram Baru',
        'type' => 'reels',
        'platform' => 'instagram',
        'media_url' => 'https://www.instagram.com/reel/new-content/',
        'thumbnail' => 'multimedia/thumbnails/reel-baru.jpg',
    ]);
    $album = Multimedia::query()->create([
        'title' => 'Album Google Photos Baru',
        'type' => 'gallery',
        'platform' => 'google_photos',
        'media_url' => 'https://photos.app.goo.gl/newAlbum',
        'thumbnail' => 'multimedia/thumbnails/album-baru.jpg',
    ]);

    expect($reel->fresh()->platform)->toBe('instagram')
        ->and($reel->display_type)->toBe('Shorts / Reels')
        ->and($album->fresh()->platform)->toBe('google_photos')
        ->and($album->display_type)->toBe('Photo Album');
});

test('reels and albums can be saved through the form with a thumbnail', function (array $data) {
    Storage::fake('public');
    $user = multimediaAdmin();

    Livewire::actingAs($user)
        ->test(CreateMultimedia::class)
        ->fillForm([
            ...$data,
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 1200, 800),
            'status' => 'draft',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Multimedia::query()->where('title', $data['title'])->exists())->toBeTrue();
})->with([
    'Instagram Reel' => [[
        'title' => 'Reel dari Form',
        'type' => 'reels',
        'platform' => 'instagram',
        'media_url' => 'https://www.instagram.com/reel/form-content/',
    ]],
    'Google Photos album' => [[
        'title' => 'Album dari Form',
        'type' => 'gallery',
        'platform' => 'google_photos',
        'media_url' => 'https://photos.app.goo.gl/formAlbum',
    ]],
]);

test('featured is restricted to one youtube video', function () {
    $first = Multimedia::query()->create([
        'title' => 'Video Utama Pertama',
        'type' => 'video',
        'platform' => 'youtube',
        'media_url' => 'https://youtube.com/watch?v=first1234',
        'featured' => true,
    ]);
    $second = Multimedia::query()->create([
        'title' => 'Video Utama Kedua',
        'type' => 'video',
        'platform' => 'youtube',
        'media_url' => 'https://youtube.com/watch?v=second123',
        'featured' => true,
    ]);
    $reel = Multimedia::query()->create([
        'title' => 'Reel Bukan Utama',
        'type' => 'reels',
        'platform' => 'instagram',
        'media_url' => 'https://instagram.com/reel/example/',
        'featured' => true,
    ]);

    expect($first->fresh()->featured)->toBeFalse()
        ->and($second->fresh()->featured)->toBeTrue()
        ->and($reel->fresh()->featured)->toBeFalse()
        ->and(Multimedia::query()->where('featured', true)->count())->toBe(1);
});

test('public multimedia page uses explicit type and platform mappings', function () {
    $video = Multimedia::query()->create([
        'title' => 'Video Publik Edulaw',
        'type' => 'video',
        'platform' => 'youtube',
        'media_url' => 'https://youtube.com/watch?v=public1234',
        'status' => 'published',
    ]);
    $reel = Multimedia::query()->create([
        'title' => 'Reel Publik Edulaw',
        'type' => 'reels',
        'platform' => 'instagram',
        'media_url' => 'https://instagram.com/reel/public/',
        'thumbnail' => 'multimedia/thumbnails/reel.jpg',
        'status' => 'published',
    ]);
    $album = Multimedia::query()->create([
        'title' => 'Album Publik Edulaw',
        'type' => 'gallery',
        'platform' => 'google_photos',
        'media_url' => 'https://photos.app.goo.gl/publicAlbum',
        'thumbnail' => 'multimedia/thumbnails/album.jpg',
        'status' => 'published',
    ]);
    Multimedia::query()->create([
        'title' => 'Video Draft Tersembunyi',
        'type' => 'video',
        'platform' => 'youtube',
        'media_url' => 'https://youtube.com/watch?v=draft12345',
        'status' => 'draft',
    ]);
    Multimedia::query()->create([
        'title' => 'Podcast Lama Tidak Dipetakan',
        'type' => 'podcast',
        'platform' => 'spotify',
        'media_url' => 'https://open.spotify.com/episode/example',
        'status' => 'published',
    ]);

    $response = $this->get(route('multimedia.index'));

    $response->assertOk()
        ->assertSee($video->title)
        ->assertSee($reel->title)
        ->assertSee($album->title)
        ->assertSee('href="'.$video->media_url.'"', false)
        ->assertSee('href="'.$reel->media_url.'"', false)
        ->assertSee('href="'.$album->media_url.'"', false)
        ->assertSee('href="'.$video->media_url.'" target="_blank" rel="noopener noreferrer"', false)
        ->assertSee('href="'.$reel->media_url.'" target="_blank" rel="noopener noreferrer"', false)
        ->assertSee('href="'.$album->media_url.'" target="_blank" rel="noopener noreferrer"', false)
        ->assertDontSee('Video Draft Tersembunyi')
        ->assertDontSee('Podcast Lama Tidak Dipetakan')
        ->assertDontSee('iframe', false);

    expect(Route::has('multimedia.show'))->toBeFalse();
});

test('youtube thumbnail falls back to the official remote thumbnail URL', function () {
    $record = new Multimedia([
        'platform' => 'youtube',
        'media_url' => 'https://www.youtube.com/watch?v=abc123XYZ_9',
    ]);

    expect($record->thumbnail_url)->toBe('https://i.ytimg.com/vi/abc123XYZ_9/maxresdefault.jpg')
        ->and($record->youtube_thumbnail_fallback_url)->toBe('https://i.ytimg.com/vi/abc123XYZ_9/hqdefault.jpg');
});

test('public layout adapts a single nullable video without repeated placeholders', function () {
    Multimedia::query()->create([
        'title' => 'Video Tunggal Tanpa Metadata',
        'type' => 'video',
        'platform' => 'youtube',
        'media_url' => 'https://youtube.com/watch?v=single1234',
        'description' => null,
        'thumbnail' => null,
        'published_at' => null,
        'status' => 'published',
    ]);

    $response = $this->get(route('multimedia.index'));
    $html = $response->getContent();

    $response->assertOk()
        ->assertSee('Video Pilihan Edulaw')
        ->assertDontSee('Video Lainnya')
        ->assertDontSee('Pagination video YouTube')
        ->assertSee('Konten pendek segera hadir')
        ->assertSee('Dokumentasi kegiatan akan segera tersedia')
        ->assertSee('href="#video"', false)
        ->assertSee('href="#shorts-reels"', false)
        ->assertSee('href="#album-foto"', false)
        ->assertSee('Ajukan Kolaborasi');

    expect(substr_count($html, 'data-featured-media'))->toBe(1)
        ->and(substr_count($html, 'data-secondary-media'))->toBe(0)
        ->and(substr_count($html, 'Konten pendek segera hadir'))->toBe(1)
        ->and(substr_count($html, 'Dokumentasi kegiatan akan segera tersedia'))->toBe(1)
        ->and($html)->toContain('overflow-x-clip');
});

test('youtube grid paginates six videos with video_page and keeps the video anchor', function () {
    $featured = Multimedia::query()->create([
        'title' => 'Featured Tetap di Atas',
        'type' => 'video',
        'platform' => 'youtube',
        'media_url' => 'https://youtube.com/watch?v=fixed12345',
        'status' => 'published',
        'published_at' => now(),
        'featured' => true,
    ]);

    $gridVideos = collect(range(1, 7))->map(fn (int $position) => Multimedia::query()->create([
        'title' => "Video Grid {$position}",
        'type' => 'video',
        'platform' => 'youtube',
        'media_url' => "https://youtube.com/watch?v=gridvideo{$position}",
        'status' => 'published',
        'published_at' => now()->subMinutes($position),
    ]));

    $firstPage = $this->get(route('multimedia.index'))->assertOk();
    $firstSection = Str::between($firstPage->getContent(), '<section id="video"', '<section id="shorts-reels"');

    expect(substr_count($firstSection, 'data-featured-media'))->toBe(1)
        ->and(substr_count($firstSection, 'data-secondary-media'))->toBe(6)
        ->and(substr_count($firstSection, 'href="'.$featured->media_url.'"'))->toBe(1)
        ->and($firstSection)->toContain($gridVideos[0]->title)
        ->not->toContain($gridVideos[6]->title)
        ->toContain('video_page=2#video')
        ->toContain('aria-label="Pagination video YouTube"');

    $secondPage = $this->get(route('multimedia.index', ['video_page' => 2]))->assertOk();
    $secondSection = Str::between($secondPage->getContent(), '<section id="video"', '<section id="shorts-reels"');

    expect(substr_count($secondSection, 'data-secondary-media'))->toBe(1)
        ->and($secondSection)->toContain($gridVideos[6]->title)
        ->not->toContain($gridVideos[0]->title)
        ->and($secondPage->getContent())->toContain('<meta name="robots" content="index,follow">')
        ->toContain('<link rel="canonical" href="'.route('multimedia.index', ['video_page' => 2]).'">');
});

test('two youtube videos render one featured and one secondary external card', function () {
    $secondary = Multimedia::query()->create([
        'title' => 'Video Sekunder Edulaw',
        'type' => 'video',
        'platform' => 'youtube',
        'media_url' => 'https://youtube.com/watch?v=secondary12',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $featured = Multimedia::query()->create([
        'title' => 'Video Featured Edulaw',
        'type' => 'video',
        'platform' => 'youtube',
        'media_url' => 'https://youtube.com/watch?v=featured123',
        'status' => 'published',
        'published_at' => now(),
        'featured' => true,
    ]);

    $html = $this->get(route('multimedia.index'))->assertOk()->getContent();
    $videoSection = Str::between($html, '<section id="video"', '<section id="shorts-reels"');

    expect(substr_count($videoSection, 'data-featured-media'))->toBe(1)
        ->and(substr_count($videoSection, 'data-secondary-media'))->toBe(1)
        ->and($videoSection)->toContain('href="'.$featured->media_url.'" target="_blank" rel="noopener noreferrer"')
        ->and($videoSection)->toContain('href="'.$secondary->media_url.'" target="_blank" rel="noopener noreferrer"')
        ->and(strpos($videoSection, $featured->title))->toBeLessThan(strpos($videoSection, $secondary->title));
});

test('first ordered youtube item becomes featured when none is explicitly featured', function () {
    Multimedia::query()->create([
        'title' => 'Video Lebih Lama',
        'type' => 'video',
        'platform' => 'youtube',
        'media_url' => 'https://youtube.com/watch?v=older12345',
        'status' => 'published',
        'published_at' => now()->subDays(2),
    ]);
    $newest = Multimedia::query()->create([
        'title' => 'Video Paling Baru',
        'type' => 'video',
        'platform' => 'youtube',
        'media_url' => 'https://youtube.com/watch?v=newest1234',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $html = $this->get(route('multimedia.index'))->assertOk()->getContent();
    $featuredCard = Str::between($html, '<article data-featured-media', '</article>');

    expect($featuredCard)->toContain($newest->title)
        ->and($featuredCard)->toContain('target="_blank"')
        ->and($featuredCard)->toContain('rel="noopener noreferrer"');
});

test('multimedia database keeps legacy fields and has no sort order column', function () {
    expect(Schema::hasColumns('multimedia', [
        'slug',
        'embed_url',
        'duration',
        'serial',
        'topic',
        'display_section',
    ]))->toBeTrue()
        ->and(Schema::hasColumn('multimedia', 'sort_order'))->toBeFalse()
        ->and(MultimediaResource::getPages())->not->toHaveKey('view');
});
