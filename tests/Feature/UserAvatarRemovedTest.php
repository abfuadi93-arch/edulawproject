<?php

use App\Models\User;
use Filament\Models\Contracts\HasAvatar;

test('admin users do not expose filament avatar images', function () {
    expect(is_subclass_of(User::class, HasAvatar::class))->toBeFalse()
        ->and(method_exists(User::class, 'getFilamentAvatarUrl'))->toBeFalse()
        ->and(method_exists(User::class, 'getAvatarUrlAttribute'))->toBeFalse();
});
