<?php

use App\Filament\Resources\Multimedia\MultimediaResource;
use App\Filament\Resources\Multimedia\Pages\ListMultimedia;
use App\Filament\Resources\Opportunities\OpportunityResource;
use App\Filament\Resources\Opportunities\Pages\ListOpportunities;
use App\Filament\Resources\ProgramResource;
use App\Filament\Resources\ProgramResource\Pages\ListPrograms;
use App\Filament\Resources\Publications\Pages\ListPublications;
use App\Filament\Resources\Publications\PublicationResource;
use App\Models\Multimedia;
use App\Models\Opportunity;
use App\Models\Program;
use App\Models\Publication;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('primary content admin lists render consistent localized headers with null data', function () {
    $role = Role::findOrCreate('super_admin');
    $user = User::query()->create([
        'name' => 'Super Admin Daftar Konten',
        'email' => 'content-list-admin@example.test',
        'password' => 'secret-password',
        'is_active' => true,
    ]);
    $user->assignRole($role);

    Publication::query()->create([
        'title' => 'Publikasi Tanpa Metadata',
        'slug' => 'publikasi-tanpa-metadata',
        'status' => 'draft',
    ]);
    Program::query()->create([
        'name' => 'Program Tanpa Jadwal',
        'slug' => 'program-tanpa-jadwal',
        'status' => 'upcoming',
        'publication_status' => 'draft',
    ]);
    Opportunity::query()->create([
        'title' => 'Peluang Tanpa Tenggat',
        'slug' => 'peluang-tanpa-tenggat',
        'status' => 'open',
    ]);
    Multimedia::query()->create([
        'title' => 'Multimedia Tanpa Thumbnail',
        'slug' => 'multimedia-tanpa-thumbnail',
        'status' => 'draft',
    ]);

    $expectations = [
        [PublicationResource::getUrl('index'), 'Tambah Publikasi', 'Kelola riset, publikasi, dokumen, dan metadata penerbitan.'],
        [ProgramResource::getUrl('index'), 'Tambah Program', 'Kelola program, jadwal kegiatan, narasumber, dan pendaftaran.'],
        [OpportunityResource::getUrl('index'), 'Tambah Peluang', 'Kelola peluang, tenggat pendaftaran, dan informasi aplikasi.'],
        [MultimediaResource::getUrl('index'), 'Tambah Multimedia', 'Kelola video, reels, shorts, album foto, dan konten multimedia.'],
    ];

    foreach ($expectations as [$url, $button, $description]) {
        $this->actingAs($user)
            ->get($url)
            ->assertOk()
            ->assertSee($button)
            ->assertSee($description)
            ->assertSee('Semua');
    }
});

test('program schedule label supports missing dates and date ranges', function () {
    $program = new Program;

    expect(ProgramResource::scheduleLabel($program))->toBe('Belum dijadwalkan');

    $program->event_date = '2026-08-10 09:00:00';
    $program->end_date = '2026-08-12 16:00:00';

    expect(ProgramResource::scheduleLabel($program))
        ->toBe('10 Agt 2026 – 12 Agt 2026');
});

test('primary content tables support search and status filters', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $role = Role::findOrCreate('super_admin');
    $user = User::query()->create([
        'name' => 'Super Admin Interaksi Tabel',
        'email' => 'content-table-admin@example.test',
        'password' => 'secret-password',
        'is_active' => true,
    ]);
    $user->assignRole($role);

    $records = [
        [
            ListPublications::class,
            Publication::query()->create(['title' => 'Publikasi Jarum Unik', 'slug' => 'publikasi-jarum-unik', 'status' => 'published']),
            Publication::query()->create(['title' => 'Publikasi Pembanding', 'slug' => 'publikasi-pembanding', 'status' => 'draft']),
            'Jarum Unik',
            'published',
        ],
        [
            ListPrograms::class,
            Program::query()->create(['name' => 'Program Jarum Unik', 'slug' => 'program-jarum-unik', 'status' => 'ongoing', 'publication_status' => 'published']),
            Program::query()->create(['name' => 'Program Pembanding', 'slug' => 'program-pembanding', 'status' => 'upcoming', 'publication_status' => 'draft']),
            'Jarum Unik',
            'ongoing',
        ],
        [
            ListOpportunities::class,
            Opportunity::query()->create(['title' => 'Peluang Jarum Unik', 'slug' => 'peluang-jarum-unik', 'status' => 'closed']),
            Opportunity::query()->create(['title' => 'Peluang Pembanding', 'slug' => 'peluang-pembanding', 'status' => 'open']),
            'Jarum Unik',
            'closed',
        ],
        [
            ListMultimedia::class,
            Multimedia::query()->create(['title' => 'Multimedia Jarum Unik', 'slug' => 'multimedia-jarum-unik', 'status' => 'published']),
            Multimedia::query()->create(['title' => 'Multimedia Pembanding', 'slug' => 'multimedia-pembanding', 'status' => 'draft']),
            'Jarum Unik',
            'published',
        ],
    ];

    foreach ($records as [$component, $matchingRecord, $otherRecord, $search, $status]) {
        Livewire::actingAs($user)
            ->test($component)
            ->searchTable($search)
            ->assertCanSeeTableRecords([$matchingRecord])
            ->assertCanNotSeeTableRecords([$otherRecord])
            ->searchTable()
            ->filterTable('status', $status)
            ->assertCanSeeTableRecords([$matchingRecord])
            ->assertCanNotSeeTableRecords([$otherRecord]);
    }
});
