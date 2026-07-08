<?php

use App\Filament\Resources\ProgramResource;

test('program admin resource derives short description seo and cta fallback', function () {
    $description = '<p>Diskusi ini membahas kemerdekaan kekuasaan kehakiman dalam negara hukum demokratis serta tantangan independensi peradilan dalam praktik ketatanegaraan Indonesia.</p>';

    $data = ProgramResource::prepareFormDataForPersistence([
        'name' => 'Kemerdekaan Kekuasaan Kehakiman',
        'slug' => '',
        'description' => $description,
        'registration_link' => 'https://example.test/daftar',
        'primary_button_text' => null,
        'primary_button_url' => null,
        'secondary_button_text' => null,
        'secondary_button_url' => null,
        'publication_status' => null,
        'status' => null,
        'image' => 'programs/posters/konstitusi.jpg',
        'hero_image' => null,
        'seo_title' => null,
        'seo_description' => null,
        'og_image' => null,
    ]);

    expect($data['slug'])->toBe('kemerdekaan-kekuasaan-kehakiman')
        ->and($data['short_description'])->toStartWith('Diskusi ini membahas')
        ->and(mb_strlen($data['short_description']))->toBeLessThanOrEqual(220)
        ->and($data['publication_status'])->toBe('draft')
        ->and($data['status'])->toBe('upcoming')
        ->and($data['primary_button_text'])->toBe('Daftar Program')
        ->and($data['primary_button_url'])->toBe('https://example.test/daftar')
        ->and($data['secondary_button_text'])->toBe('Diskusikan Kolaborasi')
        ->and($data['secondary_button_url'])->toBe('/kolaborasi')
        ->and($data['seo_title'])->toBe('Kemerdekaan Kekuasaan Kehakiman')
        ->and($data['seo_description'])->toStartWith('Diskusi ini membahas')
        ->and($data['og_image'])->toBe('programs/posters/konstitusi.jpg');
});

test('program admin resource exposes simplified statuses with archived fallback labels', function () {
    expect(ProgramResource::publicationStatusOptions())->toBe([
        'draft' => 'Draft',
        'reviewed' => 'Reviewed',
        'published' => 'Published',
    ])
        ->and(ProgramResource::statusOptions())->toBe([
            'upcoming' => 'Upcoming',
            'ongoing' => 'Ongoing',
            'completed' => 'Completed',
        ])
        ->and(ProgramResource::publicationStatusLabel('archived'))->toBe('Archived')
        ->and(ProgramResource::statusLabel('archived'))->toBe('Archived')
        ->and(ProgramResource::normalizePublicationStatusForForm('archived'))->toBe('draft')
        ->and(ProgramResource::normalizeStatusForForm('portfolio'))->toBe('completed');
});
