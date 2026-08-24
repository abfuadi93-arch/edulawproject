<?php

use App\Models\Program;

it('renders the program catalogue as a compact editorial flow', function () {
    $response = $this->get(route('programs.index'));
    $html = $response->getContent();

    $response
        ->assertOk()
        ->assertSee('data-program-filter-bar', false)
        ->assertSee('Belum ada program aktif saat ini.')
        ->assertSee('Lihat Program Arsip')
        ->assertSee('Lihat Opportunities')
        ->assertSee(route('programs.archive'), false)
        ->assertSee(route('opportunities.index'), false)
        ->assertSeeInOrder([
            'Program Aktif (0)',
            'Belum ada program aktif saat ini.',
            'Program Terdahulu',
            'Kolaborasi Program',
        ])
        ->assertDontSee('Untuk Siapa?')
        ->assertDontSee('Ajukan Program');

    $document = new DOMDocument;
    @$document->loadHTML($html);
    $xpath = new DOMXPath($document);

    expect($xpath->query('//*[@data-program-filter-bar]//input[@name="q"]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-program-filter-bar]//select')->length)->toBe(4)
        ->and($xpath->query('//main//aside')->length)->toBe(0);
});

it('renders compact archive cards without a large archive button', function () {
    $program = Program::query()->create([
        'name' => 'Program Arsip Katalog',
        'slug' => 'program-arsip-katalog',
        'status' => 'archived',
        'publication_status' => 'published',
        'event_date' => now()->subMonth()->toDateString(),
    ]);

    $html = $this->get(route('programs.index'))->assertOk()->getContent();
    $document = new DOMDocument;
    @$document->loadHTML($html);
    $xpath = new DOMXPath($document);
    $card = $xpath->query('//*[@data-program-archive-card]')->item(0);

    expect($card)->toBeInstanceOf(DOMElement::class)
        ->and($card->textContent)->toContain($program->name)
        ->and($xpath->query('.//img', $card)->length)->toBeLessThanOrEqual(1)
        ->and($xpath->query('.//a', $card)->length)->toBe(1)
        ->and($card->textContent)->not->toContain('Lihat Arsip');
});

it('uses the shared pagination navigation for the program archive', function () {
    foreach (range(1, 13) as $position) {
        Program::query()->create([
            'name' => "Program Arsip Halaman {$position}",
            'slug' => "program-arsip-halaman-{$position}",
            'publication_status' => 'published',
            'event_date' => now()->subDays($position)->toDateString(),
        ]);
    }

    $response = $this->get(route('programs.archive'))->assertOk();

    expect(substr_count($response->getContent(), 'data-program-archive-card'))->toBe(12);

    $response
        ->assertSee('aria-label="Navigasi halaman arsip program"', false)
        ->assertSee('class="mt-7 flex flex-wrap items-center justify-center gap-2"', false)
        ->assertSee('#program-archive', false)
        ->assertSee('Halaman 1 dari 2');
});
