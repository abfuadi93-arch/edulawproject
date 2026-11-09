<?php

use App\Models\Multimedia;
use App\Models\Program;
use App\Support\PublicContentQuality;

it('previews sourced updates and only applies to matching public records', function () {
    $video = Multimedia::create([
        'title' => 'DIKSI Seri 12', 'type' => 'video', 'platform' => 'youtube',
        'media_url' => 'https://youtu.be/2ATZEA_sqdQ', 'description' => 'Ringkasan lama.',
        'status' => 'published',
    ]);
    $draft = Multimedia::create([
        'title' => 'DIKSI Seri 12 Draft', 'type' => 'video', 'platform' => 'youtube',
        'media_url' => 'https://youtu.be/2ATZEA_sqdQ', 'description' => 'Draf.',
        'status' => 'draft',
    ]);
    $program = Program::create([
        'name' => 'DIKSI Seri 12', 'slug' => 'diskusi-literasi-konstitusi-seri-12',
        'description' => 'Ringkasan acara lama.', 'event_date' => '2026-05-20',
        'status' => 'archived', 'publication_status' => 'published',
    ]);

    $this->artisan('edulaw:enrich-diksi')->assertSuccessful();
    expect($video->fresh()->description)->toBe('Ringkasan lama.')
        ->and($program->fresh()->description)->toBe('Ringkasan acara lama.');

    $this->artisan('edulaw:enrich-diksi', ['--apply' => true])->assertSuccessful();

    expect($video->fresh()->description)->toContain('Mery Christian Putri')
        ->and(PublicContentQuality::multimedia($video->fresh()))->toBeTrue()
        ->and($draft->fresh()->description)->toBe('Draf.')
        ->and($program->fresh()->description)->toContain('diksi-seri-12-big-data-dan-ekonomi-digital-25078');

    $this->get(route('multimedia.show', $video->slug))->assertOk()
        ->assertSee('Liputan resmi Mahkamah Konstitusi RI')
        ->assertSee('Mery Christian Putri');

    $snapshot = $video->fresh()->getAttributes();
    $this->travel(1)->hour();
    $this->artisan('edulaw:enrich-diksi', ['--apply' => true])->assertSuccessful();
    expect($video->fresh()->getAttributes())->toBe($snapshot);
});
