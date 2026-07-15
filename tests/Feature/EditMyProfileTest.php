<?php

use App\Filament\Pages\EditMyProfile;
use App\Models\Author;
use App\Models\Insight;
use App\Models\PageVisit;
use App\Models\Publication;
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

test('account credentials remain available on the separate filament account page', function () {
    $user = makePanelUser('account-page@example.test');

    $this->actingAs($user)
        ->get('/admin/profile')
        ->assertOk();
});

test('every admin role can open my profile page and lazily creates one author', function (string $role) {
    $user = makePanelUser("{$role}@example.test", $role);

    $this->actingAs($user)
        ->get('/admin/edit-my-profile')
        ->assertOk();

    $this->actingAs($user)
        ->get('/admin/edit-my-profile')
        ->assertOk();

    expect(Author::query()->where('user_id', $user->id)->count())->toBe(1)
        ->and($user->refresh()->profile)->not->toBeNull();
})->with([
    'super_admin',
    'editor',
    'writer',
    'program_admin',
    'media_opportunity_admin',
]);

test('user updates their author profile without changing account credentials or another author', function () {
    $user = makePanelUser('owner@example.test');
    $otherUser = makePanelUser('other@example.test');
    $author = $user->ensureProfile();
    $otherAuthor = $otherUser->ensureProfile();
    $accountSnapshot = $user->only(['name', 'email', 'password', 'is_active']);

    Livewire::actingAs($user)
        ->test(EditMyProfile::class)
        ->set('data.name', 'Dr. Penulis Edulaw')
        ->set('data.slug', 'penulis-edulaw')
        ->set('data.title', 'S.H., M.H.')
        ->set('data.bio', 'Penulis dan peneliti hukum tata negara.')
        ->set('data.interests', ['Hukum Tata Negara', 'Pemilu'])
        ->set('data.position', 'Senior Researcher')
        ->set('data.institution', 'Edulaw Project')
        ->set('data.location', 'Jakarta, Indonesia')
        ->set('data.email', 'publik@example.test')
        ->set('data.social_links.linkedin', 'https://linkedin.com/in/penulis-edulaw')
        ->set('data.social_links.orcid', '0000-0001-2345-6789')
        ->set('data.show_in_organization', true)
        ->set('data.seo_title', 'Dr. Penulis Edulaw')
        ->set('data.meta_description', 'Profil penulis dan peneliti hukum Edulaw Project.')
        ->call('save')
        ->assertHasNoErrors();

    $author->refresh();
    $user->refresh();
    $otherAuthor->refresh();

    expect($author->name)->toBe('Dr. Penulis Edulaw')
        ->and($author->slug)->toBe('penulis-edulaw')
        ->and($author->title)->toBe('S.H., M.H.')
        ->and($author->interests)->toBe(['Hukum Tata Negara', 'Pemilu'])
        ->and($author->socialLinksMap()['linkedin'])->toBe('https://linkedin.com/in/penulis-edulaw')
        ->and($author->show_in_organization)->toBeTrue()
        ->and($user->only(['name', 'email', 'password', 'is_active']))->toBe($accountSnapshot)
        ->and($otherAuthor->name)->toBe('Panel User');
});

test('slug must remain unique between author profiles', function () {
    $firstUser = makePanelUser('first@example.test');
    $secondUser = makePanelUser('second@example.test');
    $firstAuthor = $firstUser->ensureProfile();
    $secondUser->ensureProfile();

    Livewire::actingAs($secondUser)
        ->test(EditMyProfile::class)
        ->set('data.slug', $firstAuthor->slug)
        ->call('save')
        ->assertHasErrors(['data.slug' => 'unique']);
});

test('profile changes are immediately visible on the public author page', function () {
    $user = makePanelUser('public-profile@example.test');
    $author = $user->ensureProfile();

    Livewire::actingAs($user)
        ->test(EditMyProfile::class)
        ->set('data.name', 'Nama Publik Baru')
        ->set('data.slug', 'nama-publik-baru')
        ->set('data.location', 'Bandung, Indonesia')
        ->set('data.bio', 'Biografi publik yang telah diperbarui.')
        ->call('save')
        ->assertHasNoErrors();

    $this->get(route('profiles.show', $author->refresh()->slug))
        ->assertOk()
        ->assertSee('Nama Publik Baru')
        ->assertSee('Bandung, Indonesia')
        ->assertSee('Biografi publik yang telah diperbarui.');
});

test('profile page shows author statistics and five latest linked contents', function () {
    $user = makePanelUser('statistics@example.test');
    $author = $user->ensureProfile();

    foreach (range(1, 6) as $index) {
        $insight = Insight::query()->create([
            'title' => "Insight {$index}",
            'slug' => "insight-statistik-{$index}",
            'status' => $index === 1 ? 'draft' : 'published',
            'published_at' => $index === 1 ? null : now()->subDays($index),
            'created_by' => $user->id,
        ]);
        $author->insights()->attach($insight->id, ['author_order' => 1, 'role' => 'Author']);
    }

    foreach (range(1, 6) as $index) {
        $publication = Publication::query()->create([
            'title' => "Publikasi {$index}",
            'slug' => "publikasi-statistik-{$index}",
            'status' => 'published',
            'published_at' => now()->subDays($index),
            'created_by' => $user->id,
        ]);
        $author->publications()->attach($publication->id, ['author_order' => 1, 'role' => 'Author']);
    }

    PageVisit::query()->create([
        'visitor_id' => 'profile-statistics-visitor',
        'method' => 'GET',
        'path' => '/profil/'.$author->slug,
        'full_url' => route('profiles.show', $author->slug),
        'route_name' => 'profiles.show',
        'status_code' => 200,
        'visited_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(EditMyProfile::class)
        ->assertSee('Insight diterbitkan')
        ->assertSee('Total View')
        ->assertSee('Insight 6')
        ->assertDontSee('Insight 1')
        ->assertSee('Publikasi 6')
        ->assertDontSee('Publikasi 1');
});
