<?php

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

test('user avatar url normalizes public storage paths and avoids localhost urls', function () {
    Storage::fake('public');
    Storage::disk('public')->put('profiles/admin.jpg', 'avatar');
    Config::set('filesystems.disks.public.url', 'http://localhost/storage');

    $user = new User([
        'name' => 'Super Admin',
        'avatar' => 'storage/profiles/admin.jpg',
    ]);

    expect($user->avatar_url)->toBe('/storage/profiles/admin.jpg')
        ->and($user->initials)->toBe('SA');
});

test('user avatar url returns null for localhost or missing avatars', function () {
    Storage::fake('public');

    $localhostUser = new User([
        'name' => 'Admin',
        'avatar' => 'http://localhost/storage/profiles/missing.jpg',
    ]);

    $missingFileUser = new User([
        'name' => 'Admin',
        'avatar' => 'profiles/missing.jpg',
    ]);

    expect($localhostUser->avatar_url)->toBeNull()
        ->and($missingFileUser->avatar_url)->toBeNull()
        ->and($localhostUser->initials)->toBe('AD');
});
