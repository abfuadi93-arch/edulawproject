<?php

use App\Filament\Resources\Insights\InsightResource;

test('insight admin resource derives excerpt and seo fallback from content', function () {
    $content = '<p>Paragraf awal artikel berisi uraian panjang tentang hukum publik, demokrasi, dan kebijakan negara yang ditulis untuk pembaca umum dengan bahasa yang mudah dipahami.</p>';

    $data = InsightResource::prepareFormDataForPersistence([
        'title' => 'Membaca Hukum Publik',
        'slug' => '',
        'content' => $content,
        'cover_image' => 'insights/hukum-publik.jpg',
        'seo_title' => null,
        'seo_description' => null,
        'og_image' => null,
    ]);

    expect($data['slug'])->toBe('membaca-hukum-publik')
        ->and($data['excerpt'])->toStartWith('Paragraf awal artikel')
        ->and(mb_strlen($data['excerpt']))->toBeLessThanOrEqual(220)
        ->and($data['seo_title'])->toBe('Membaca Hukum Publik')
        ->and($data['seo_description'])->toStartWith('Paragraf awal artikel')
        ->and(mb_strlen($data['seo_description']))->toBeLessThanOrEqual(180)
        ->and($data['og_image'])->toBe('insights/hukum-publik.jpg')
        ->and($data['reading_time'])->toBe(1);
});

test('insight admin resource exposes simplified statuses and maps legacy values', function () {
    expect(InsightResource::statusOptions())->toBe([
        'draft' => 'Draft',
        'reviewed' => 'Reviewed',
        'published' => 'Published',
    ])
        ->and(InsightResource::statusLabel('submitted'))->toBe('Reviewed')
        ->and(InsightResource::statusLabel('archived'))->toBe('Draft')
        ->and(InsightResource::statusLabel('published'))->toBe('Published');
});
