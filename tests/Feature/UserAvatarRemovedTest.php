<?php

use App\Models\Author;
use App\Models\User;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

test('admin users do not expose filament avatar images', function () {
    expect(is_subclass_of(User::class, HasAvatar::class))->toBeFalse()
        ->and(method_exists(User::class, 'getFilamentAvatarUrl'))->toBeFalse()
        ->and(method_exists(User::class, 'getAvatarUrlAttribute'))->toBeFalse();
});

test('public author photo falls back to linked user avatar', function () {
    $user = User::query()->create([
        'name' => 'Website Author',
        'email' => 'website-author@example.test',
        'password' => Hash::make('password'),
        'avatar' => 'avatars/website-author.webp',
        'is_active' => true,
    ]);

    $profile = Author::query()
        ->where('user_id', $user->id)
        ->first();

    $profile->forceFill(['photo' => null])->save();
    $profile->load('user');

    expect($profile)->not->toBeNull()
        ->and($profile->photo_url)->toBe(Storage::disk('public')->url('avatars/website-author.webp'));
});

test('blank linked profile photo follows updated user avatar', function () {
    $user = User::query()->create([
        'name' => 'Updated Avatar',
        'email' => 'updated-avatar@example.test',
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);

    $profile = $user->profile()->first();
    $profile->forceFill(['photo' => null])->save();

    $user->forceFill(['avatar' => 'avatars/updated-avatar.webp'])->save();

    expect($profile->refresh()->photo)->toBe('avatars/updated-avatar.webp')
        ->and($profile->photo_url)->toBe(Storage::disk('public')->url('avatars/updated-avatar.webp'));
});
