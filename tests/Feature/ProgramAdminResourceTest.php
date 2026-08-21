<?php

use App\Filament\Resources\ProgramResource;
use App\Models\Program;
use Illuminate\Support\Carbon;

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
        ->and($data['publication_status'])->toBe('published')
        ->and($data['status'])->toBe('upcoming')
        ->and($data['primary_button_text'])->toBe('Daftar Program')
        ->and($data['primary_button_url'])->toBe('https://example.test/daftar')
        ->and($data['secondary_button_text'])->toBe('Diskusikan Kolaborasi')
        ->and($data['secondary_button_url'])->toBe('/kolaborasi')
        ->and($data['seo_title'])->toBe('Kemerdekaan Kekuasaan Kehakiman')
        ->and($data['seo_description'])->toStartWith('Diskusi ini membahas')
        ->and($data['og_image'])->toBe('programs/posters/konstitusi.jpg');
});

test('program status is derived automatically from its schedule', function () {
    Carbon::setTestNow('2026-08-21 10:00:00');

    expect(Program::statusFromDates('2026-08-22'))->toBe('upcoming')
        ->and(Program::statusFromDates('2026-08-21'))->toBe('ongoing')
        ->and(Program::statusFromDates('2026-08-20'))->toBe('archived')
        ->and(Program::statusFromDates('2026-08-20', '2026-08-22'))->toBe('ongoing')
        ->and(Program::statusFromDates('2026-08-18', '2026-08-20'))->toBe('archived');
});

test('program persistence ignores a manually supplied status when schedule exists', function () {
    Carbon::setTestNow('2026-08-21 10:00:00');

    $program = Program::query()->create([
        'name' => 'Program Otomatis',
        'slug' => 'program-otomatis',
        'event_date' => '2026-08-25',
        'end_date' => '2026-08-26',
        'status' => 'archived',
    ]);

    expect($program->getRawOriginal('status'))->toBe('upcoming')
        ->and($program->status)->toBe('upcoming');

    Carbon::setTestNow('2026-08-25 10:00:00');
    expect($program->status)->toBe('ongoing');

    Carbon::setTestNow('2026-08-27 10:00:00');
    expect($program->status)->toBe('archived');
});

test('program resource assigns the next sequence automatically', function () {
    Program::query()->create([
        'name' => 'Program Pertama',
        'slug' => 'program-pertama',
        'sort_order' => 7,
    ]);

    Program::query()->create([
        'name' => 'Program Kedua',
        'slug' => 'program-kedua',
        'sort_order' => 12,
    ]);

    expect(ProgramResource::nextSortOrder())->toBe(13);
});

test('program admin resource exposes simplified statuses with archived fallback labels', function () {
    expect(ProgramResource::publicationStatusOptions())->toBe([
        'draft' => 'Draft',
        'reviewed' => 'Reviewed',
        'published' => 'Published',
    ])
        ->and(ProgramResource::statusOptions())->toBe([
            'upcoming' => 'Akan Datang',
            'ongoing' => 'Berlangsung',
            'archived' => 'Diarsipkan',
        ])
        ->and(ProgramResource::publicationStatusLabel('archived'))->toBe('Archived')
        ->and(ProgramResource::statusLabel('archived'))->toBe('Diarsipkan')
        ->and(ProgramResource::statusLabel('completed'))->toBe('Diarsipkan')
        ->and(ProgramResource::normalizePublicationStatusForForm('archived'))->toBe('draft')
        ->and(ProgramResource::normalizeStatusForForm('completed'))->toBe('archived')
        ->and(ProgramResource::normalizeStatusForForm('portfolio'))->toBe('archived');
});
