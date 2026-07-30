<?php

use App\Models\Program;
use App\Models\ProgramCategory;

test('program detail page renders compact editorial layout without repeated technical fields', function () {
    $category = ProgramCategory::query()->create([
        'name' => 'Kelas Publik',
        'slug' => 'kelas-publik',
        'is_active' => true,
    ]);

    $program = Program::query()->create([
        'program_category_id' => $category->id,
        'type' => 'training',
        'name' => 'Kelas Hukum dan Kebijakan Publik',
        'slug' => 'kelas-hukum-kebijakan-publik',
        'subtitle' => 'Membaca regulasi secara kontekstual',
        'duration' => '2 jam',
        'short_description' => 'Program ringkas untuk memahami kebijakan publik.',
        'description' => 'Peserta mempelajari cara membaca regulasi, putusan, dan data publik secara kritis.',
        'learning_points' => ['Membaca regulasi', 'Menyusun argumen hukum'],
        'orientation' => 'Literasi hukum publik.',
        'method' => 'Diskusi terpandu dan studi kasus.',
        'output' => 'Rangkuman analisis kebijakan.',
        'speakers' => [
            [
                'name' => 'Aulia Rahman',
                'title' => 'Peneliti Hukum',
                'bio' => 'Berfokus pada hukum tata negara dan kebijakan publik. Ia aktif menulis kajian, mengajar, dan mendampingi forum pembelajaran publik mengenai konstitusi, demokrasi, peradilan, serta pengalaman panjang dalam pendidikan publik hukum.',
            ],
        ],
        'moderator_name' => 'Nabila Rahma',
        'moderator_affiliation' => 'Edulaw Project',
        'format' => 'hybrid',
        'level' => 'beginner',
        'audience' => 'Mahasiswa dan komunitas',
        'event_date' => now()->addDays(7)->toDateString(),
        'registration_link' => 'https://example.test/daftar',
        'location' => 'Jakarta',
        'price_type' => 'Gratis',
        'certificate_available' => true,
        'status' => 'upcoming',
        'publication_status' => 'published',
    ]);

    Program::query()->create([
        'program_category_id' => $category->id,
        'name' => 'Diskusi Kebijakan Publik',
        'slug' => 'diskusi-kebijakan-publik',
        'short_description' => 'Program terkait.',
        'format' => 'online',
        'event_date' => now()->addDays(14)->toDateString(),
        'status' => 'upcoming',
        'publication_status' => 'published',
    ]);

    $html = $this->get(route('programs.show', $program->slug))
        ->assertOk()
        ->assertSee('Kelas Hukum dan Kebijakan Publik')
        ->assertSee('Membaca regulasi secara kontekstual')
        ->assertSee('Tentang Program')
        ->assertSee('Yang Dipelajari')
        ->assertSee('Narasumber')
        ->assertSee('pengalaman panjang dalam pendidikan publik hukum')
        ->assertSee('Rancangan Program')
        ->assertSee('Program Terkait')
        ->assertSee('Bagikan Program')
        ->assertSee('WhatsApp')
        ->assertSee('Telegram')
        ->assertSee('X/Twitter')
        ->assertSee('Facebook')
        ->assertSee('LinkedIn')
        ->assertSee('Instagram')
        ->assertSee('Salin Link')
        ->assertSee('Bangun Literasi Hukum Bersama Edulaw Project')
        ->assertSeeInOrder(['Poster Kegiatan', 'Informasi Program'])
        ->getContent();

    expect(preg_match_all('/>\s*Durasi\s*</', $html))->toBe(1)
        ->and(preg_match_all('/>\s*Level\s*</', $html))->toBe(1);

    expect($html)
        ->toContain('property="og:title" content="Kelas Hukum dan Kebijakan Publik | Edulaw Project"')
        ->toContain('property="og:type" content="article"')
        ->toContain('property="og:url" content="'.route('programs.show', $program->slug).'"')
        ->toContain('name="twitter:card" content="summary_large_image"');
});
