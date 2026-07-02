<?php

use App\Filament\Auth\Pages\Register;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

test('admin registration page is available to guests', function () {
    $this->get('/admin/register')
        ->assertOk();
});

test('admin registration creates inactive account pending approval', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(Register::class)
        ->set('data.name', 'Pending Admin')
        ->set('data.email', 'pending-admin@example.test')
        ->set('data.password', 'secure-password')
        ->set('data.passwordConfirmation', 'secure-password')
        ->call('register')
        ->assertHasNoErrors();

    $user = User::query()
        ->where('email', 'pending-admin@example.test')
        ->first();

    expect($user)->not->toBeNull()
        ->and($user->is_active)->toBeFalse()
        ->and(Auth::check())->toBeFalse();
});
