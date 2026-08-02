<?php

use App\Filament\Resources\Authors\AuthorResource;
use App\Models\Author;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

test('author admin resource derives slug from name when slug is empty', function () {
    $data = AuthorResource::prepareFormDataForPersistence([
        'name' => 'Nabila Rahma',
        'slug' => '',
    ]);

    expect($data['slug'])->toBe('nabila-rahma');
});

test('author bio supports one thousand characters', function () {
    $bio = str_repeat('a', 1000);

    $author = Author::create([
        'name' => 'Profil Bio Panjang',
        'slug' => 'profil-bio-panjang',
        'profile_type' => 'team',
        'bio' => $bio,
        'is_active' => true,
    ]);

    expect(mb_strlen((string) $author->fresh()->bio))->toBe(1000);
});

test('author schema exposes editorial contributor curation fields with safe defaults', function () {
    expect(Schema::hasColumns('authors', [
        'show_in_contributor_section',
        'sort_order',
    ]))->toBeTrue();

    $author = Author::query()->create([
        'name' => 'Kontributor Baru',
        'slug' => 'kontributor-baru',
    ])->fresh();

    expect($author->show_in_contributor_section)->toBeFalse()
        ->and($author->sort_order)->toBe(0);
});

test('visible contributor scope requires active and selected authors and supports editorial ordering', function () {
    Author::query()->create([
        'name' => 'Budi Kedua',
        'slug' => 'budi-kedua',
        'sort_order' => 20,
        'is_active' => true,
        'show_in_contributor_section' => true,
    ]);

    Author::query()->create([
        'name' => 'Ani Pertama',
        'slug' => 'ani-pertama',
        'sort_order' => 10,
        'is_active' => true,
        'show_in_contributor_section' => true,
    ]);

    Author::query()->create([
        'name' => 'Tidak Dipilih',
        'slug' => 'tidak-dipilih',
        'sort_order' => 1,
        'is_active' => true,
        'show_in_contributor_section' => false,
    ]);

    Author::query()->create([
        'name' => 'Tidak Aktif',
        'slug' => 'tidak-aktif',
        'sort_order' => 0,
        'is_active' => false,
        'show_in_contributor_section' => true,
    ]);

    $authors = Author::query()
        ->visibleInContributorSection()
        ->orderBy('sort_order')
        ->orderBy('name')
        ->pluck('slug')
        ->all();

    expect($authors)->toBe(['ani-pertama', 'budi-kedua']);
});

test('author avatar safely falls back to initials in admin', function () {
    $withoutPhoto = new Author([
        'name' => 'Nadia Peneliti',
        'slug' => 'nadia-peneliti',
    ]);

    $withUnavailablePhoto = new Author([
        'name' => 'Redaksi Edulaw',
        'slug' => 'redaksi-edulaw',
        'photo' => 'authors/foto-tidak-tersedia.jpg',
    ]);

    expect($withoutPhoto->initials)->toBe('NP')
        ->and((string) AuthorResource::avatarHtml($withoutPhoto))
        ->toContain('>NP</span>')
        ->not->toContain('<img')
        ->and((string) AuthorResource::avatarHtml($withUnavailablePhoto))
        ->toContain('>RE<img')
        ->toContain('onerror="this.remove()"');
});

test('super admin can open create and edit author forms with contributor controls', function () {
    $role = Role::findOrCreate('super_admin');
    $user = User::query()->create([
        'name' => 'Super Admin Author',
        'email' => 'author-admin@example.test',
        'password' => 'secret-password',
        'is_active' => true,
    ]);
    $user->assignRole($role);

    $author = Author::query()->create([
        'name' => 'Profil untuk Diedit',
        'slug' => 'profil-untuk-diedit',
        'position' => 'Peneliti',
        'institution' => 'Edulaw Project',
        'sort_order' => 3,
        'show_in_contributor_section' => true,
    ]);

    $this->actingAs($user)
        ->get(AuthorResource::getUrl('index'))
        ->assertOk()
        ->assertSee('Profil')
        ->assertSee('>PU</span>', false);

    $this->actingAs($user)
        ->get(AuthorResource::getUrl('create'))
        ->assertOk()
        ->assertSee('Tampilkan di Kontributor Editorial');

    $this->actingAs($user)
        ->get(AuthorResource::getUrl('edit', ['record' => $author]))
        ->assertOk()
        ->assertSee('Profil untuk Diedit')
        ->assertSee('Gunakan foto rasio 1:1, minimal 400 × 400 px.');
});
