<?php

use App\Models\Author;
use App\Models\Insight;
use App\Models\Multimedia;
use App\Models\Program;
use App\Models\Publication;
use App\Support\PublicContentQuality;

test('word count ignores markup and supports Indonesian unicode text', function () {
    expect(PublicContentQuality::wordCount('<style>hidden words</style><p>Keadilan, kebijakan, dan perlindungan konstitusional.</p>'))
        ->toBe(5);
});

test('thin records remain public but are not considered index ready', function () {
    expect(PublicContentQuality::insight(new Insight([
        'title' => 'Editorial Tipis',
        'slug' => 'editorial-tipis',
        'content' => 'Ringkasan singkat.',
    ])))->toBeFalse()
        ->and(PublicContentQuality::publication(new Publication([
            'title' => 'Publikasi Tipis',
            'slug' => 'publikasi-tipis',
            'excerpt' => 'Abstrak singkat.',
        ])))->toBeFalse()
        ->and(PublicContentQuality::program(new Program([
            'name' => 'Program Tipis',
            'slug' => 'program-tipis',
            'description' => 'Dokumentasi singkat.',
        ])))->toBeFalse()
        ->and(PublicContentQuality::multimedia(new Multimedia([
            'title' => 'Video Tipis',
            'slug' => 'video-tipis',
            'type' => 'video',
            'platform' => 'youtube',
            'media_url' => 'https://www.youtube.com/watch?v=oMjVH5Rbn5k',
        ])))->toBeFalse()
        ->and(PublicContentQuality::author(new Author([
            'name' => 'Penulis Baru',
            'slug' => 'penulis-baru',
        ]), 1, 0))->toBeFalse();
});

test('substantive records become index ready without requiring filler', function () {
    $insight = new Insight([
        'title' => 'Editorial Substantif',
        'slug' => 'editorial-substantif',
        'content' => str_repeat('Analisis hukum berbasis sumber primer menjelaskan konteks dan dampak kebijakan. ', 45),
    ]);
    $publication = new Publication([
        'title' => 'Publikasi Substantif',
        'slug' => 'publikasi-substantif',
        'description' => str_repeat('Ringkasan riset menjelaskan masalah hukum, pendekatan, bukti, dan hasil kajian. ', 20),
        'methodology' => str_repeat('Metode menggunakan analisis dokumen hukum dan perbandingan kebijakan. ', 8),
        'contribution' => str_repeat('Kontribusi kajian menyediakan kerangka evaluasi bagi pembuat kebijakan. ', 8),
    ]);
    $program = new Program([
        'name' => 'Program Substantif',
        'slug' => 'program-substantif',
        'description' => str_repeat('Dokumentasi menjelaskan konteks kegiatan, materi, peserta, proses, dan hasil pembelajaran. ', 20),
    ]);
    $multimedia = new Multimedia([
        'title' => 'Video Substantif',
        'slug' => 'video-substantif',
        'type' => 'video',
        'platform' => 'youtube',
        'media_url' => 'https://www.youtube.com/watch?v=oMjVH5Rbn5k',
        'description' => str_repeat('Ringkasan video menjelaskan dasar hukum, konteks, pembahasan, dan dampaknya bagi publik. ', 15),
    ]);
    $author = new Author([
        'name' => 'Penulis Substantif',
        'slug' => 'penulis-substantif',
        'bio' => str_repeat('Penulis meneliti hukum dan kebijakan publik untuk memperluas literasi masyarakat. ', 8),
    ]);

    expect(PublicContentQuality::insight($insight))->toBeTrue()
        ->and(PublicContentQuality::publication($publication))->toBeTrue()
        ->and(PublicContentQuality::program($program))->toBeTrue()
        ->and(PublicContentQuality::multimedia($multimedia))->toBeTrue()
        ->and(PublicContentQuality::author($author, 1, 0))->toBeTrue();
});
