@props([
    'block' => null,
    'eyebrow' => null,
    'title' => null,
    'body' => null,
    'primaryUrl' => null,
    'primaryLabel' => null,
    'secondaryUrl' => null,
    'secondaryLabel' => null,
    'titleClass' => '',
])

@php
    $meta = (array) ($block?->meta ?? []);
    $resolvedEyebrow = $eyebrow ?? $block?->eyebrow ?? 'Kolaborasi';
    $resolvedTitle = $title ?? $block?->title ?? 'Bangun ruang literasi hukum bersama Edulaw Project.';
    $resolvedBody = $body ?? $block?->body ?? 'Edulaw Project terbuka untuk kerja sama program edukasi hukum, diskusi publik, riset, publikasi, pelatihan, dan pengembangan ekosistem literasi hukum.';
    $resolvedPrimaryUrl = $primaryUrl ?? $block?->resolved_url ?? route('collaboration.index');
    $resolvedPrimaryLabel = $primaryLabel ?? $block?->url_label ?? 'Ajukan Kerja Sama';
    $resolvedSecondaryUrl = $secondaryUrl ?? \App\Support\EdulawSite::resolveUrl($meta['secondary_url'] ?? null, route('programs.index'));
    $resolvedSecondaryLabel = $secondaryLabel ?? $meta['secondary_label'] ?? 'Lihat Program';
@endphp

<x-shared.cta-section
    :eyebrow="$resolvedEyebrow"
    :title="$resolvedTitle"
    :body="$resolvedBody"
    :primary-url="$resolvedPrimaryUrl"
    :primary-label="$resolvedPrimaryLabel"
    :secondary-url="$resolvedSecondaryUrl"
    :secondary-label="$resolvedSecondaryLabel"
    :title-class="$titleClass"
/>
