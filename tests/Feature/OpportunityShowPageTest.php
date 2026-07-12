<?php

use App\Models\Opportunity;

test('open opportunity detail page renders public content and action', function () {
    $opportunity = Opportunity::create([
        'title' => 'Fellowship Riset Hukum',
        'slug' => 'fellowship-riset-hukum',
        'type' => 'fellowship',
        'excerpt' => 'Kesempatan riset hukum untuk peneliti muda.',
        'description' => '<p>Program fellowship untuk memperkuat riset hukum publik.</p>',
        'status' => 'open',
        'deadline' => now()->addDays(10)->toDateString(),
        'format' => 'hybrid',
        'location' => 'Jakarta',
        'application_link' => 'https://example.test/daftar',
        'eligibility' => ['Mahasiswa atau peneliti hukum'],
        'benefits' => ['Mentoring riset'],
    ]);

    $this->get(route('opportunities.show', $opportunity->slug))
        ->assertOk()
        ->assertSee('Fellowship Riset Hukum')
        ->assertSee('Program fellowship untuk memperkuat riset hukum publik')
        ->assertSee('Mahasiswa atau peneliti hukum')
        ->assertSee('Mentoring riset')
        ->assertSee('https://example.test/daftar', false)
        ->assertSee('Bagikan');
});

test('non open opportunity detail page is not publicly visible', function () {
    $opportunity = Opportunity::create([
        'title' => 'Peluang Ditutup',
        'slug' => 'peluang-ditutup-detail',
        'type' => 'open_collaboration',
        'excerpt' => 'Peluang yang sudah ditutup.',
        'description' => 'Peluang yang sudah ditutup.',
        'status' => 'closed',
    ]);

    $this->get(route('opportunities.show', $opportunity->slug))
        ->assertNotFound();
});
