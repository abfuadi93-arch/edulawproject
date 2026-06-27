@extends('layouts.app')

@section('title', ($publication->seo_title ?? $publication->title) . ' - Edulaw Project')

@section('content')
@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Route;

    $indexUrl = Route::has('publications.index')
        ? route('publications.index')
        : url('/riset-publikasi');

    $collaborationUrl = Route::has('collaboration.index')
        ? route('collaboration.index')
        : url('/kolaborasi');

    $imageUrl = function (?string $path) {
        return edulaw_file_url($path);
    };

    $fileUrl = function (?string $path) {
        return edulaw_file_url($path);
    };

    $typeName = $publication?->type?->name
        ?? $publication?->publicationType?->name
        ?? $publication?->publication_type
        ?? $publication?->type
        ?? 'Publikasi';

    $authors = isset($publication->authors) && $publication->authors->count()
        ? $publication->authors->pluck('name')->filter()->join(', ')
        : ($publication->author_name ?? $publication->source_name ?? 'Edulaw Project');

    $publishedDate = 'Belum dipublikasikan';
    $publishedYear = null;

    if (! empty($publication->published_at)) {
        try {
            $publishedDate = Carbon::parse($publication->published_at)->translatedFormat('F Y');
            $publishedYear = Carbon::parse($publication->published_at)->format('Y');
        } catch (\Throwable $e) {
            $publishedDate = $publication->published_at;
        }
    }

    $summary = $publication->excerpt
        ?: Str::limit(strip_tags($publication->description ?? ''), 420)
        ?: 'Publikasi Edulaw Project untuk mendukung literasi hukum, riset kebijakan, dan penguatan pengetahuan publik.';

    $description = trim(strip_tags($publication->description ?? ''));

    $downloadUrl = null;

    if (! empty($publication->pdf_file)) {
        $downloadUrl = $fileUrl($publication->pdf_file);
    } elseif (! empty($publication->external_url)) {
        $downloadUrl = $publication->external_url;
    }

    $pdfPreviewUrl = ! empty($publication->pdf_file)
        ? $fileUrl($publication->pdf_file) . '#page=1&toolbar=0&navpanes=0&scrollbar=0&view=FitH'
        : null;

    $coverImage = $imageUrl($publication->cover_image ?? null);

    $tags = collect($publication->tags ?? []);

    $relatedCollection = collect(
        $relatedPublications
        ?? $related
        ?? collect()
    );

    $pageCountLabel = $publication->page_count
        ? $publication->page_count . ' halaman'
        : 'Dokumen digital';

    $sourceName = $publication->source_name ?: 'Edulaw Project';

    $citationText = 'Edulaw Project. (' . ($publishedYear ?: now()->format('Y')) . '). ' . $publication->title . '. Edulaw Project.';

    $palettes = [
        ['from' => '#102A43', 'via' => '#1E5F74', 'to' => '#F4B942', 'accent' => '#F4B942'],
        ['from' => '#240046', 'via' => '#7B2CBF', 'to' => '#F72585', 'accent' => '#F72585'],
        ['from' => '#143601', 'via' => '#2D6A4F', 'to' => '#95D5B2', 'accent' => '#B7E4C7'],
        ['from' => '#3D0C11', 'via' => '#A4161A', 'to' => '#FFB703', 'accent' => '#FFB703'],
        ['from' => '#001219', 'via' => '#005F73', 'to' => '#94D2BD', 'accent' => '#94D2BD'],
        ['from' => '#03045E', 'via' => '#0077B6', 'to' => '#90E0EF', 'accent' => '#90E0EF'],
    ];

    $seed = abs(crc32(($publication->slug ?? '') . '|' . ($publication->title ?? '') . '|' . ($publication->id ?? '')));
    $palette = $palettes[$seed % count($palettes)];

    $relatedPreviewUrl = function ($relatedPublication) use ($fileUrl) {
        if (empty($relatedPublication->pdf_file)) {
            return null;
        }

        return $fileUrl($relatedPublication->pdf_file) . '#page=1&toolbar=0&navpanes=0&scrollbar=0&view=FitH';
    };

    $relatedCoverUrl = function ($relatedPublication) use ($imageUrl) {
        return $imageUrl($relatedPublication->cover_image ?? null);
    };

    $relatedTypeName = function ($relatedPublication) {
        return $relatedPublication?->type?->name
            ?? $relatedPublication?->publicationType?->name
            ?? $relatedPublication?->publication_type
            ?? $relatedPublication?->type
            ?? 'Publikasi';
    };

    $relatedExcerpt = function ($relatedPublication) {
        $text = $relatedPublication->excerpt ?: strip_tags($relatedPublication->description ?? '');

        return Str::limit($text ?: 'Publikasi Edulaw Project untuk mendukung literasi hukum dan kebijakan publik.', 145);
    };
@endphp

<style>
    .publication-show {
        background: #f8fafc;
        color: #0f172a;
    }

    .publication-container {
        max-width: 1180px;
        margin: 0 auto;
        padding: 0 24px;
    }

    .publication-hero {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        background:
            linear-gradient(135deg, rgba(6, 26, 61, .94), rgba(15, 40, 104, .90)),
            url('https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=1800&q=85') center / cover no-repeat;
        color: #ffffff;
        padding: 72px 0 78px;
    }

    .publication-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -2;
        background:
            radial-gradient(circle at 86% 18%, rgba(244, 185, 66, .18), transparent 30%),
            radial-gradient(circle at 14% 72%, rgba(45, 212, 191, .14), transparent 34%);
        pointer-events: none;
    }

    .publication-hero::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        background:
            linear-gradient(120deg, rgba(255,255,255,.08), transparent 34%, rgba(0,0,0,.20)),
            rgba(3, 10, 28, .18);
        pointer-events: none;
    }

    .publication-hero-inner {
        position: relative;
        z-index: 1;
        width: 100%;
    }

    .publication-back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: rgba(255,255,255,.78);
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
        transition: .2s ease;
    }

    .publication-back-link:hover {
        color: #ffffff;
    }

    .publication-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 9px;
        margin-top: 28px;
    }

    .publication-badge {
        display: inline-flex;
        align-items: center;
        width: fit-content;
        border: 1px solid rgba(255,255,255,.18);
        border-radius: 999px;
        background: rgba(255,255,255,.08);
        padding: 7px 11px;
        color: rgba(255,255,255,.84);
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .14em;
        text-transform: uppercase;
        backdrop-filter: blur(10px);
    }

    .publication-badge-primary {
        border-color: rgba(56, 164, 216, .38);
        background: rgba(56, 164, 216, .18);
        color: #e0f2fe;
    }

    .publication-badge-gold {
        border-color: rgba(245, 185, 67, .42);
        background: rgba(245, 185, 67, .16);
        color: #fff3cf;
    }

    .publication-hero-title {
        margin-top: 18px;
        max-width: 100%;
        color: #ffffff;
        font-size: clamp(38px, 5vw, 64px);
        line-height: 1.03;
        font-weight: 950;
        letter-spacing: -.055em;
    }

    .publication-hero-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 9px;
        margin-top: 22px;
        max-width: 100%;
        color: rgba(255,255,255,.76);
        font-size: 13px;
        font-weight: 800;
    }

    .publication-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 30px;
        max-width: 100%;
    }

    .publication-button-primary,
    .publication-button-secondary,
    .publication-button-soft {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        border-radius: 999px;
        padding: 13px 18px;
        font-size: 13px;
        font-weight: 950;
        text-decoration: none;
        transition: .2s ease;
    }

    .publication-button-primary {
        border: 1px solid rgba(255,255,255,.16);
        background: #ffffff;
        color: #0F2868;
    }

    .publication-button-primary:hover {
        transform: translateY(-1px);
        background: #f8fafc;
    }

    .publication-button-secondary {
        border: 1px solid rgba(255,255,255,.22);
        background: rgba(255,255,255,.08);
        color: #ffffff;
        backdrop-filter: blur(10px);
    }

    .publication-button-secondary:hover {
        background: rgba(255,255,255,.14);
    }

    .publication-button-soft {
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #0F2868;
    }

    .publication-button-soft:hover {
        border-color: rgba(15, 40, 104, .32);
        background: #f8fafc;
    }

    .publication-body {
        padding: 42px 0 72px;
    }

    .publication-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 320px;
        gap: 28px;
        align-items: start;
    }

    .publication-main {
        display: grid;
        gap: 24px;
    }

    .publication-sidebar {
        position: sticky;
        top: 96px;
        display: grid;
        gap: 18px;
    }

    .publication-panel {
        border: 1px solid #e2e8f0;
        border-radius: 28px;
        background: #ffffff;
        padding: 24px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }

    .publication-panel-label {
        color: #0F2868;
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .20em;
        text-transform: uppercase;
    }

    .publication-panel h2 {
        margin-top: 10px;
        color: #0f172a;
        font-size: 28px;
        line-height: 1.15;
        font-weight: 950;
        letter-spacing: -.04em;
    }

    .publication-panel h3 {
        margin-top: 10px;
        color: #0f172a;
        font-size: 20px;
        line-height: 1.25;
        font-weight: 950;
        letter-spacing: -.03em;
    }

    .publication-panel p {
        margin-top: 16px;
        color: #334155;
        font-size: 15px;
        line-height: 1.85;
    }

    .publication-prose {
        margin-top: 16px;
        color: #334155;
        font-size: 15px;
        line-height: 1.85;
    }

    .publication-prose p {
        margin: 0 0 16px;
    }

    .publication-preview-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-top: 8px;
    }

    .publication-preview-frame {
        margin-top: 20px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-radius: 22px;
        background: #f8fafc;
    }

    .publication-preview-frame iframe {
        display: block;
        width: 100%;
        height: 660px;
        border: 0;
        background: #ffffff;
    }

    .publication-preview-frame img {
        display: block;
        width: 100%;
        height: 520px;
        object-fit: cover;
        background: #ffffff;
    }

    .publication-preview-fallback {
        position: relative;
        isolation: isolate;
        display: flex;
        min-height: 520px;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
        padding: 32px;
        color: #ffffff;
    }

    .publication-preview-fallback::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        background: linear-gradient(120deg, rgba(255,255,255,.14), transparent 38%, rgba(0,0,0,.30));
    }

    .publication-preview-fallback .line {
        width: 72px;
        height: 9px;
        border-radius: 999px;
        background: currentColor;
        opacity: .9;
    }

    .publication-preview-fallback strong {
        display: block;
        margin-top: 28px;
        max-width: 560px;
        font-size: 32px;
        line-height: 1.05;
        font-weight: 950;
        letter-spacing: -.04em;
    }

    .publication-meta-list {
        display: grid;
        gap: 16px;
        margin-top: 18px;
    }

    .publication-meta-item span {
        display: block;
        color: #94a3b8;
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .publication-meta-item strong {
        display: block;
        margin-top: 5px;
        color: #0f172a;
        font-size: 13px;
        line-height: 1.45;
        font-weight: 900;
    }

    .publication-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 18px;
    }

    .publication-tags span,
    .publication-tags a {
        display: inline-flex;
        border-radius: 999px;
        background: #ecfdf9;
        color: #0f766e;
        padding: 7px 10px;
        font-size: 11px;
        font-weight: 900;
        text-decoration: none;
    }

    .publication-action-list {
        display: grid;
        gap: 10px;
        margin-top: 18px;
    }

    .publication-action-list a,
    .publication-action-list button {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
        padding: 12px 14px;
        color: #0F2868;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
        cursor: pointer;
        transition: .2s ease;
    }

    .publication-action-list a:hover,
    .publication-action-list button:hover {
        border-color: rgba(15, 40, 104, .28);
        background: #f8fafc;
    }

    .publication-citation-box {
        margin-top: 16px;
        border-radius: 18px;
        background: #f8fafc;
        padding: 16px;
        color: #334155;
        font-size: 13px;
        line-height: 1.7;
        font-weight: 700;
    }

    .publication-related-header {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 20px;
    }

    .publication-related-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .publication-related-card {
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-radius: 22px;
        background: #ffffff;
        transition: .25s ease;
    }

    .publication-related-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 34px rgba(15, 23, 42, .08);
    }

    .publication-related-preview {
        height: 150px;
        overflow: hidden;
        background: #f8fafc;
    }

    .publication-related-preview iframe,
    .publication-related-preview img {
        display: block;
        width: 100%;
        height: 150px;
        border: 0;
        object-fit: cover;
        background: #ffffff;
        pointer-events: none;
    }

    .publication-related-content {
        padding: 17px;
    }

    .publication-related-badge {
        display: inline-flex;
        width: fit-content;
        border: 1px solid rgba(31, 60, 105, .15);
        border-radius: 999px;
        background: #e8ebef;
        color: #1f3c69;
        padding: 6px 10px;
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .10em;
        text-transform: uppercase;
    }

    .publication-related-content h3 {
        margin-top: 10px;
        color: #0f172a;
        font-size: 16px;
        line-height: 1.32;
        font-weight: 950;
    }

    .publication-related-content h3 a {
        color: inherit;
        text-decoration: none;
    }

    .publication-related-content p {
        margin-top: 10px;
        color: #64748b;
        font-size: 13px;
        line-height: 1.65;
    }

    @media (max-width: 1024px) {
        .publication-layout {
            grid-template-columns: 1fr;
        }

        .publication-sidebar {
            position: static;
        }

        .publication-related-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 720px) {
        .publication-container {
            padding: 0 16px;
        }

        .publication-hero {
            padding: 52px 0 58px;
        }

        .publication-badges {
            margin-top: 22px;
        }

        .publication-hero-title {
            font-size: clamp(34px, 10vw, 48px);
            letter-spacing: -.045em;
        }

        .publication-hero-meta {
            align-items: flex-start;
            flex-direction: column;
            gap: 6px;
        }

        .publication-hero-meta span:nth-child(2),
        .publication-hero-meta span:nth-child(4) {
            display: none;
        }

        .publication-preview-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .publication-preview-frame iframe {
            height: 480px;
        }

        .publication-panel {
            border-radius: 22px;
            padding: 20px;
        }
    }
</style>

<main class="publication-show">
    <x-shared.page-header
        :title="$publication->title"
        :description="Str::limit($summary, 280)"
        :eyebrow="$typeName"
        :background-image="$coverImage ?: 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=1800&q=85'"
        :background-alt="$publication->title"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Riset & Publikasi', 'url' => $indexUrl],
            ['label' => $typeName],
        ]"
    >
        <div class="flex flex-wrap gap-2">
            @if (! empty($publication->featured))
                <span class="publication-badge publication-badge-gold">
                    Pilihan
                </span>
            @endif

            @if ($publishedYear)
                <span class="publication-badge">
                    {{ $publishedYear }}
                </span>
            @endif

            <span class="publication-badge">
                {{ $pageCountLabel }}
            </span>
        </div>

        <div class="mt-5 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm font-semibold text-white/76">
            <span class="font-bold text-white">{{ $authors }}</span>
            <span class="h-1 w-1 rounded-full bg-white/45"></span>
            <span>{{ $publishedDate }}</span>
            <span class="h-1 w-1 rounded-full bg-white/45"></span>
            <span>{{ $sourceName }}</span>
        </div>

        <div class="publication-hero-actions">
            @if ($downloadUrl)
                <a
                    href="{{ $downloadUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="publication-button-primary"
                >
                    Unduh Publikasi
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            @endif

            <a href="#ringkasan" class="publication-button-secondary">
                Baca Ringkasan
            </a>
        </div>
    </x-shared.page-header>

    <section class="publication-body">
        <div class="publication-container">
            <div class="publication-layout">
                <div class="publication-main">


                    <article id="preview-pdf" class="publication-panel">
                        <div class="publication-panel-label">
                            Preview PDF
                        </div>

                        <div class="publication-preview-header">
                            <h2>Baca dokumen langsung dari halaman ini.</h2>

                            @if ($downloadUrl)
                                <a
                                    href="{{ $downloadUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="publication-button-soft"
                                >
                                    Unduh Publikasi
                                </a>
                            @endif
                        </div>

                        <div class="publication-preview-frame">
                            @if ($pdfPreviewUrl)
                                <iframe
                                    src="{{ $pdfPreviewUrl }}"
                                    title="Preview dokumen {{ $publication->title }}"
                                    loading="lazy"
                                ></iframe>
                            @elseif ($coverImage)
                                <img
                                    src="{{ $coverImage }}"
                                    alt="{{ $publication->title }}"
                                >
                            @else
                                <div
                                    class="publication-preview-fallback"
                                    style="background: linear-gradient(135deg, {{ $palette['from'] }} 0%, {{ $palette['via'] }} 50%, {{ $palette['to'] }} 100%);"
                                >
                                    <div>
                                        <span class="line" style="color: {{ $palette['accent'] }}"></span>
                                        <strong>{{ $publication->title }}</strong>
                                    </div>

                                    <span class="publication-badge publication-badge-primary">
                                        {{ $typeName }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </article>

                    <article id="ringkasan" class="publication-panel">
                        <div class="publication-panel-label">
                            Ringkasan Publikasi
                        </div>

                        <h2>Ringkasan Publikasi</h2>

                        <p>{{ $summary }}</p>
                    </article>

                    

                    <section class="publication-panel">
                        <div class="publication-related-header">
                            <div>
                                <div class="publication-panel-label">
                                    Publikasi Terkait
                                </div>

                                <h2>Baca Publikasi Lainnya</h2>
                            </div>

                            <a href="{{ $indexUrl }}" class="publication-button-soft">
                                Semua Publikasi →
                            </a>
                        </div>

                        @if ($relatedCollection->count())
                            <div class="publication-related-grid">
                                @foreach ($relatedCollection->take(3) as $relatedPublication)
                                    @php
                                        $relatedPdfPreview = $relatedPreviewUrl($relatedPublication);
                                        $relatedCover = $relatedCoverUrl($relatedPublication);
                                    @endphp

                                    <article class="publication-related-card">
                                        <div class="publication-related-preview">
                                            @if ($relatedCover)
                                                <img
                                                    src="{{ $relatedCover }}"
                                                    alt="{{ $relatedPublication->title }}"
                                                >
                                            @elseif ($relatedPdfPreview)
                                                <iframe
                                                    src="{{ $relatedPdfPreview }}"
                                                    title="Preview {{ $relatedPublication->title }}"
                                                    loading="lazy"
                                                ></iframe>
                                            @else
                                                <div
                                                    style="height:150px;background:linear-gradient(135deg, {{ $palette['from'] }}, {{ $palette['via'] }}, {{ $palette['to'] }});"
                                                ></div>
                                            @endif
                                        </div>

                                        <div class="publication-related-content">
                                            <span class="publication-related-badge">
                                                {{ $relatedTypeName($relatedPublication) }}
                                            </span>

                                            <h3>
                                                <a href="{{ route('publications.show', $relatedPublication->slug) }}">
                                                    {{ $relatedPublication->title }}
                                                </a>
                                            </h3>

                                            <p>{{ $relatedExcerpt($relatedPublication) }}</p>

                                            <div style="margin-top:12px;">
                                                <a
                                                    href="{{ route('publications.show', $relatedPublication->slug) }}"
                                                    class="publication-button-soft"
                                                    style="padding:9px 12px;font-size:12px;"
                                                >
                                                    Baca Ringkasan →
                                                </a>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <p>
                                Belum ada publikasi terkait yang tersedia. Silakan kembali ke halaman
                                Riset & Publikasi untuk menjelajahi koleksi lainnya.
                            </p>
                        @endif
                    </section>
                </div>

                <aside class="publication-sidebar">
                    <section class="publication-panel">
                        <div class="publication-panel-label">
                            Metadata
                        </div>

                        <div class="publication-meta-list">
                            <div class="publication-meta-item">
                                <span>Penulis</span>
                                <strong>{{ $authors }}</strong>
                            </div>

                            <div class="publication-meta-item">
                                <span>Tahun</span>
                                <strong>{{ $publishedYear ?: '-' }}</strong>
                            </div>

                            <div class="publication-meta-item">
                                <span>Kategori</span>
                                <strong>{{ $typeName }}</strong>
                            </div>

                            <div class="publication-meta-item">
                                <span>Jumlah Halaman</span>
                                <strong>{{ $pageCountLabel }}</strong>
                            </div>

                            <div class="publication-meta-item">
                                <span>Sumber</span>
                                <strong>{{ $sourceName }}</strong>
                            </div>
                        </div>

                        @if ($downloadUrl)
                            <div style="margin-top:20px;">
                                <a
                                    href="{{ $downloadUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="publication-button-primary"
                                    style="width:100%;"
                                >
                                    Unduh Publikasi
                                </a>
                            </div>
                        @endif
                    </section>

                    <section class="publication-panel">
                        <div class="publication-panel-label">
                            Kata Kunci
                        </div>

                        <div class="publication-tags">
                            @forelse ($tags as $tag)
                                <span>#{{ $tag->name }}</span>
                            @empty
                                <span>#RisetHukum</span>
                                <span>#LiterasiHukum</span>
                                <span>#KebijakanPublik</span>
                            @endforelse
                        </div>
                    </section>

                    <section class="publication-panel" id="aksi-publikasi">
                        <div class="publication-panel-label">
                            Aksi Publikasi
                        </div>

                        <div class="publication-action-list">
                            <button
                                type="button"
                                onclick="navigator.clipboard?.writeText(window.location.href)"
                            >
                                <span>Bagikan Publikasi</span>
                                <span>↗</span>
                            </button>

                            <a href="{{ $collaborationUrl }}">
                                <span>Gunakan untuk Diskusi</span>
                                <span>→</span>
                            </a>
                        </div>
                    </section>

                    <section class="publication-panel">
                        <div class="publication-panel-label">
                            Sitasi
                        </div>

                        <div class="publication-citation-box">
                            {{ $citationText }}
                        </div>

                        <button
                            type="button"
                            onclick="navigator.clipboard?.writeText(@js($citationText))"
                            class="publication-button-soft"
                            style="margin-top:14px;width:100%;cursor:pointer;"
                        >
                            Salin Sitasi
                        </button>
                    </section>
                </aside>
            </div>
        </div>
    </section>

    <x-shared.cta-collaboration
        eyebrow="Kolaborasi Publikasi"
        title="Kembangkan riset atau publikasi berikutnya bersama Edulaw."
        body="Gunakan publikasi ini sebagai awal diskusi untuk kajian, policy brief, kelas, atau diseminasi hukum yang lebih luas."
        :primary-url="$collaborationUrl"
        primary-label="Gunakan untuk Diskusi"
        :secondary-url="$indexUrl"
        secondary-label="Lihat Publikasi Lainnya"
    />
</main>
@endsection
