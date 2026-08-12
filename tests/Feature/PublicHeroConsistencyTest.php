<?php

test('hero halaman kanal memakai tinggi desktop Program sebagai rujukan', function () {
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
            ->and($heroMarkup)->toContain('lg:min-h-[240px]')
            ->and($heroText)
            ->toContain('Beranda')
            ->toContain($breadcrumb)
            ->toContain($channelLabel)
            ->and($leftColumnText)->toContain($descriptionExcerpt);
    }
});
