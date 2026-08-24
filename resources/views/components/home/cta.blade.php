@props(['block' => null])

@php
    $meta = (array) ($block?->meta ?? []);
    $eyebrow = $block?->eyebrow ?? 'Kolaborasi';
    $title = $block?->title ?? 'Bangun ruang literasi hukum bersama Edulaw Project.';
    $body = $block?->body ?? 'Kami membuka ruang kolaborasi dengan kampus, komunitas, lembaga, dan mitra strategis untuk mengembangkan edukasi hukum yang inklusif dan berdampak.';
    $primaryUrl = $block?->resolved_url ?? route('collaboration.index');
    $primaryLabel = $block?->url_label ?? 'Ajukan Kerja Sama';
    $secondaryUrl = \App\Support\EdulawSite::resolveUrl($meta['secondary_url'] ?? null, route('programs.index'));
    $secondaryLabel = $meta['secondary_label'] ?? 'Lihat Program';
@endphp

<x-shared.cta-section
    heading-id="home-collaboration-title"
    :eyebrow="$eyebrow"
    :title="$title"
    :body="$body"
    :primary-url="$primaryUrl"
    :primary-label="$primaryLabel"
    :secondary-url="$secondaryUrl"
    :secondary-label="$secondaryLabel"
/>
