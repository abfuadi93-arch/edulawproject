<?php

use App\Models\Opportunity;

test('public opportunities page only renders open opportunities', function () {
    Opportunity::create([
        'title' => 'Peluang Dibuka',
        'slug' => 'peluang-dibuka',
        'type' => 'open_collaboration',
        'excerpt' => 'Peluang yang masih terbuka.',
        'description' => 'Peluang yang masih terbuka.',
        'status' => 'open',
    ]);

    Opportunity::create([
        'title' => 'Peluang Ditutup',
        'slug' => 'peluang-ditutup',
        'type' => 'open_collaboration',
        'excerpt' => 'Peluang yang sudah ditutup.',
        'description' => 'Peluang yang sudah ditutup.',
        'status' => 'closed',
    ]);

    Opportunity::create([
        'title' => 'Peluang Arsip',
        'slug' => 'peluang-arsip',
        'type' => 'open_collaboration',
        'excerpt' => 'Peluang yang sudah diarsipkan.',
        'description' => 'Peluang yang sudah diarsipkan.',
        'status' => 'archived',
    ]);

    $this->get(route('opportunities.index'))
        ->assertOk()
        ->assertSee('Peluang Dibuka')
        ->assertDontSee('Peluang Ditutup')
        ->assertDontSee('Peluang Arsip');
});
