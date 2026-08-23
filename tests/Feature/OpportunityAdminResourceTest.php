<?php

use App\Filament\Resources\Opportunities\OpportunityResource;
use App\Models\User;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Role;

test('opportunity admin resource derives excerpt seo and og image from content', function () {
    $description = '<p>Peluang ini membuka ruang pengembangan kapasitas hukum, riset kebijakan, dan kolaborasi publik untuk mahasiswa serta komunitas.</p>';

    $data = OpportunityResource::prepareFormDataForPersistence([
        'title' => 'Open Collaboration Edulaw',
        'slug' => '',
        'description' => $description,
        'posters' => [
            'opportunities/open-collaboration.jpg',
            'opportunities/open-collaboration-detail.jpg',
        ],
        'status' => null,
        'seo_title' => null,
        'seo_description' => null,
        'og_image' => null,
    ]);

    expect($data['slug'])->toBe('open-collaboration-edulaw')
        ->and($data['status'])->toBe('open')
        ->and($data['excerpt'])->toStartWith('Peluang ini membuka')
        ->and(mb_strlen($data['excerpt']))->toBeLessThanOrEqual(220)
        ->and($data['seo_title'])->toBe('Open Collaboration Edulaw')
        ->and($data['seo_description'])->toStartWith('Peluang ini membuka')
        ->and(mb_strlen($data['seo_description']))->toBeLessThanOrEqual(180)
        ->and($data['posters'])->toBe([
            'opportunities/open-collaboration.jpg',
            'opportunities/open-collaboration-detail.jpg',
        ])
        ->and($data['poster'])->toBe('opportunities/open-collaboration.jpg')
        ->and($data['og_image'])->toBe('opportunities/open-collaboration.jpg');
});

test('opportunity admin resource preserves a legacy single poster', function () {
    $data = OpportunityResource::prepareFormDataForPersistence([
        'title' => 'Peluang Lama',
        'poster' => 'opportunities/legacy-poster.jpg',
    ]);

    expect($data['posters'])->toBe(['opportunities/legacy-poster.jpg'])
        ->and($data['poster'])->toBe('opportunities/legacy-poster.jpg')
        ->and($data['og_image'])->toBe('opportunities/legacy-poster.jpg');
});

test('opportunity admin resource keeps the primary upload as slide one and appends extra posters', function () {
    $data = OpportunityResource::prepareFormDataForPersistence([
        'title' => 'Peluang dengan Poster Tambahan',
        'poster' => 'opportunities/poster-satu.jpg',
        'additional_posters' => [
            ['image' => 'opportunities/poster-dua.jpg'],
            ['image' => 'opportunities/poster-tiga.jpg'],
        ],
        'og_image' => null,
    ]);

    expect($data['posters'])->toBe([
        'opportunities/poster-satu.jpg',
        'opportunities/poster-dua.jpg',
        'opportunities/poster-tiga.jpg',
    ])
        ->and($data['poster'])->toBe('opportunities/poster-satu.jpg')
        ->and($data['og_image'])->toBe('opportunities/poster-satu.jpg')
        ->and($data)->not->toHaveKey('additional_posters');
});

test('opportunity create form presents the primary poster before additional posters', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $role = Role::findOrCreate('super_admin');
    $user = User::query()->create([
        'name' => 'Super Admin Poster Peluang',
        'email' => 'opportunity-poster-admin@example.test',
        'password' => 'secret-password',
        'is_active' => true,
    ]);
    $user->assignRole($role);

    $this->actingAs($user)
        ->get(OpportunityResource::getUrl('create'))
        ->assertOk()
        ->assertSeeInOrder(['Status Peluang', 'Poster'])
        ->assertSee('Poster Slide 1')
        ->assertSee('Poster Tambahan')
        ->assertSee('Unggah Poster Slide 1 agar tombol Tambah Poster tersedia.');
});

test('opportunity admin resource exposes only open closed and archived statuses', function () {
    expect(OpportunityResource::statusOptions())->toBe([
        'open' => 'Dibuka',
        'closed' => 'Ditutup',
        'archived' => 'Diarsipkan',
    ])
        ->and(OpportunityResource::statusLabel('open'))->toBe('Dibuka')
        ->and(OpportunityResource::statusLabel('closed'))->toBe('Ditutup')
        ->and(OpportunityResource::statusLabel('archived'))->toBe('Diarsipkan')
        ->and(OpportunityResource::statusLabel('draft'))->toBe('Diarsipkan')
        ->and(OpportunityResource::normalizeStatusForForm('draft'))->toBe('open');
});

test('opportunity admin resource exposes career as an opportunity type', function () {
    expect(OpportunityResource::typeOptions())
        ->toHaveKey('career', 'Karier');
});

test('opportunity deadline labels are localized and null safe', function () {
    expect(OpportunityResource::deadlineRelativeLabel(null))->toBeNull()
        ->and(OpportunityResource::deadlineRelativeLabel(today()))->toBe('Berakhir hari ini')
        ->and(OpportunityResource::deadlineRelativeLabel(today()->addDays(3)))->toBe('3 hari lagi')
        ->and(OpportunityResource::deadlineRelativeLabel(today()->subDays(2)))->toBe('Lewat 2 hari');
});
