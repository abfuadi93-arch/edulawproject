<?php

use App\Filament\Resources\Multimedia\MultimediaResource;
use App\Filament\Resources\Multimedia\Pages\CreateMultimedia;
use App\Filament\Resources\Multimedia\Pages\EditMultimedia;
use App\Models\Multimedia;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
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

    expect($record->thumbnail_url)->toBe('https://i.ytimg.com/vi/abc123XYZ_9/hqdefault.jpg');
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
