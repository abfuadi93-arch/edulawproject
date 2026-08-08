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

test('author public roles use three organizational levels and normalize legacy founder values', function () {
    expect(Author::PROFILE_TYPES)->toBe([
        'director' => 'Director',
        'manager' => 'Manager',
        'team' => 'Contributor',
    ])
        ->and(Author::ORGANIZATION_GROUPS)->toBe([
            'research_team' => 'Research Team',
            'internship_member' => 'Internship Member',
            'writer' => 'Writer',
            'speaker_moderator' => 'Speaker and Moderator',
        ])
        ->and(Author::canonicalProfileType('founder'))->toBe('director')
        ->and(Author::canonicalProfileType('co_founder'))->toBe('director')
        ->and(AuthorResource::prepareFormDataForPersistence([
            'name' => 'Profil Lama',
            'profile_type' => 'co_founder',
        ])['profile_type'])->toBe('director');
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
        'organization_group',
        'show_in_contributor_section',
        'sort_order',
    ]))->toBeTrue();

    $author = Author::query()->create([
        'name' => 'Kontributor Baru',
        'slug' => 'kontributor-baru',
    ])->fresh();

    expect($author->show_in_contributor_section)->toBeFalse()
        ->and($author->sort_order)->toBeNull();
});

test('author organization group is explicit for contributors and cleared for higher levels', function () {
    $contributor = AuthorResource::prepareFormDataForPersistence([
        'name' => 'Kontributor Riset',
        'position' => 'General Contributor',
        'profile_type' => 'team',
        'organization_group' => 'research-team',
    ]);
    $inferred = AuthorResource::prepareFormDataForPersistence([
        'name' => 'Pembicara Baru',
        'position' => 'Speaker',
        'profile_type' => 'team',
        'organization_group' => null,
    ]);
    $manager = AuthorResource::prepareFormDataForPersistence([
        'name' => 'Manager Program',
        'profile_type' => 'manager',
        'organization_group' => 'writer',
    ]);

    expect($contributor['organization_group'])->toBe('research_team')
        ->and($inferred['organization_group'])->toBe('speaker_moderator')
        ->and($manager['organization_group'])->toBeNull();
});

test('author display order remains nullable when left empty', function () {
    $data = AuthorResource::prepareFormDataForPersistence([
        'name' => 'Kontributor Tanpa Urutan',
        'sort_order' => '',
    ]);

    expect($data['sort_order'])->toBeNull();
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
        ->assertSee('Peran Publik')
        ->assertSee('Director')
        ->assertSee('Manager')
        ->assertSee('Contributor')
        ->assertSee('Kelompok Organisasi')
        ->assertSee('Research Team')
        ->assertSee('Internship Member')
        ->assertSee('Writer')
        ->assertSee('Speaker and Moderator')
        ->assertSee('Menentukan substruktur Contributor pada halaman Tentang.')
        ->assertSee('Founder dan Co-Founder ditetapkan secara statis pada halaman Tentang.')
        ->assertSee('Opsional. Kontributor Editorial diurutkan otomatis berdasarkan jumlah tulisan; angka ini hanya digunakan saat jumlahnya sama.')
        ->assertSee('Tampilkan di Kontributor Editorial');

    $this->actingAs($user)
        ->get(AuthorResource::getUrl('edit', ['record' => $author]))
        ->assertOk()
        ->assertSee('Profil untuk Diedit')
        ->assertSee('Gunakan foto rasio 1:1, minimal 400 × 400 px.');
});
