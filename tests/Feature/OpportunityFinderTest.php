<?php

use App\Models\Opportunity;

function createFinderOpportunity(array $attributes = []): Opportunity
{
    static $sequence = 0;
    $sequence++;

    return Opportunity::query()->create([
        'title' => 'Opportunity Finder '.$sequence,
        'slug' => 'opportunity-finder-'.$sequence.'-'.uniqid(),
        'type' => 'competition',
        'excerpt' => 'Peluang pengembangan hukum untuk komunitas.',
        'deadline' => today()->addDays(20)->toDateString(),
        'application_link' => 'https://example.test/opportunity-'.$sequence,
        'format' => 'online',
        'location' => 'Jakarta',
        'status' => 'open',
        'featured' => false,
        ...$attributes,
    ]);
}

test('finder separates featured opportunity from the paginated results', function () {
    $featured = createFinderOpportunity([
        'title' => 'Pilihan Utama Edulaw',
        'featured' => true,
        'deadline' => today()->addDays(5)->toDateString(),
    ]);
    $regular = createFinderOpportunity(['title' => 'Kompetisi Reguler Edulaw']);

    $response = $this->get(route('opportunities.index'))->assertOk();

    expect(substr_count($response->getContent(), 'data-featured-opportunity'))->toBe(1)
        ->and(substr_count($response->getContent(), 'data-opportunity-card'))->toBe(1);

    $response
        ->assertViewHas('featuredOpportunity', fn (?Opportunity $item): bool => $item?->is($featured) === true)
        ->assertViewHas('opportunities', fn ($items): bool => $items->total() === 1 && $items->first()->is($regular))
        ->assertSee($featured->application_link, false)
        ->assertSee($regular->application_link, false);
});

test('finder searches and filters by type format location and deadline', function () {
    $matching = createFinderOpportunity([
        'title' => 'Beasiswa Riset Konstitusi',
        'type' => 'scholarship',
        'format' => 'Program hybrid dengan sesi online dan offline',
        'location' => 'Bandung',
        'deadline' => today()->addDays(6)->toDateString(),
    ]);
    $other = createFinderOpportunity([
        'title' => 'Kompetisi Peradilan Nasional',
        'type' => 'competition',
        'format' => 'offline',
        'location' => 'Surabaya',
        'deadline' => today()->addDays(25)->toDateString(),
    ]);

    $this->get(route('opportunities.index', [
        'q' => 'Konstitusi',
        'type' => 'scholarship',
        'format' => 'hybrid',
        'location' => 'Bandung',
        'deadline' => '7_days',
    ]))
        ->assertOk()
        ->assertSee($matching->title)
        ->assertDontSee($other->title)
        ->assertSeeText('1 kesempatan ditemukan');
});

test('finder can show closed opportunities without exposing archived or expired open records by default', function () {
    $open = createFinderOpportunity(['title' => 'Masih Aktif']);
    $closed = createFinderOpportunity(['title' => 'Sudah Selesai', 'status' => 'closed']);
    $expired = createFinderOpportunity([
        'title' => 'Status Open Tetapi Kedaluwarsa',
        'deadline' => today()->subDay()->toDateString(),
    ]);
    $archived = createFinderOpportunity(['title' => 'Peluang Arsip', 'status' => 'archived']);

    $this->get(route('opportunities.index'))
        ->assertOk()
        ->assertSee($open->title)
        ->assertDontSee($closed->title)
        ->assertDontSee($expired->title)
        ->assertDontSee($archived->title);

    $this->get(route('opportunities.index', ['status' => 'closed']))
        ->assertOk()
        ->assertSee($closed->title)
        ->assertSee('Peluang yang Sudah Ditutup')
        ->assertDontSee($open->title)
        ->assertDontSee($expired->title)
        ->assertDontSee($archived->title);
});

test('finder sorts deadlines in both directions and supports latest ordering', function () {
    $near = createFinderOpportunity([
        'title' => 'Deadline Paling Dekat',
        'deadline' => today()->addDays(3)->toDateString(),
        'created_at' => today()->subDays(3),
    ]);
    $far = createFinderOpportunity([
        'title' => 'Deadline Paling Jauh',
        'deadline' => today()->addDays(30)->toDateString(),
        'created_at' => today(),
    ]);
    $flexible = createFinderOpportunity([
        'title' => 'Deadline Fleksibel',
        'deadline' => null,
        'created_at' => today()->subDay(),
    ]);

    $this->get(route('opportunities.index'))
        ->assertSeeInOrder([$near->title, $far->title, $flexible->title]);

    $this->get(route('opportunities.index', ['sort' => 'deadline_desc']))
        ->assertSeeInOrder([$far->title, $near->title, $flexible->title]);

    $this->get(route('opportunities.index', ['sort' => 'latest']))
        ->assertSeeInOrder([$far->title, $flexible->title, $near->title]);
});

test('finder paginates nine results and preserves query parameters', function () {
    collect(range(1, 11))->each(fn (int $position) => createFinderOpportunity([
        'title' => 'Kompetisi Halaman '.str_pad((string) $position, 2, '0', STR_PAD_LEFT),
        'deadline' => today()->addDays($position)->toDateString(),
    ]));

    $response = $this->get(route('opportunities.index', ['q' => 'Kompetisi', 'sort' => 'deadline']));

    $response
        ->assertOk()
        ->assertViewHas('opportunities', fn ($items): bool => $items->perPage() === 9 && $items->count() === 9 && $items->total() === 11)
        ->assertSee('q=Kompetisi', false)
        ->assertSee('sort=deadline', false)
        ->assertSee('Menampilkan 1–9 dari 11 peluang');
});

test('finder statistics are calculated from public database records', function () {
    createFinderOpportunity(['title' => 'Peluang Terbuka', 'deadline' => today()->addDays(4)->toDateString()]);
    createFinderOpportunity(['title' => 'Peluang Ditutup', 'status' => 'closed']);
    createFinderOpportunity(['title' => 'Tanpa Link', 'application_link' => null]);

    $this->get(route('opportunities.index'))
        ->assertOk()
        ->assertViewHas('statistics', fn (array $statistics): bool => $statistics['total'] === 2
            && $statistics['open'] === 1
            && $statistics['nearest_deadline'] === today()->addDays(4)->locale('id')->translatedFormat('d F'));
});
