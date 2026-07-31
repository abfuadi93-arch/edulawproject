<?php

use App\Models\Author;
use App\Models\Insight;
use App\Models\InsightCategory;
use App\Models\Multimedia;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\Publication;
use App\Models\PublicationType;

function structuredDataSchemas(string $html): array
{
    preg_match_all(
        '/<script type="application\/ld\+json">(.*?)<\/script>/s',
        $html,
        $matches,
    );

    return collect($matches[1] ?? [])
        ->map(fn (string $json): array => json_decode($json, true, 512, JSON_THROW_ON_ERROR))
        ->all();
}

function structuredDataOfType(array $schemas, string $type): ?array
{
    return collect($schemas)->first(fn (array $schema): bool => ($schema['@type'] ?? null) === $type);
}

test('organization is global and homepage exposes website structured data', function () {
    $schemas = structuredDataSchemas(
        $this->get(route('home'))->assertOk()->getContent(),
    );

    $organization = structuredDataOfType($schemas, 'Organization');
    $website = structuredDataOfType($schemas, 'WebSite');

    expect($organization)
        ->not->toBeNull()
        ->and($organization['name'])->toBe('Edulaw Project')
        ->and($organization['url'])->toBe('https://edulawproject.id')
        ->and($organization['logo']['url'])->toBe('https://edulawproject.id/images/logo/edulaw-logo.png')
        ->and($organization['email'])->toBe('hello@edulawproject.id')
        ->and($organization['address']['addressLocality'])->toBe('Jakarta')
        ->and($organization['sameAs'])->toContain(
            config('edulaw.social.instagram_url'),
            config('edulaw.social.linkedin_url'),
            config('edulaw.social.youtube_url'),
        )
        ->and($website)
        ->not->toBeNull()
        ->and($website['potentialAction']['@type'])->toBe('SearchAction');
});

test('insight pages expose item list article authors and breadcrumbs', function () {
    $category = InsightCategory::query()->create([
        'name' => 'Edulaw Insight',
        'slug' => 'edulaw-insight-schema',
        'is_active' => true,
    ]);
    $author = Author::query()->create([
        'name' => 'Nadia Peneliti',
        'slug' => 'nadia-peneliti-schema',
        'position' => 'Peneliti Hukum',
        'is_active' => true,
    ]);
    $insight = Insight::query()->create([
        'insight_category_id' => $category->id,
        'title' => 'Kerugian Konstitusional dalam Pengujian Undang-Undang',
        'slug' => 'kerugian-konstitusional-schema',
        'excerpt' => 'Analisis konsep kerugian konstitusional dan penerapannya dalam pengujian undang-undang.',
        'content' => '<p>Isi analisis hukum.</p>',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $author->insights()->attach($insight->id, ['author_order' => 1, 'role' => 'Penulis']);

    $indexSchemas = structuredDataSchemas(
        $this->get(route('insights.index'))->assertOk()->getContent(),
    );
    $detailSchemas = structuredDataSchemas(
        $this->get(route('insights.show', $insight->slug))->assertOk()->getContent(),
    );

    $itemList = structuredDataOfType($indexSchemas, 'ItemList');
    $article = structuredDataOfType($detailSchemas, 'Article');
    $breadcrumbs = structuredDataOfType($detailSchemas, 'BreadcrumbList');

    expect($itemList['itemListElement'][0]['item']['url'])->toBe(route('insights.show', $insight->slug))
        ->and($article['headline'])->toBe($insight->title)
        ->and($article['author'][0]['name'])->toBe($author->name)
        ->and($article['author'][0]['url'])->toBe(route('profiles.show', $author->slug))
        ->and($article['datePublished'])->toBe($insight->published_at->toIso8601String())
        ->and($breadcrumbs['itemListElement'])->toHaveCount(3)
        ->and($breadcrumbs['itemListElement'][2]['name'])->toBe($insight->title);
});

test('profile pages expose person and breadcrumb data from public fields', function () {
    $author = Author::query()->create([
        'name' => 'Aulia Rahman',
        'slug' => 'aulia-rahman-schema',
        'bio' => 'Peneliti hukum tata negara dan kebijakan publik.',
        'position' => 'Researcher',
        'institution' => 'Edulaw Project',
        'interests' => ['Konstitusi', 'Kebijakan Publik'],
        'social_links' => ['linkedin' => 'https://www.linkedin.com/in/aulia-rahman'],
        'is_active' => true,
    ]);

    $schemas = structuredDataSchemas(
        $this->get(route('profiles.show', $author->slug))->assertOk()->getContent(),
    );

    $person = structuredDataOfType($schemas, 'Person');

    expect($person['name'])->toBe($author->name)
        ->and($person['jobTitle'])->toBe('Researcher')
        ->and($person['knowsAbout'])->toBe(['Konstitusi', 'Kebijakan Publik'])
        ->and($person['sameAs'])->toBe(['https://www.linkedin.com/in/aulia-rahman'])
        ->and(structuredDataOfType($schemas, 'BreadcrumbList'))->not->toBeNull();
});

test('dated programs expose event data only when a real location is available', function () {
    $category = ProgramCategory::query()->create([
        'name' => 'Kelas Publik',
        'slug' => 'kelas-publik-schema',
        'is_active' => true,
    ]);
    $program = Program::query()->create([
        'program_category_id' => $category->id,
        'name' => 'Kelas Kebijakan Publik',
        'slug' => 'kelas-kebijakan-publik-schema',
        'short_description' => 'Kelas untuk memahami proses penyusunan dan evaluasi kebijakan publik.',
        'format' => 'hybrid',
        'event_date' => now()->addWeek(),
        'end_date' => now()->addWeek()->addHours(2),
        'registration_link' => 'https://example.test/daftar',
        'location' => 'Jakarta',
        'status' => 'upcoming',
        'publication_status' => 'published',
    ]);

    $schemas = structuredDataSchemas(
        $this->get(route('programs.show', $program->slug))->assertOk()->getContent(),
    );
    $event = structuredDataOfType($schemas, 'Event');

    expect($event)
        ->not->toBeNull()
        ->and($event['eventAttendanceMode'])->toBe('https://schema.org/MixedEventAttendanceMode')
        ->and($event['location'])->toHaveCount(2)
        ->and($event['startDate'])->toBe($program->event_date->toIso8601String())
        ->and(structuredDataOfType($schemas, 'BreadcrumbList'))->not->toBeNull();
});

test('publication schema type follows the real publication type', function () {
    $type = PublicationType::query()->create([
        'name' => 'Policy Brief',
        'slug' => 'policy-brief-schema',
        'is_active' => true,
    ]);
    $publication = Publication::query()->create([
        'publication_type_id' => $type->id,
        'title' => 'Reformasi Hukum Acara',
        'slug' => 'reformasi-hukum-acara-schema',
        'excerpt' => 'Kajian mengenai kebutuhan pembaruan hukum acara untuk memperkuat akses terhadap keadilan.',
        'status' => 'published',
        'published_at' => today(),
        'page_count' => 24,
    ]);

    $schemas = structuredDataSchemas(
        $this->get(route('publications.show', $publication->slug))->assertOk()->getContent(),
    );
    $report = structuredDataOfType($schemas, 'Report');

    expect($report)
        ->not->toBeNull()
        ->and($report['name'])->toBe($publication->title)
        ->and($report['pagination'])->toBe('24')
        ->and(structuredDataOfType($schemas, 'BreadcrumbList'))->not->toBeNull();
});

test('youtube entries expose video object and multimedia item list', function () {
    $video = Multimedia::query()->create([
        'title' => 'Memahami Putusan Mahkamah Konstitusi',
        'slug' => 'memahami-putusan-mk-schema',
        'type' => 'video',
        'platform' => 'youtube',
        'description' => 'Video edukasi untuk memahami struktur dan dampak putusan Mahkamah Konstitusi.',
        'media_url' => 'https://www.youtube.com/watch?v=abc123XYZ_9',
        'embed_url' => 'https://www.youtube.com/embed/abc123XYZ_9',
        'duration' => '12:30',
        'published_at' => now(),
        'status' => 'published',
    ]);

    $schemas = structuredDataSchemas(
        $this->get(route('multimedia.index'))->assertOk()->getContent(),
    );
    $videoObject = structuredDataOfType($schemas, 'VideoObject');
    $itemList = structuredDataOfType($schemas, 'ItemList');

    expect($videoObject)
        ->not->toBeNull()
        ->and($videoObject['embedUrl'])->toBe('https://www.youtube.com/embed/abc123XYZ_9')
        ->and($videoObject['duration'])->toBe('PT12M30S')
        ->and($videoObject['uploadDate'])->toBe($video->published_at->toIso8601String())
        ->and($itemList['itemListElement'][0]['item']['name'])->toBe($video->title);
});
