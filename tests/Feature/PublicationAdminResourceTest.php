<?php

use App\Filament\Resources\Publications\PublicationResource;

test('publication admin resource derives excerpt and seo fallback from description', function () {
    $description = '<p>Kajian ini membahas reformasi hukum acara, akses keadilan, dan penguatan literasi hukum publik untuk masyarakat luas.</p>';

    $data = PublicationResource::prepareFormDataForPersistence([
        'title' => 'Reformasi Hukum Acara',
        'slug' => '',
        'description' => $description,
        'cover_image' => 'publications/covers/reformasi.jpg',
        'source_name' => null,
        'seo_title' => null,
        'seo_description' => null,
        'og_image' => null,
    ]);

    expect($data['slug'])->toBe('reformasi-hukum-acara')
        ->and($data['excerpt'])->toStartWith('Kajian ini membahas')
        ->and(mb_strlen($data['excerpt']))->toBeLessThanOrEqual(220)
        ->and($data['source_name'])->toBe('Edulaw Project')
        ->and($data['seo_title'])->toBe('Reformasi Hukum Acara')
        ->and($data['seo_description'])->toStartWith('Kajian ini membahas')
        ->and(mb_strlen($data['seo_description']))->toBeLessThanOrEqual(180)
        ->and($data['og_image'])->toBe('publications/covers/reformasi.jpg');
});

test('publication admin resource exposes simplified statuses and keeps archived as legacy label', function () {
    expect(PublicationResource::statusOptions())->toBe([
        'draft' => 'Draft',
        'reviewed' => 'Reviewed',
        'published' => 'Published',
    ])
        ->and(PublicationResource::statusLabel('archived'))->toBe('Archived')
        ->and(PublicationResource::statusLabel('reviewed'))->toBe('Reviewed')
        ->and(PublicationResource::normalizeStatusForForm('archived'))->toBe('draft');
});
