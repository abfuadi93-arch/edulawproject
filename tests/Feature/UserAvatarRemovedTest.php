<?php

use App\Models\Author;
use App\Models\User;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Support\Facades\Hash;

test('admin users do not expose filament avatar images', function () {
    expect(is_subclass_of(User::class, HasAvatar::class))->toBeFalse()
        ->and(method_exists(User::class, 'getFilamentAvatarUrl'))->toBeFalse()
        ->and(method_exists(User::class, 'getAvatarUrlAttribute'))->toBeFalse();
});

test('creating admin user does not automatically create public author profile', function () {
    $user = User::query()->create([
        'name' => 'Website Author',
        'email' => 'website-author@example.test',
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);

    expect($user->profile()->exists())->toBeFalse();
});

test('public author photo does not fall back to linked user avatar', function () {
    $user = User::query()->create([
        'name' => 'Linked Author',
        'email' => 'linked-author@example.test',
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);

    $user->forceFill(['avatar' => 'avatars/linked-author.webp'])->save();

    $profile = Author::query()->create([
        'user_id' => $user->id,
        'name' => 'Linked Author',
        'slug' => 'linked-author',
        'email' => 'linked-author@example.test',
        'photo' => null,
        'profile_type' => 'team',
        'is_active' => true,
    ]);

    expect($profile->photo_url)->toBeNull();
});
