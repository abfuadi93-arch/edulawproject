<?php

use App\Models\Insight;
use App\Models\Program;
use App\Models\Publication;

it('opens the homepage without leaking template expressions or invalid links', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertDontSee('{{ insight.created_date }}', false)
        ->assertDontSee('{{ insight.author?.role_name }}', false)
        ->assertDontSee('{{ insight?.title }}', false)
        ->assertDontSee('{{ insight?.excerpt }}', false)
        ->assertDontSee('{{ publication.type }}', false)
        ->assertDontSee('{{ publication.title }}', false)
        ->assertDontSee('{{ publication.excerpt }}', false)
        ->assertDontSee('{{ publication.download_count }}', false)
        ->assertSee(route('insights.index'), false)
        ->assertSee(route('publications.index'), false)
        ->assertSee(route('programs.index'), false)
        ->assertSee(route('collaboration.index'), false)
        ->assertSee(route('privacy'), false)
        ->assertSee(route('terms'), false);

    preg_match_all('/\shref="([^"]*)"/i', $response->getContent(), $matches);

    expect($matches[1])
        ->not->toContain('', '#')
        ->each->not->toStartWith('javascript:');
});

it('shows compact empty states and omits unavailable publication statistics', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Belum ada Insight yang ditampilkan. Nantikan analisis hukum terbaru dari Edulaw Project.')
        ->assertSee('Lihat Semua Insight')
        ->assertSee('Belum ada publikasi yang tersedia. Nantikan riset dan publikasi terbaru dari Edulaw Project.')
        ->assertSee('Belum ada program yang ditampilkan. Nantikan program terbaru dari Edulaw Project.')
        ->assertDontSee('Total Unduhan')
        ->assertDontSee('Dokumen Tersedia')
        ->assertDontSee('Terpopuler (30 hari)')
        ->assertDontSee('Unduh atau buka publikasi');
});

it('renders available homepage data safely without inventing missing metadata', function () {
    $insight = Insight::query()->create([
        'title' => 'Analisis Hukum <script>alert("x")</script>',
        'slug' => 'analisis-hukum-aman',
        'excerpt' => 'Ringkasan analisis yang tersedia.',
        'content' => 'Isi analisis.',
        'status' => 'published',
        'published_at' => now()->subDay(),
        'reading_time' => 7,
        'featured' => true,
    ]);

    $publication = Publication::query()->create([
        'title' => 'Kajian Kebijakan Digital',
        'slug' => 'kajian-kebijakan-digital',
        'excerpt' => 'Ringkasan kajian.',
        'status' => 'published',
        'published_at' => now()->toDateString(),
        'featured' => true,
    ]);

    $program = Program::query()->create([
        'name' => 'Kelas Hukum Publik',
        'slug' => 'kelas-hukum-publik',
        'short_description' => 'Kelas untuk memahami hukum publik.',
        'event_date' => now()->addWeek(),
        'status' => 'upcoming',
        'publication_status' => 'published',
        'featured' => true,
    ]);

    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee($insight->title)
        ->assertSee(route('insights.show', $insight->slug), false)
        ->assertSee($publication->title)
        ->assertSee(route('publications.show', $publication->slug), false)
        ->assertSee($program->name)
        ->assertSee(route('programs.show', $program->slug), false)
        ->assertDontSee('<script>alert("x")</script>', false)
        ->assertDontSee('Unduh atau buka publikasi')
        ->assertDontSee('PDF')
        ->assertDontSee('12 hlm');
});
