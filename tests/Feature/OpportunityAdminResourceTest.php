<?php

use App\Filament\Resources\Opportunities\OpportunityResource;

test('opportunity admin resource derives excerpt seo and og image from content', function () {
    $description = '<p>Peluang ini membuka ruang pengembangan kapasitas hukum, riset kebijakan, dan kolaborasi publik untuk mahasiswa serta komunitas.</p>';

    $data = OpportunityResource::prepareFormDataForPersistence([
        'title' => 'Open Collaboration Edulaw',
        'slug' => '',
        'description' => $description,
        'poster' => 'opportunities/open-collaboration.jpg',
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
        ->and($data['og_image'])->toBe('opportunities/open-collaboration.jpg');
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

test('opportunity deadline labels are localized and null safe', function () {
    expect(OpportunityResource::deadlineRelativeLabel(null))->toBeNull()
        ->and(OpportunityResource::deadlineRelativeLabel(today()))->toBe('Berakhir hari ini')
        ->and(OpportunityResource::deadlineRelativeLabel(today()->addDays(3)))->toBe('3 hari lagi')
        ->and(OpportunityResource::deadlineRelativeLabel(today()->subDays(2)))->toBe('Lewat 2 hari');
});
