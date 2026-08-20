<?php

use App\Models\Opportunity;

test('active opportunity has a public detail page', function () {
    $opportunity = Opportunity::query()->create([
        'title' => 'Fellowship Riset Hukum',
        'slug' => 'fellowship-riset-hukum',
        'type' => 'fellowship',
        'excerpt' => 'Kesempatan riset hukum untuk peneliti muda.',
        'description' => 'Program fellowship untuk memperkuat riset hukum publik.',
        'eligibility' => ['Peneliti muda', 'Memiliki minat pada hukum publik'],
        'benefits' => ['Mentoring riset', 'Jejaring profesional'],
        'format' => 'hybrid',
        'location' => 'Jakarta',
        'status' => 'open',
        'deadline' => now()->addDays(10)->toDateString(),
        'application_link' => 'https://example.test/daftar',
    ]);

    $this->get(route('opportunities.show', $opportunity->slug))
        ->assertOk()
        ->assertViewIs('opportunities.show')
        ->assertViewHas('opportunity', fn (Opportunity $item) => $item->is($opportunity))
        ->assertSee($opportunity->title)
        ->assertSee('Program fellowship untuk memperkuat riset hukum publik.')
        ->assertSee('Peneliti muda')
        ->assertSee('Mentoring riset')
        ->assertSee('Buka Pendaftaran')
        ->assertSee('https://example.test/daftar', false);
});

test('inactive or invalid opportunities do not have public detail pages', function (array $attributes) {
    $opportunity = Opportunity::query()->create([
        'title' => 'Peluang Tidak Publik',
        'slug' => 'peluang-tidak-publik-'.uniqid(),
        'status' => 'open',
        'deadline' => now()->addDays(10)->toDateString(),
        'application_link' => 'https://example.test/daftar',
        ...$attributes,
    ]);

    $this->get('/opportunities/'.$opportunity->slug)->assertNotFound();
})->with([
    'closed' => [['status' => 'closed']],
    'expired' => [['deadline' => now()->subDay()->toDateString()]],
    'without external link' => [['application_link' => '/kontak']],
]);

test('opportunity index links to the detail page before the external application page', function () {
    $opportunity = Opportunity::query()->create([
        'title' => 'Kompetisi Peradilan Semu',
        'slug' => 'kompetisi-peradilan-semu',
        'status' => 'open',
        'deadline' => now()->addDays(10)->toDateString(),
        'application_link' => 'https://example.test/daftar',
    ]);

    $this->get(route('opportunities.index'))
        ->assertOk()
        ->assertSee(route('opportunities.show', $opportunity->slug), false)
        ->assertSee('Lihat Detail')
        ->assertDontSee($opportunity->application_link, false);
});

test('legacy opportunity detail path is not publicly available', function () {
    $this->get('/peluang/peluang-lama')->assertNotFound();
});
