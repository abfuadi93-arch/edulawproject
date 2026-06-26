<?php

use App\Filament\Pages\EditMyProfile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

function makePanelUser(string $email = 'panel@example.test', ?string $role = null): User
{
    $user = User::query()->create([
        'name' => 'Panel User',
        'email' => $email,
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);

    if ($role) {
        Role::query()->firstOrCreate([
            'name' => $role,
            'guard_name' => 'web',
        ]);

        $user->assignRole($role);
    }

    return $user;
}

test('my profile page redirects guests to admin login', function () {
    $this->get('/admin/edit-my-profile')
        ->assertRedirect('/admin/login');
});

test('every admin role can open my profile page', function (string $role) {
    $user = makePanelUser("{$role}@example.test", $role);

    $this->actingAs($user)
        ->get('/admin/edit-my-profile')
        ->assertOk();
})->with([
    'super_admin',
    'editor',
    'writer',
    'program_admin',
    'media_opportunity_admin',
]);

test('user can update only their own profile without changing blank password', function () {
    $user = makePanelUser('owner@example.test');
    $otherUser = makePanelUser('other@example.test');
    $oldPassword = $user->password;
    $otherUserName = $otherUser->name;

    Livewire::actingAs($user)
        ->test(EditMyProfile::class)
        ->set('data.name', 'Updated Owner')
        ->set('data.email', 'updated-owner@example.test')
        ->set('data.bio', 'Bio pribadi')
        ->set('data.institution', 'Edulaw Project')
        ->set('data.position', 'Writer')
        ->set('data.password', '')
        ->set('data.password_confirmation', '')
        ->call('save')
        ->assertHasNoErrors();

    $user->refresh();
    $otherUser->refresh();

    expect($user->name)->toBe('Updated Owner')
        ->and($user->email)->toBe('updated-owner@example.test')
        ->and($user->bio)->toBe('Bio pribadi')
        ->and($user->institution)->toBe('Edulaw Project')
        ->and($user->position)->toBe('Writer')
        ->and($user->password)->toBe($oldPassword)
        ->and($otherUser->name)->toBe($otherUserName);
});

test('user can update password from my profile page', function () {
    $user = makePanelUser('password@example.test');

    Livewire::actingAs($user)
        ->test(EditMyProfile::class)
        ->set('data.password', 'new-secure-password')
        ->set('data.password_confirmation', 'new-secure-password')
        ->call('save')
        ->assertHasNoErrors();

    expect(Hash::check('new-secure-password', $user->refresh()->password))->toBeTrue();
});
