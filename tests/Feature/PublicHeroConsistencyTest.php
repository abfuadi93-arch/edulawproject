<?php

test('hero halaman kanal memakai pola utama dan tinggi desktop yang seragam', function () {
    $pages = [
        [route('programs.index'), 'Program', 'Kanal Program', 'Program Edulaw Project dirancang sebagai ruang belajar'],
        [route('insights.index'), 'Editorial', 'Kanal Editorial', 'Editorial Edulaw menghadirkan analisis hukum'],
        [route('publications.index'), 'Riset & Publikasi', 'Kanal Riset & Publikasi', 'Repository kajian, policy brief, naskah akademik'],
        [route('opportunities.index'), 'Opportunities', 'Kanal Opportunities', 'Temukan beasiswa, magang, fellowship'],
        [route('multimedia.index'), 'Multimedia', 'Kanal Multimedia', 'Video, Shorts/Reels, dan dokumentasi kegiatan'],
    ];

    foreach ($pages as [$url, $breadcrumb, $channelLabel, $descriptionExcerpt]) {
        $html = $this->get($url)->assertOk()->getContent();
        $document = new DOMDocument;
        @$document->loadHTML($html);
        $hero = $document->getElementsByTagName('h1')->item(0)?->parentNode;

        while ($hero instanceof DOMElement && $hero->tagName !== 'section') {
            $hero = $hero->parentNode;
        }

        $heroMarkup = $document->saveHTML($hero);
        $heroText = html_entity_decode($hero->textContent, ENT_QUOTES | ENT_HTML5);
        $leftColumnText = html_entity_decode($document->getElementsByTagName('h1')->item(0)->parentNode->textContent, ENT_QUOTES | ENT_HTML5);

        expect($hero)->toBeInstanceOf(DOMElement::class)
            ->and($heroMarkup)
            ->toContain('lg:min-h-[240px]')
            ->toContain('lg:grid-cols-[minmax(0,7fr)_minmax(300px,3fr)]')
            ->not->toContain('divide-white/15')
            ->and($heroText)
            ->toContain('Beranda')
            ->toContain($breadcrumb)
            ->toContain($channelLabel)
            ->and($leftColumnText)->toContain($descriptionExcerpt);
    }
});

test('hero halaman informasi publik mengikuti pola hero Tentang', function () {
    $pages = [
        [route('about'), 'Tentang', 'Tentang Kami', 'Edulaw Project adalah ruang belajar'],
        [route('collaboration.index'), 'Kolaborasi', 'Kolaborasi', 'Edulaw Project membuka ruang kerja sama'],
        [route('contact.index'), 'Kontak', 'Kontak', 'Sampaikan pertanyaan, kebutuhan informasi'],
        [route('terms'), 'Syarat & Ketentuan', 'Ketentuan Layanan', 'Ketentuan penggunaan website Edulaw Project'],
        [route('privacy'), 'Kebijakan Privasi', 'Kebijakan', 'Cara Edulaw Project mengelola'],
    ];

    foreach ($pages as [$url, $breadcrumb, $eyebrow, $descriptionExcerpt]) {
        $html = $this->get($url)->assertOk()->getContent();
        $document = new DOMDocument;
        @$document->loadHTML($html);
        $hero = $document->getElementsByTagName('h1')->item(0)?->parentNode;

        while ($hero instanceof DOMElement && $hero->tagName !== 'section') {
            $hero = $hero->parentNode;
        }

        $heroMarkup = $document->saveHTML($hero);
        $heroText = html_entity_decode($hero->textContent, ENT_QUOTES | ENT_HTML5);

        expect($hero)->toBeInstanceOf(DOMElement::class)
            ->and($heroMarkup)
            ->toContain('lg:min-h-[240px]')
            ->toContain('lg:grid-cols-[minmax(0,7fr)_minmax(300px,3fr)]')
            ->not->toContain('divide-white/15')
            ->and($heroText)
            ->toContain('Beranda')
            ->toContain($breadcrumb)
            ->toContain($eyebrow)
            ->toContain($descriptionExcerpt);
    }
});

test('halaman kanal memakai lebar dan padding horizontal body yang seragam', function () {
    $pages = [
        route('programs.index'),
        route('insights.index'),
        route('publications.index'),
        route('opportunities.index'),
        route('multimedia.index'),
    ];

    foreach ($pages as $url) {
        $html = $this->get($url)->assertOk()->getContent();
        $document = new DOMDocument;
        @$document->loadHTML($html);
        $xpath = new DOMXPath($document);
        $standardContainers = $xpath->query(
            '//*[contains(concat(" ", normalize-space(@class), " "), " max-w-7xl ")'
            .' and contains(concat(" ", normalize-space(@class), " "), " px-5 ")'
            .' and contains(concat(" ", normalize-space(@class), " "), " sm:px-6 ")'
            .' and contains(concat(" ", normalize-space(@class), " "), " lg:px-8 ")]'
        );

        expect($standardContainers->length)->toBeGreaterThanOrEqual(2);
    }
});

test('hero kategori Editorial mengikuti struktur dan tinggi hero kanal', function (string $category, string $name) {
    $html = $this->get(route('insights.categories.show', $category))->assertOk()->getContent();
    $document = new DOMDocument;
    @$document->loadHTML($html);
    $heading = $document->getElementsByTagName('h1')->item(0);
    $hero = $heading?->parentNode;

    while ($hero instanceof DOMElement && $hero->tagName !== 'section') {
        $hero = $hero->parentNode;
    }

    $heroMarkup = $document->saveHTML($hero);
    $heroText = html_entity_decode($hero->textContent, ENT_QUOTES | ENT_HTML5);
    $leftColumnText = html_entity_decode($heading->parentNode->textContent, ENT_QUOTES | ENT_HTML5);
    $titleLines = collect($heading->childNodes)
        ->filter(fn ($node): bool => $node instanceof DOMElement && $node->tagName === 'span')
        ->map(fn (DOMElement $node): string => trim($node->textContent))
        ->values();

    expect($hero)->toBeInstanceOf(DOMElement::class)
        ->and($heroMarkup)->toContain('lg:min-h-[240px]')
        ->and($heroText)
        ->toContain('Beranda')
        ->toContain('Editorial')
        ->toContain($name)
        ->toContain('Kanal Editorial')
        ->toContain('artikel terbit')
        ->and($leftColumnText)->toContain($name)
        ->and($titleLines)->toHaveCount(2)
        ->and($titleLines->first())->toEndWith(':')
        ->and($titleLines->last())->not->toBeEmpty();
})->with([
    'law and governance' => ['law-governance', 'Law & Governance'],
    'legal 101' => ['legal-101', 'Legal 101'],
    'regulatory update' => ['regulatory-update', 'Regulatory Update'],
    'edulaw insight' => ['edulaw-insight', 'Edulaw Insight'],
]);
