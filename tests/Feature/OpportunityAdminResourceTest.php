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
        'open' => 'Open',
        'closed' => 'Closed',
        'archived' => 'Archived',
    ])
        ->and(OpportunityResource::statusLabel('open'))->toBe('Open')
        ->and(OpportunityResource::statusLabel('closed'))->toBe('Closed')
        ->and(OpportunityResource::statusLabel('archived'))->toBe('Archived')
        ->and(OpportunityResource::statusLabel('draft'))->toBe('Archived')
        ->and(OpportunityResource::normalizeStatusForForm('draft'))->toBe('open');
});
