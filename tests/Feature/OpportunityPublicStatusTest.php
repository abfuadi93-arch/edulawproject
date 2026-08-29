<?php

use App\Models\Opportunity;

test('public opportunities page only renders active opportunities with external links', function () {
    Opportunity::create([
        'title' => 'Peluang Dibuka',
        'slug' => 'peluang-dibuka',
        'type' => 'open_collaboration',
        'excerpt' => 'Peluang yang masih terbuka.',
        'description' => 'Peluang yang masih terbuka.',
        'status' => 'open',
        'deadline' => now()->addWeek()->toDateString(),
        'application_link' => 'https://example.test/peluang',
    ]);

    Opportunity::create([
        'title' => 'Peluang Ditutup',
        'slug' => 'peluang-ditutup',
        'type' => 'open_collaboration',
        'excerpt' => 'Peluang yang sudah ditutup.',
        'description' => 'Peluang yang sudah ditutup.',
        'status' => 'closed',
        'deadline' => now()->addWeek()->toDateString(),
        'application_link' => 'https://example.test/ditutup',
    ]);

    Opportunity::create([
        'title' => 'Peluang Arsip',
        'slug' => 'peluang-arsip',
        'type' => 'open_collaboration',
        'excerpt' => 'Peluang yang sudah diarsipkan.',
        'description' => 'Peluang yang sudah diarsipkan.',
        'status' => 'archived',
        'deadline' => now()->addWeek()->toDateString(),
        'application_link' => 'https://example.test/arsip',
    ]);

    Opportunity::create([
        'title' => 'Peluang Kedaluwarsa',
        'slug' => 'peluang-kedaluwarsa-index',
        'type' => 'open_collaboration',
        'status' => 'open',
        'deadline' => now()->subDay()->toDateString(),
        'application_link' => 'https://example.test/kedaluwarsa',
    ]);

    Opportunity::create([
        'title' => 'Peluang Tanpa URL',
        'slug' => 'peluang-tanpa-url-index',
        'type' => 'open_collaboration',
        'status' => 'open',
        'deadline' => now()->addWeek()->toDateString(),
    ]);

    $this->get(route('opportunities.index'))
        ->assertOk()
        ->assertSee('Peluang Dibuka')
        ->assertSee('https://example.test/peluang', false)
        ->assertSee('target="_blank"', false)
        ->assertSee('rel="noopener noreferrer"', false)
        ->assertSee('Lihat Informasi Resmi')
        ->assertDontSee(route('opportunities.show', 'peluang-dibuka'), false)
        ->assertDontSee('Peluang Ditutup')
        ->assertDontSee('Peluang Arsip')
        ->assertDontSee('Peluang Kedaluwarsa')
        ->assertDontSee('Peluang Tanpa URL');
});
