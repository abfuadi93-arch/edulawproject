<?php

use App\Filament\Resources\ProgramResource;
use App\Filament\Resources\ProgramResource\Pages\CreateProgram;
use App\Filament\Resources\ProgramResource\Pages\EditProgram;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('program admin validates and saves ticket price and the actual end date', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $user = User::query()->create([
        'name' => 'Admin Program',
        'email' => 'program-admin@example.test',
        'password' => 'secret-password',
        'is_active' => true,
    ]);
    $user->assignRole(Role::findOrCreate('super_admin'));
    $category = ProgramCategory::query()->where('slug', 'pelatihan')->firstOrFail();

    Livewire::actingAs($user)
        ->test(CreateProgram::class)
        ->fillForm([
            'name' => 'Kelas Publik dengan Tiket',
            'slug' => 'kelas-publik-dengan-tiket',
            'program_category_id' => $category->id,
            'short_description' => 'Kelas edukasi publik dengan materi hukum yang praktis.',
            'description' => '<p>Kelas edukasi publik.</p>',
            'speakers' => [['name' => 'Narasumber Terverifikasi']],
            'format' => 'offline',
            'event_date' => '2026-09-05',
            'end_date' => '2026-09-04',
            'ticket_price' => -1,
        ])
        ->call('create')
        ->assertHasFormErrors(['ticket_price', 'end_date'])
        ->fillForm([
            'end_date' => '2026-09-05',
            'ticket_price' => '75000.50',
            'event_time' => '19:00',
            'end_time' => '18:00',
        ])
        ->call('create')
        ->assertHasFormErrors(['end_time'])
        ->fillForm([
            'end_time' => '21:00',
            'event_timezone' => 'Asia/Makassar',
            'ticket_currency' => 'IDR',
            'ticket_availability' => 'PreOrder',
            'registration_opens_at' => '2026-09-01 10:00:00',
            'venue_address' => 'Jalan Pendidikan 1',
            'venue_city' => 'Makassar',
            'venue_country' => 'id',
            'organizer_name' => 'Komunitas Hukum',
            'organizer_url' => 'https://example.test/komunitas',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $program = Program::query()->where('slug', 'kelas-publik-dengan-tiket')->firstOrFail();

    expect($program->ticket_price)->toBe('75000.50')
        ->and($program->end_date->toDateString())->toBe('2026-09-05')
        ->and($program->eventDateValue())->toBe('2026-09-05T19:00:00+08:00')
        ->and($program->eventDateValue(true))->toBe('2026-09-05T21:00:00+08:00')
        ->and($program->registration_opens_date->toIso8601String())->toBe('2026-09-01T10:00:00+08:00')
        ->and($program->venue_country)->toBe('ID');

    $this->actingAs($user)->get(ProgramResource::getUrl('edit', ['record' => $program]))
        ->assertOk()
        ->assertSeeInOrder(['Informasi Program', 'Jadwal &amp; Pelaksanaan', 'Deskripsi Program', 'Penyelenggara &amp; Narasumber', 'Media', 'Pendaftaran', 'Tampilan Publik', 'Pengaturan Lanjutan — opsional'], false);

    Livewire::actingAs($user)->test(EditProgram::class, ['record' => $program->getRouteKey()])
        ->assertFormSet(['event_time' => '19:00', 'end_time' => '21:00', 'event_timezone' => 'Asia/Makassar'])
        ->call('save')->assertHasNoFormErrors();
    expect($program->fresh()->eventDateValue())->toBe('2026-09-05T19:00:00+08:00');
});

test('program create form presents a simplified primary flow', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $user = User::query()->create([
        'name' => 'Admin Form Program Ringkas',
        'email' => 'program-form-ringkas@example.test',
        'password' => 'secret-password',
        'is_active' => true,
    ]);
    $user->assignRole(Role::findOrCreate('super_admin'));

    $this->actingAs($user)
        ->get(ProgramResource::getUrl('create'))
        ->assertOk()
        ->assertSeeInOrder(['Informasi Program', 'Jadwal &amp; Pelaksanaan', 'Deskripsi Program', 'Penyelenggara &amp; Narasumber', 'Media', 'Pendaftaran', 'Tampilan Publik', 'Pengaturan Lanjutan — opsional'], false)
        ->assertSee('Mulai dari template')
        ->assertSee('Status Publikasi')
        ->assertSee('Memerlukan pendaftaran?')
        ->assertSee('Simpan Draft')
        ->assertSee('Publikasikan')
        ->assertDontSee('Simpan &amp; Buat Lagi', false);
});

test('program admin accepts an upcoming virtual internship without announced dates', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $user = User::query()->create([
        'name' => 'Admin Virtual Internship',
        'email' => 'virtual-internship-admin@example.test',
        'password' => 'secret-password',
        'is_active' => true,
    ]);
    $user->assignRole(Role::findOrCreate('super_admin'));
    $category = ProgramCategory::query()->where('slug', 'internship')->firstOrFail();
    $summary = 'Program magang virtual Edulaw Project sebagai ruang belajar, berkontribusi, dan berkembang melalui riset, penulisan hukum, serta produksi konten edukatif.';
    $learningPoints = [
        'Melatih kemampuan riset hukum dasar dan penelusuran sumber hukum yang relevan.',
        'Mengembangkan keterampilan legal writing, penulisan artikel, dan penyusunan konten edukasi hukum.',
        'Memahami proses kerja kolaboratif dalam pengelolaan platform edukasi hukum digital.',
        'Membangun portofolio akademik dan profesional melalui kontribusi pada program Edulaw Project.',
    ];

    Livewire::actingAs($user)
        ->test(CreateProgram::class)
        ->fillForm([
            'name' => 'Virtual Internship Edulaw Project',
            'slug' => 'virtual-internship-edulaw-project',
            'program_category_id' => $category->id,
            'level' => ['Beginner', 'Intermediate'],
            'audience' => 'Mahasiswa hukum, fresh graduate, peneliti muda, kontributor muda',
            'short_description' => $summary,
            'learning_points' => $learningPoints,
            'speakers' => [[
                'type' => 'PerformingGroup',
                'name' => 'Tim Edulaw Project',
                'title' => 'Edulaw Project',
                'bio' => 'Fasilitator program virtual internship',
            ]],
            'format' => 'online',
            'location' => 'Online',
            'registration_link' => null,
            'price_type' => 'Gratis',
            'certificate_available' => true,
            'status' => 'upcoming',
            'event_date' => null,
            'end_date' => null,
            'featured' => true,
            'seo_title' => 'Virtual Internship Edulaw Project',
            'seo_description' => 'Program magang virtual Edulaw Project untuk belajar riset hukum, legal writing, produksi konten edukatif, dan kerja kolaboratif.',
        ])
        ->call('createAnother')
        ->assertHasNoFormErrors()
        ->assertNoRedirect()
        ->assertFormSet(['name' => null, 'status' => 'upcoming']);

    $program = Program::query()->where('slug', 'virtual-internship-edulaw-project')->firstOrFail();

    expect($program->program_category_id)->toBe($category->id)
        ->and($program->level)->toBe('Beginner, Intermediate')
        ->and($program->audience)->toBe('Mahasiswa hukum, fresh graduate, peneliti muda, kontributor muda')
        ->and($program->short_description)->toBe($summary)
        ->and($program->learning_points)->toBe($learningPoints)
        ->and($program->speakers[0])->toMatchArray([
            'type' => 'PerformingGroup',
            'name' => 'Tim Edulaw Project',
            'title' => 'Edulaw Project',
            'bio' => 'Fasilitator program virtual internship',
        ])
        ->and($program->format)->toBe('online')
        ->and($program->location)->toBe('Online')
        ->and($program->price_type)->toBe('Gratis')
        ->and($program->certificate_available)->toBeTrue()
        ->and($program->featured)->toBeTrue()
        ->and($program->event_date)->toBeNull()
        ->and($program->end_date)->toBeNull()
        ->and($program->status)->toBe('upcoming')
        ->and($program->seo_title)->toBe('Virtual Internship Edulaw Project');
});

test('program admin resource derives short description seo and cta fallback', function () {
    $description = '<p>Diskusi ini membahas kemerdekaan kekuasaan kehakiman dalam negara hukum demokratis serta tantangan independensi peradilan dalam praktik ketatanegaraan Indonesia.</p>';

    $data = ProgramResource::prepareFormDataForPersistence([
        'name' => 'Kemerdekaan Kekuasaan Kehakiman',
        'slug' => '',
        'short_description' => null,
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
        ->and($data['primary_button_text'])->toBeNull()
        ->and($data['primary_button_url'])->toBeNull()
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

test('program draft stays private and automatic CTA respects lifecycle', function () {
    $data = ProgramResource::prepareFormDataForPersistence([
        'name' => 'Draft Baru', 'publication_status' => 'draft',
        'requires_registration' => true, 'price_type' => 'Gratis',
    ]);
    expect($data['publication_status'])->toBe('draft')->and($data['ticket_price'])->toBe(0);
    $program = new Program([
        'publication_status' => 'published', 'event_date' => now()->addDays(2)->toDateString(),
        'requires_registration' => true, 'registration_status' => 'soon',
        'registration_link' => 'https://example.test/register',
    ]);
    expect($program->program_cta)->toBe(['label' => 'Segera Dibuka', 'url' => null]);
    $program->registration_status = 'open';
    expect($program->program_cta)->toBe(['label' => 'Daftar Program', 'url' => 'https://example.test/register']);
    $program->registration_status = 'closed';
    expect($program->program_cta)->toBeNull();
    $program->event_date = now()->subDays(2)->toDateString();
    $program->primary_button_text = 'Join Zoom Meeting';
    $program->primary_button_url = 'https://example.test/old-meeting';
    expect($program->program_cta)->toBeNull();
    $program->youtube_url = 'https://youtube.com/watch?v=example';
    expect($program->program_cta)->toBe(['label' => 'Tonton Dokumentasi', 'url' => $program->youtube_url]);
});

test('program template and conditional registration keep the main form concise', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $user = User::query()->create(['name' => 'Template Admin', 'email' => 'template@example.test', 'password' => 'password', 'is_active' => true]);
    $user->assignRole(Role::findOrCreate('super_admin'));
    $category = ProgramCategory::query()->where('slug', 'diskusi')->firstOrFail();
    Livewire::actingAs($user)->test(CreateProgram::class)
        ->set('data.program_template', 'diksi')
        ->assertFormSet(['program_category_id' => $category->id, 'format' => 'online', 'platform' => 'Zoom / YouTube', 'price_type' => 'Gratis'])
        ->assertFormFieldIsHidden('location')
        ->assertFormFieldIsVisible('platform')
        ->assertFormFieldIsHidden('registration_link')
        ->set('data.requires_registration', true)
        ->assertFormFieldIsVisible('registration_link')
        ->assertFormFieldIsHidden('ticket_price')
        ->set('data.price_type', 'Berbayar')
        ->assertFormFieldIsVisible('ticket_price')
        ->set('data.format', 'hybrid')
        ->assertFormFieldIsVisible('location')
        ->assertFormFieldIsVisible('platform')
        ->set('data.format', 'offline')
        ->assertFormFieldIsHidden('platform');
});

test('draft and publish actions enforce separate publication requirements', function () {
    Storage::fake('public');
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $user = User::query()->create(['name' => 'Publish Admin', 'email' => 'publish-admin@example.test', 'password' => 'password', 'is_active' => true]);
    $user->assignRole(Role::findOrCreate('super_admin'));
    $category = ProgramCategory::query()->where('slug', 'diskusi')->firstOrFail();
    $form = ['name' => 'Draft Sederhana', 'slug' => 'draft-sederhana', 'tags' => ['Konstitusi', 'Legal Writing'], 'short_description' => 'Ringkasan program.', 'program_category_id' => $category->id, 'format' => 'online'];
    Livewire::actingAs($user)->test(CreateProgram::class)->fillForm($form)
        ->call('createWithStatus', 'draft')->assertHasNoFormErrors();
    $draft = Program::query()->where('slug', 'draft-sederhana')->firstOrFail();
    expect($draft->tags)->toBe(['Konstitusi', 'Legal Writing'])
        ->and($draft->publication_status)->toBe('draft')
        ->and(Program::query()->visible()->whereKey($draft)->exists())->toBeFalse();
    Livewire::actingAs($user)->test(CreateProgram::class)
        ->fillForm([...$form, 'name' => 'Program Terbit', 'slug' => 'program-terbit'])
        ->call('createWithStatus', 'published')
        ->assertHasFormErrors(['event_date' => 'required', 'image' => 'required'])
        ->fillForm(['event_date' => now()->addWeek()->toDateString(), 'image' => UploadedFile::fake()->image('poster.jpg')])
        ->call('createWithStatus', 'published')->assertHasNoFormErrors();
    expect(Program::query()->visible()->where('slug', 'program-terbit')->exists())->toBeTrue();
});

test('manual archive removes future programs from active counts and suppresses meeting CTA', function () {
    $program = Program::query()->create([
        'name' => 'Arsip Manual', 'slug' => 'arsip-manual', 'publication_status' => 'published',
        'event_date' => now()->addWeek()->toDateString(), 'is_manually_archived' => true,
        'primary_button_text' => 'Join Zoom Meeting', 'primary_button_url' => 'https://example.test/meeting',
    ]);
    expect($program->fresh()->status)->toBe('archived')
        ->and($program->fresh()->program_cta)->toBeNull()
        ->and(Program::query()->active()->whereKey($program)->exists())->toBeFalse()
        ->and(Program::query()->archived()->whereKey($program)->exists())->toBeTrue();
    $program->update(['is_manually_archived' => false]);
    expect($program->fresh()->status)->toBe('upcoming');
});

test('program category choices contain only the four simplified categories', function () {
    expect(ProgramCategory::query()->where('is_active', true)->orderBy('sort_order')->pluck('name')->all())
        ->toBe(['Diskusi', 'Pelatihan', 'Internship', 'Workshop/Webinar']);
});
