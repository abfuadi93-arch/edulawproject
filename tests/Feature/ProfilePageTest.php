<?php

use App\Models\Author;
use App\Models\ContentBlock;
use App\Models\Insight;
use App\Models\InsightCategory;
use App\Models\Publication;
use App\Models\PublicationType;
use App\Models\User;

test('active public profile page shows biography writings and publications', function () {
    $author = Author::query()->create([
        'name' => 'Aulia Rahman',
        'slug' => 'aulia-rahman',
        'bio' => 'Penulis hukum tata negara dan kebijakan publik.',
        'institution' => 'Edulaw Project',
        'position' => 'Researcher',
        'profile_type' => 'internal_author',
        'is_active' => true,
    ]);

    $category = InsightCategory::query()->create([
        'name' => 'Konstitusi',
        'slug' => 'konstitusi',
        'is_active' => true,
    ]);

    $type = PublicationType::query()->create([
        'name' => 'Policy Brief',
        'slug' => 'policy-brief',
        'is_active' => true,
    ]);

    $publishedInsight = Insight::query()->create([
        'insight_category_id' => $category->id,
        'title' => 'Membaca Konstitusi Secara Publik',
        'slug' => 'membaca-konstitusi-secara-publik',
        'excerpt' => 'Tulisan tentang literasi konstitusi.',
        'status' => 'published',
        'published_at' => now(),
        'reading_time' => 5,
    ]);

    $draftInsight = Insight::query()->create([
        'insight_category_id' => $category->id,
        'title' => 'Catatan Draft Tidak Tampil',
        'slug' => 'catatan-draft-tidak-tampil',
        'status' => 'draft',
    ]);

    $publication = Publication::query()->create([
        'publication_type_id' => $type->id,
        'title' => 'Policy Brief Literasi Konstitusi',
        'slug' => 'policy-brief-literasi-konstitusi',
        'excerpt' => 'Publikasi tentang literasi konstitusi.',
        'status' => 'published',
        'published_at' => now()->toDateString(),
    ]);

    $author->insights()->attach($publishedInsight->id, ['author_order' => 1, 'role' => 'Author']);
    $author->insights()->attach($draftInsight->id, ['author_order' => 2, 'role' => 'Author']);
    $author->publications()->attach($publication->id, ['author_order' => 1, 'role' => 'Author']);

    $this->get(route('profiles.show', $author->slug))
        ->assertOk()
        ->assertSee('Aulia Rahman')
        ->assertSee('Penulis hukum tata negara dan kebijakan publik.')
        ->assertSee('Membaca Konstitusi Secara Publik')
        ->assertSee('Policy Brief Literasi Konstitusi')
        ->assertDontSee('Catatan Draft Tidak Tampil');
});

test('inactive public profile page is not visible', function () {
    $author = Author::query()->create([
        'name' => 'Profil Nonaktif',
        'slug' => 'profil-nonaktif',
        'is_active' => false,
    ]);

    $this->get(route('profiles.show', $author->slug))
        ->assertNotFound();
});

test('public profile hides technical user data', function () {
    $user = User::query()->create([
        'name' => 'Nabila Publik',
        'email' => 'login-internal@example.test',
        'password' => bcrypt('password'),
        'position' => 'user',
        'institution' => 'user',
    ]);

    $author = Author::query()->create([
        'user_id' => $user->id,
        'name' => 'Nabila Publik',
        'slug' => 'nabila-publik',
        'is_active' => true,
    ]);

    $this->get(route('profiles.show', $author->slug))
        ->assertOk()
        ->assertSee('Nabila Publik')
        ->assertSee('Contributor')
        ->assertSee('Edulaw Project')
        ->assertSee('Nabila Publik merupakan bagian dari Edulaw Project')
        ->assertDontSeeText('user')
        ->assertDontSee('login-internal@example.test');
});

test('public profile shows compact latest writings without pagination', function () {
    $author = Author::query()->create([
        'name' => 'Penulis Ringkas',
        'slug' => 'penulis-ringkas',
        'is_active' => true,
    ]);

    $category = InsightCategory::query()->create([
        'name' => 'Legal Insight',
        'slug' => 'legal-insight',
        'is_active' => true,
    ]);

    foreach (range(1, 5) as $index) {
        $insight = Insight::query()->create([
            'insight_category_id' => $category->id,
            'title' => "Tulisan Ringkas {$index}",
            'slug' => "tulisan-ringkas-{$index}",
            'excerpt' => "Ringkasan tulisan {$index}.",
            'status' => 'published',
            'published_at' => now()->subDays($index),
            'reading_time' => $index + 1,
        ]);

        $author->insights()->attach($insight->id, ['author_order' => $index, 'role' => 'Author']);
    }

    $this->get(route('profiles.show', $author->slug))
        ->assertOk()
        ->assertSee('Tulisan Terbaru oleh Penulis Ringkas')
        ->assertSee('Tulisan lainnya')
        ->assertSee('Tulisan Ringkas 1')
        ->assertSee('Tulisan Ringkas 4')
        ->assertDontSee('Tulisan Ringkas 5')
        ->assertSee('author=penulis-ringkas', false)
        ->assertDontSee('Pagination Navigation');
});

test('public profile limits publications to latest three compact items', function () {
    $author = Author::query()->create([
        'name' => 'Peneliti Publikasi',
        'slug' => 'peneliti-publikasi',
        'is_active' => true,
    ]);

    $type = PublicationType::query()->create([
        'name' => 'Kajian',
        'slug' => 'kajian',
        'is_active' => true,
    ]);

    foreach (range(1, 4) as $index) {
        $publication = Publication::query()->create([
            'publication_type_id' => $type->id,
            'title' => "Publikasi Ringkas {$index}",
            'slug' => "publikasi-ringkas-{$index}",
            'excerpt' => "Ringkasan publikasi {$index}.",
            'status' => 'published',
            'published_at' => now()->subDays($index)->toDateString(),
        ]);

        $author->publications()->attach($publication->id, ['author_order' => $index, 'role' => 'Author']);
    }

    $this->get(route('profiles.show', $author->slug))
        ->assertOk()
        ->assertSee('Publikasi oleh Peneliti Publikasi')
        ->assertSee('4 publikasi')
        ->assertSee('Publikasi Ringkas 1')
        ->assertSee('Publikasi Ringkas 3')
        ->assertDontSee('Publikasi Ringkas 4')
        ->assertDontSee('Pagination Navigation');
});

test('about page team cards link to active public profiles', function () {
    $activeProfile = Author::query()->create([
        'name' => 'Abdul Basid Fuadi',
        'slug' => 'abdul-basid-fuadi',
        'photo' => 'authors/abdul-basid-fuadi.webp',
        'position' => 'Founder',
        'interests' => 'Hukum tata negara, demokrasi',
        'profile_type' => 'founder',
        'sort_order' => 10,
        'is_active' => true,
    ]);

    $inactiveProfile = Author::query()->create([
        'name' => 'Azmi Fathu Rohman',
        'slug' => 'azmi-fathu-rohman',
        'profile_type' => 'co_founder',
        'is_active' => false,
    ]);

    $directorProfile = Author::query()->create([
        'name' => 'Umi Zakia Azzahro',
        'slug' => 'umi-zakia-azzahro',
        'position' => 'Director of Operations (non-aktif)',
        'interests' => 'Operasional, kolaborasi',
        'profile_type' => 'co_founder',
        'sort_order' => 12,
        'is_active' => true,
    ]);

    $managerProfile = Author::query()->create([
        'name' => 'Manager Baru',
        'slug' => 'manager-baru',
        'position' => 'Manager Editorial',
        'interests' => 'Editorial, riset hukum',
        'profile_type' => 'manager',
        'sort_order' => 20,
        'is_active' => true,
    ]);

    $firstManagerProfile = Author::query()->create([
        'name' => 'Zed Manager',
        'slug' => 'zed-manager',
        'position' => 'Manager Program',
        'interests' => 'Program',
        'profile_type' => 'manager',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $teamProfile = Author::query()->create([
        'name' => 'Fadlah Nur',
        'slug' => 'fadlah-nur',
        'profile_type' => 'team',
        'sort_order' => 30,
        'is_active' => true,
    ]);

    $seniorResearcherProfile = Author::query()->create([
        'name' => 'Lalu Rizqi Ramdani Alfaen',
        'slug' => 'lalu-rizqi-ramdani-alfaen',
        'profile_type' => 'team',
        'sort_order' => 99,
        'is_active' => true,
    ]);

    $publicOnlyProfile = Author::query()->create([
        'name' => 'Kontributor Publik',
        'slug' => 'kontributor-publik',
        'bio' => 'Profil publik yang tidak masuk struktur organisasi.',
        'profile_type' => 'team',
        'is_active' => true,
        'show_in_organization' => false,
    ]);

    Author::query()->create([
        'name' => 'Super Admin',
        'slug' => 'super-admin',
        'position' => 'superadmin',
        'profile_type' => 'team',
        'is_active' => true,
    ]);

    Author::query()->create([
        'name' => 'Redaksi Edulaw',
        'slug' => 'redaksi-edulaw',
        'position' => 'admin',
        'profile_type' => 'team',
        'is_active' => true,
    ]);

    ContentBlock::query()->create([
        'area' => 'about.managers',
        'title' => 'Nabila Rahma',
        'subtitle' => 'Manager Program',
        'body' => 'Mengelola perencanaan, pelaksanaan, dan evaluasi program edukasi hukum.',
        'is_active' => true,
    ]);

    $this->get(route('about'))
        ->assertOk()
        ->assertSee('Penggerak Edulaw Project')
        ->assertSee('Vision &amp; Mission', false)
        ->assertSee('Menjadi wadah edukasi hukum yang berorientasi pada kesetaraan, kemanusiaan, dan kemajuan')
        ->assertSee('Menyediakan pendidikan hukum yang berkualitas dan setara bagi semua lapisan masyarakat.')
        ->assertSee('Memperluas jaringan keilmuan melalui program kolaboratif.')
        ->assertSee('Struktur Organisasi')
        ->assertSee('Edulaw Project Organizational Structure')
        ->assertSeeInOrder(['Penggerak Edulaw Project', 'Umi Zakia Azzahro', 'Co-Founder', 'Vision'])
        ->assertSee(route('profiles.show', $activeProfile->slug), false)
        ->assertSee(route('profiles.show', $directorProfile->slug), false)
        ->assertSee(route('profiles.show', $managerProfile->slug), false)
        ->assertSee(route('profiles.show', $firstManagerProfile->slug), false)
        ->assertSee(route('profiles.show', $teamProfile->slug), false)
        ->assertSee(route('profiles.show', $seniorResearcherProfile->slug), false)
        ->assertSeeInOrder(['Edulaw Project Organizational Structure', 'Director', 'Umi Zakia Azzahro', 'Director of Operations (non-aktif)', 'Manager'])
        ->assertSeeInOrder(['Zed Manager', 'Manager Baru'])
        ->assertSeeInOrder(['Research Team', 'Lalu Rizqi Ramdani Alfaen', 'Senior Researcher', 'Fadlah Nur', 'Junior Researcher'])
        ->assertDontSee('Internship Member')
        ->assertDontSee('Tim Pelaksana')
        ->assertSee('Manager Editorial')
        ->assertSee('Hukum tata negara')
        ->assertSee('demokrasi')
        ->assertSee('Editorial')
        ->assertSee('riset hukum')
        ->assertSee('authors/abdul-basid-fuadi.webp')
        ->assertDontSee('Nabila Rahma')
        ->assertDontSee('Super Admin')
        ->assertDontSee('Redaksi Edulaw')
        ->assertDontSee('Kontributor Publik')
        ->assertDontSee(route('profiles.show', $publicOnlyProfile->slug), false)
        ->assertDontSee(route('profiles.show', $inactiveProfile->slug), false);

    $this->get(route('profiles.show', $publicOnlyProfile->slug))
        ->assertOk()
        ->assertSee('Kontributor Publik')
        ->assertSee('Profil publik yang tidak masuk struktur organisasi.');
});
