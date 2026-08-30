<?php

use App\Models\Program;
use App\Support\StructuredData;
use Illuminate\Support\Carbon;

test('complete event details are consistent between public content and json ld', function () {
    Carbon::setTestNow('2027-07-01');
    $program = Program::query()->create([
        'name' => 'Kelas Hukum Internasional Edulaw',
        'short_title' => 'Kelas Hukum',
        'slug' => 'kelas-hukum-lengkap',
        'description' => 'Membahas akses terhadap keadilan.',
        'format' => 'hybrid',
        'event_date' => '2027-07-21',
        'end_date' => '2027-07-21',
        'event_time' => '19:00',
        'end_time' => '23:00',
        'event_timezone' => 'America/New_York',
        'event_status' => 'EventScheduled',
        'location' => 'Aula Diskusi',
        'venue_address' => '100 Main Street',
        'venue_city' => 'New York',
        'venue_region' => 'NY',
        'venue_postal_code' => '10001',
        'venue_country' => 'US',
        'online_url' => 'https://example.test/siaran',
        'registration_link' => 'https://example.test/tiket',
        'ticket_price' => '30.00',
        'ticket_currency' => 'USD',
        'ticket_availability' => 'InStock',
        'registration_opens_at' => '2027-06-01 12:00:00',
        'speakers' => [['name' => 'Tim Kajian Hukum', 'type' => 'PerformingGroup']],
        'organizer_name' => 'Forum Kajian Hukum',
        'organizer_url' => 'https://example.test/forum',
        'organizer_type' => 'Organization',
        'image' => 'https://example.test/poster.jpg',
        'hero_image' => 'https://example.test/hero.jpg',
        'gallery_images' => ['https://example.test/square.jpg', 'https://example.test/landscape.jpg'],
        'publication_status' => 'published',
    ])->fresh();

    $html = $this->get(route('programs.show', $program->slug))->assertOk()
        ->assertSee('Kelas Hukum Internasional Edulaw')
        ->assertSee('19:00 America/New_York')->assertSee('23:00 America/New_York')
        ->assertSee('100 Main Street, New York, NY, 10001, US')
        ->assertSee('USD 30,00')->assertSee('Pendaftaran dibuka')
        ->assertSee('01 Juni 2027, 12:00')->assertSee('Galeri Acara')
        ->assertSee('href="https://example.test/forum"', false)
        ->assertSee('href="https://example.test/siaran"', false)->getContent();

    preg_match_all('~<script type="application/ld\+json">(.*?)</script>~s', $html, $matches);
    $event = collect($matches[1])->map(fn ($json) => json_decode($json, true, 512, JSON_THROW_ON_ERROR))
        ->firstWhere('@type', 'Event');

    expect($event['name'])->toBe('Kelas Hukum Internasional Edulaw')
        ->and($event['startDate'])->toBe('2027-07-21T19:00:00-04:00')
        ->and($event['endDate'])->toBe('2027-07-21T23:00:00-04:00')
        ->and($event['offers'])->toMatchArray([
            'price' => '30.00', 'priceCurrency' => 'USD',
            'availability' => 'https://schema.org/InStock',
            'validFrom' => '2027-06-01T12:00:00-04:00',
        ])
        ->and($event['location'][0]['url'])->toBe('https://example.test/siaran')
        ->and($event['location'][1]['address'])->toMatchArray([
            '@type' => 'PostalAddress', 'streetAddress' => '100 Main Street',
            'addressLocality' => 'New York', 'addressRegion' => 'NY', 'postalCode' => '10001', 'addressCountry' => 'US',
        ])
        ->and($event['performer'][0]['@type'])->toBe('PerformingGroup')
        ->and($event['organizer']['name'])->toBe('Forum Kajian Hukum')
        ->and($event['image'])->toHaveCount(4);

    foreach ($event['image'] as $url) {
        expect($html)->toContain('src="'.$url.'"');
    }
});

test('legacy date only events do not acquire invented times addresses or organizer websites', function () {
    $program = new Program([
        'slug' => 'legacy', 'event_date' => '2027-07-21', 'end_date' => '2027-07-21',
        'location' => 'Jakarta', 'format' => 'offline', 'organizer_name' => 'Komunitas Independen',
    ]);
    $event = StructuredData::event($program);

    expect($event['startDate'])->toBe('2027-07-21')
        ->and($event['endDate'])->toBe('2027-07-21')
        ->and($event['location']['address'])->toBe('Jakarta')
        ->and($event['organizer'])->not->toHaveKey('url');

    $program->event_time = '00:00';
    expect(StructuredData::event($program)['startDate'])->toBe('2027-07-21T00:00:00+07:00');
});

test('an online event uses its public access url rather than its registration url', function () {
    $program = new Program([
        'slug' => 'online', 'event_date' => '2027-07-21', 'format' => 'online',
        'registration_link' => 'https://example.test/register',
    ]);
    expect(StructuredData::event($program))->toBeNull();

    $program->online_url = 'https://example.test/watch';
    expect(StructuredData::event($program)['location'])->toBe([
        '@type' => 'VirtualLocation', 'url' => 'https://example.test/watch',
    ]);
});

test('unavailable registration is clearly labelled without an active register button', function ($attributes, $label) {
    Carbon::setTestNow('2027-07-01 12:00:00');
    $program = Program::query()->create(array_merge([
        'name' => 'Status Pendaftaran Acara', 'slug' => 'status-pendaftaran-acara',
        'format' => 'offline', 'location' => 'Jakarta',
        'event_date' => '2027-07-21', 'end_date' => '2027-07-21',
        'registration_link' => 'https://example.test/register', 'price_type' => 'Gratis',
        'primary_button_url' => 'https://example.test/register', 'primary_button_text' => 'Daftar Program',
        'publication_status' => 'published',
    ], $attributes));

    $this->get(route('programs.show', $program->slug))->assertOk()->assertSee($label)->assertDontSee('Daftar Program');
    expect($program->registration_unavailable)->toBeTrue();

    $offer = StructuredData::event($program)['offers'];
    expect($offer['availability'] ?? null)->not->toBe('https://schema.org/InStock');
})->with([
    'cancelled' => [['event_status' => 'EventCancelled', 'ticket_availability' => 'InStock'], 'Acara dibatalkan'],
    'postponed' => [['event_status' => 'EventPostponed'], 'Pendaftaran ditunda'],
    'sold out' => [['ticket_availability' => 'SoldOut'], 'Kuota habis'],
    'not open yet' => [['registration_opens_at' => '2027-07-10 10:00:00', 'ticket_availability' => 'InStock'], 'Belum dibuka'],
    'completed' => [['event_date' => '2027-06-21', 'end_date' => '2027-06-21', 'ticket_availability' => 'InStock'], 'Pendaftaran ditutup'],
]);
