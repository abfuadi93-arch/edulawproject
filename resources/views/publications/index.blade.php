@extends('layouts.app')

@section('title', 'Riset dan Publikasi Hukum | Edulaw Project')
@section('meta_description', 'Jelajahi hasil riset, policy brief, laporan, dan publikasi hukum Edulaw Project yang menyajikan analisis berbasis bukti untuk kepentingan publik.')

@push('head')
    @php
        $publicationListSchemaItems = collect($publications->items())
            ->map(fn ($item): array => [
                'name' => $item->title,
                'url' => route('publications.show', $item->slug),
                'image' => $item->cover_image_url,
            ])
            ->all();
    @endphp
    @if ($publicationListSchemaItems !== [])
        <x-structured-data :data="\App\Support\StructuredData::itemList($publicationListSchemaItems, 'Riset dan Publikasi Hukum')" />
    @endif
@endpush

@section('content')
@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Carbon;

    $publicationPaginator = $publications ?? collect();

    $publicationItems = $publicationPaginator instanceof \Illuminate\Pagination\AbstractPaginator
        ? $publicationPaginator->getCollection()
        : collect($publicationPaginator);

    $typeCollection = $publicationTypes ?? $types ?? collect();

    $search = $search ?? request('q');
    $selectedType = $selectedType ?? request('type');
    $selectedYear = request('year');

    $totalPublications = $publicationPaginator instanceof \Illuminate\Pagination\AbstractPaginator
        ? $publicationPaginator->total()
        : $publicationItems->count();

    $totalDownloads = $totalDownloads ?? 0;

    $imageUrl = function (?string $path) {
        return edulaw_file_url($path);
    };

    $publicationTypeName = function ($publication) {
        return $publication?->type?->name
            ?? $publication?->publicationType?->name
            ?? $publication?->publication_type
            ?? $publication?->type
            ?? 'Publikasi';
    };

    $publicationTypeSlug = function ($type) {
        return is_string($type)
            ? Str::slug($type)
            : ($type->slug ?? Str::slug($type->name ?? ''));
    };

    $publicationAuthors = function ($publication) {
        if (isset($publication->authors) && $publication->authors->count()) {
            return $publication->authors->pluck('name')->filter()->join(', ');
        }

        return $publication->author_name
            ?? $publication->source_name
            ?? 'Edulaw Project';
    };

    $publicationAuthorProfiles = function ($publication) {
        return isset($publication->authors)
            ? $publication->authors
                ->filter(fn ($author) => $author->is_active !== false)
                ->sortBy(fn ($author) => $author->pivot?->author_order ?? 999)
                ->values()
            : collect();
    };

    $authorInitials = function (string $name): string {
        return Str::of($name)
            ->explode(' ')
            ->filter()
            ->map(fn ($part) => Str::substr($part, 0, 1))
            ->take(2)
            ->implode('') ?: 'E';
    };

    $publicationDate = function ($publication) {
        if (filled($publication->publication_date_text ?? null)) {
            return $publication->publication_date_text;
        }

        if (! $publication->published_at) {
            return 'Belum diketahui';
        }

        try {
            return $publication->published_at instanceof Carbon
                ? $publication->published_at->translatedFormat('F Y')
                : Carbon::parse($publication->published_at)->translatedFormat('F Y');
        } catch (\Throwable $e) {
            return (string) $publication->published_at;
        }
    };

    $publicationYear = function ($publication) {
        if (filled($publication->publication_year ?? null)) {
            return $publication->publication_year;
        }

        if (! empty($publication->published_at)) {
            try {
                return Carbon::parse($publication->published_at)->format('Y');
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    };

    $publicationExcerpt = function ($publication, int $limit = 260) {
        $text = $publication->excerpt ?: strip_tags($publication->description ?? '');

        return Str::limit($text ?: 'Publikasi Edulaw Project untuk mendukung literasi hukum, riset kebijakan, dan penguatan pengetahuan publik.', $limit);
    };

    $detailUrl = function ($publication) {
        return route('publications.show', $publication->slug);
    };

    $downloadUrl = function ($publication) {
        if (! empty($publication->pdf_file)) {
            return route('publications.download', $publication->slug);
        }

        if (! empty($publication->external_url)) {
            return $publication->external_url;
        }

        return null;
    };

    $pdfPreviewUrl = function ($publication) {
        if (empty($publication->pdf_file)) {
            return null;
        }

        return route('publications.preview', $publication->slug).'#page=1&toolbar=0&navpanes=0&scrollbar=0&view=FitH';
    };

    $availableYears = $publicationItems
        ->map(fn ($publication) => $publicationYear($publication))
        ->filter()
        ->unique()
        ->sortDesc()
        ->values();

    $fallbackPalettes = [
        ['from' => '#102A43', 'via' => '#1E5F74', 'to' => '#F4B942', 'accent' => '#F4B942'],
        ['from' => '#240046', 'via' => '#7B2CBF', 'to' => '#F72585', 'accent' => '#F72585'],
        ['from' => '#143601', 'via' => '#2D6A4F', 'to' => '#95D5B2', 'accent' => '#B7E4C7'],
        ['from' => '#3D0C11', 'via' => '#A4161A', 'to' => '#FFB703', 'accent' => '#FFB703'],
        ['from' => '#001219', 'via' => '#005F73', 'to' => '#94D2BD', 'accent' => '#94D2BD'],
        ['from' => '#03045E', 'via' => '#0077B6', 'to' => '#90E0EF', 'accent' => '#90E0EF'],
        ['from' => '#2B2D42', 'via' => '#8D99AE', 'to' => '#EF233C', 'accent' => '#EF233C'],
        ['from' => '#22223B', 'via' => '#4A4E69', 'to' => '#C9ADA7', 'accent' => '#C9ADA7'],
    ];

    $fallbackPalette = function ($publication, int $index = 0) use ($fallbackPalettes) {
        $seed = abs(crc32(
            ($publication->slug ?? '') . '|' .
            ($publication->title ?? '') . '|' .
            ($publication->id ?? $index)
        ));

        return $fallbackPalettes[$seed % count($fallbackPalettes)];
    };
@endphp

<style>
    .publication-repository {
        background: #f8fafc;
    }

    .publication-repository-layout {
        display: grid;
        grid-template-columns: 300px minmax(0, 1fr);
        gap: 28px;
        align-items: start;
    }

    .publication-filter-card,
    .publication-list-card,
    .publication-empty-card {
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }

    .publication-filter-card {
        position: sticky;
        top: 96px;
        border-radius: 28px;
        padding: 22px;
    }

    .publication-filter-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
    }

    .publication-filter-header h3 {
        color: #0f172a;
        font-size: 18px;
        font-weight: 900;
        letter-spacing: -.02em;
    }

    .publication-filter-header a {
        color: #64748b;
        font-size: 12px;
        font-weight: 900;
        text-decoration: none;
    }

    .publication-form-group {
        display: grid;
        gap: 8px;
        margin-bottom: 16px;
    }

    .publication-form-group label {
        color: #64748b;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .publication-form-group input,
    .publication-form-group select {
        min-height: 46px;
        width: 100%;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #f8fafc;
        padding: 0 14px;
        color: #334155;
        font-size: 13px;
        font-weight: 700;
        outline: none;
        transition: .2s ease;
    }

    .publication-form-group input:focus,
    .publication-form-group select:focus {
        border-color: #24457f;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(36, 69, 127, .08);
    }

    .publication-filter-button {
        min-height: 46px;
        width: 100%;
        border: 0;
        border-radius: 14px;
        background: #0F2868;
        color: #ffffff;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        transition: .2s ease;
    }

    .publication-filter-button:hover {
        background: #071A3D;
        transform: translateY(-1px);
    }

    .publication-list {
        display: grid;
        gap: 16px;
    }

    .publication-list-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        color: #64748b;
        font-size: 13px;
        font-weight: 800;
    }

    .publication-list-summary strong {
        color: #0f172a;
    }

    .publication-count-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        background: #ffffff;
        padding: 9px 13px;
        color: #64748b;
        font-size: 12px;
        font-weight: 900;
    }

    .publication-count-pill span {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #0F2868;
    }

    .publication-list-card {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 220px;
        gap: 22px;
        border-radius: 28px;
        padding: 22px;
        transition: .25s ease;
    }

    .publication-list-card:hover {
        transform: translateY(-3px);
        border-color: rgba(15, 40, 104, .28);
        box-shadow: 0 18px 42px rgba(15, 23, 42, .08);
    }

    .publication-category {
        display: inline-flex;
        width: fit-content;
        border-radius: 999px;
        background: #eaf0fb;
        color: #0F2868;
        padding: 6px 10px;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .10em;
        text-transform: uppercase;
    }

    .publication-featured-badge {
        display: inline-flex;
        width: fit-content;
        border: 1px solid rgba(245, 185, 67, .35);
        border-radius: 999px;
        background: #fff3cf;
        color: #1f3c69;
        padding: 6px 10px;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .10em;
        text-transform: uppercase;
    }

    .publication-content h2 {
        margin-top: 12px;
        font-size: 22px;
        line-height: 1.22;
        font-weight: 950;
        letter-spacing: -.03em;
    }

    .publication-content h2 a {
        color: #0f172a;
        text-decoration: none;
        transition: .2s ease;
    }

    .publication-content h2 a:hover {
        color: #0F2868;
    }

    .publication-meta {
        margin-top: 9px;
        color: #64748b;
        font-size: 13px;
        font-weight: 800;
    }

    .publication-content p {
        margin-top: 12px;
        color: #475569;
        font-size: 14px;
        line-height: 1.7;
    }

    .publication-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 9px;
        margin-top: 16px;
    }

    .publication-read-more,
    .publication-download-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 12px;
        font-weight: 900;
        text-decoration: none;
        transition: .2s ease;
    }

    .publication-read-more {
        background: #0F2868;
        color: #ffffff;
    }

    .publication-read-more:hover {
        background: #071A3D;
        transform: translateY(-1px);
    }

    .publication-download-link {
        border: 1px solid rgba(15, 40, 104, .2);
        color: #0F2868;
        background: #ffffff;
    }

    .publication-download-link:hover {
        background: #f8fafc;
        border-color: rgba(15, 40, 104, .35);
    }

    .publication-thumb {
        min-height: 178px;
        overflow: hidden;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,.65);
    }

    .publication-thumb iframe {
        display: block;
        width: 100%;
        height: 100%;
        min-height: 178px;
        border: 0;
        background: #f8fafc;
        pointer-events: none;
    }

    .publication-cover-image {
        display: block;
        width: 100%;
        height: 100%;
        min-height: 178px;
        object-fit: cover;
        background: #ffffff;
    }

    .publication-thumb-placeholder {
        position: relative;
        isolation: isolate;
        display: flex;
        min-height: 178px;
        height: 100%;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
        padding: 18px;
        color: #ffffff;
    }

    .publication-thumb-placeholder::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        background: linear-gradient(120deg, rgba(255,255,255,.14), transparent 40%, rgba(0,0,0,.28));
    }

    .publication-thumb-placeholder .line {
        width: 48px;
        height: 7px;
        border-radius: 999px;
        background: currentColor;
        opacity: .88;
    }

    .publication-thumb-placeholder strong {
        display: block;
        margin-top: 16px;
        font-size: 15px;
        line-height: 1.18;
        font-weight: 950;
        letter-spacing: -.02em;
    }

    .publication-thumb-placeholder small {
        display: block;
        margin-top: 8px;
        color: rgba(255,255,255,.72);
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .publication-empty-card {
        border-style: dashed;
        border-radius: 28px;
        padding: 42px;
        text-align: center;
    }

    .publication-empty-card h3 {
        font-size: 22px;
        font-weight: 950;
        color: #0f172a;
    }

    .publication-empty-card p {
        margin-top: 10px;
        color: #64748b;
        font-size: 14px;
        line-height: 1.7;
    }

    .publication-pagination {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        border-top: 1px solid #e2e8f0;
        padding-top: 18px;
        color: #64748b;
        font-size: 13px;
        font-weight: 800;
    }

    @media (max-width: 980px) {
        .publication-repository-layout {
            grid-template-columns: 1fr;
        }

        .publication-filter-card {
            position: static;
        }

        .publication-list-card {
            grid-template-columns: 1fr;
        }

        .publication-thumb {
            order: -1;
            min-height: 260px;
        }

        .publication-thumb iframe,
        .publication-cover-image,
        .publication-thumb-placeholder {
            min-height: 260px;
        }
    }

    @media (max-width: 640px) {
        .publication-content h2 {
            font-size: 19px;
        }

        .publication-list-summary {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>

<main class="publication-repository">
    <x-shared.page-header
        title="Riset & Publikasi"
        :compact="true"
        eyebrow="Kanal Riset & Publikasi"
        :channel-header="true"
        grid-class="gap-5 px-5 py-7 sm:w-full sm:px-6 lg:min-h-[240px] lg:grid-cols-2 lg:items-center lg:px-8 lg:py-8"
        description="Repository kajian, policy brief, naskah akademik, working paper, research report, dan buku digital untuk memperkuat literasi hukum dan kebijakan publik."
        background-image="https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=1800&q=85"
        background-alt="Riset dan publikasi hukum Edulaw Project"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Riset & Publikasi'],
        ]"
    />

    <section class="py-12 lg:py-16">
        <div class="publication-repository-container mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <div class="publication-repository-layout">
                <aside>
                    <div class="publication-filter-card">
                        <div class="publication-filter-header">
                            <h3>Filter</h3>
                            <a href="{{ route('publications.index') }}">Hapus</a>
                        </div>

                        <form method="GET" action="{{ route('publications.index') }}">
                            <div class="publication-form-group">
                                <label for="q">Kata Kunci</label>
                                <input
                                    id="q"
                                    type="text"
                                    name="q"
                                    value="{{ request('q') }}"
                                    placeholder="Cari publikasi..."
                                >
                            </div>

                            <div class="publication-form-group">
                                <label for="type">Tipe Publikasi</label>
                                <select id="type" name="type">
                                    <option value="">Semua Tipe</option>

                                    @foreach ($typeCollection as $type)
                                        @php
                                            $typeName = is_string($type) ? $type : ($type->name ?? '');
                                            $typeSlug = $publicationTypeSlug($type);
                                        @endphp

                                        <option value="{{ $typeSlug }}" @selected($selectedType === $typeSlug)>
                                            {{ $typeName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="publication-form-group">
                                <label for="year">Tahun Publikasi</label>
                                <select id="year" name="year">
                                    <option value="">Semua Tahun</option>

                                    @foreach ($availableYears as $year)
                                        <option value="{{ $year }}" @selected((string) $selectedYear === (string) $year)>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="publication-filter-button">
                                Terapkan Filter
                            </button>
                        </form>
                    </div>
                </aside>

                <main class="publication-list">
                    <div class="publication-list-summary">
                        <p>
                            Menampilkan
                            <strong>
                                @if ($publicationPaginator instanceof \Illuminate\Pagination\AbstractPaginator)
                                    {{ $publicationPaginator->firstItem() ?? 0 }}–{{ $publicationPaginator->lastItem() ?? 0 }}
                                @else
                                    {{ $publicationItems->count() }}
                                @endif
                            </strong>
                            dari
                            <strong>{{ $totalPublications }}</strong>
                            publikasi
                        </p>

                        <div class="publication-count-pill">
                            <span></span>
                            {{ number_format($totalPublications, 0, ',', '.') }} dokumen tersedia
                        </div>
                    </div>

                    @forelse ($publicationItems as $publicationIndex => $publication)
                        @php
                            $coverImage = $imageUrl($publication->cover_image ?? null);
                            $previewUrl = $pdfPreviewUrl($publication);
                            $currentDownloadUrl = $downloadUrl($publication);
                            $palette = $fallbackPalette($publication, $publicationIndex);
                            $authorProfiles = $publicationAuthorProfiles($publication);
                            $primaryAuthor = $authorProfiles->first();
                        @endphp

                        <article class="publication-list-card">
                            <div class="publication-content">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="publication-category">
                                        {{ $publicationTypeName($publication) }}
                                    </span>

                                    @if (! empty($publication->featured))
                                        <span class="publication-featured-badge">
                                            Pilihan
                                        </span>
                                    @endif
                                </div>

                                <h2>
                                    <a href="{{ $detailUrl($publication) }}">
                                        {{ $publication->title }}
                                    </a>
                                </h2>

                                <div class="publication-meta">
                                    <a
                                        href="{{ $primaryAuthor ? route('profiles.show', $primaryAuthor->slug) : $detailUrl($publication) }}"
                                        class="inline-flex max-w-full items-center gap-2 align-middle transition hover:text-brand-navy"
                                    >
                                        @if ($authorProfiles->isNotEmpty())
                                            <span class="inline-flex shrink-0 -space-x-1.5">
                                                @foreach ($authorProfiles->take(3) as $author)
                                                    @if ($author->photo_url)
                                                        <img
                                                            src="{{ $author->photo_url }}"
                                                            alt="Foto profil {{ $author->name }}"
                                                            class="h-6 w-6 rounded-full border border-white object-cover shadow-sm"
                                                            loading="lazy"
                                                        >
                                                    @else
                                                        <span class="grid h-6 w-6 place-items-center rounded-full border border-white bg-brand-navy text-[9px] font-black text-white shadow-sm">
                                                            {{ $authorInitials($author->name) }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </span>
                                        @endif

                                        <span class="min-w-0">
                                            {{ $publicationAuthors($publication) }}
                                        </span>
                                    </a>
                                    ·
                                    {{ $publicationDate($publication) }}
                                    ·
                                    {{ $publication->page_count ? $publication->page_count . ' halaman' : 'Dokumen digital' }}
                                </div>

                                <p>
                                    {{ $publicationExcerpt($publication, 270) }}
                                </p>

                                <div class="publication-actions">
                                    <a href="{{ $detailUrl($publication) }}" class="publication-read-more">
                                        Lihat Selengkapnya →
                                    </a>

                                    @if ($currentDownloadUrl)
                                        <a
                                            href="{{ $currentDownloadUrl }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="publication-download-link"
                                        >
                                            Unduh Dokumen
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="publication-thumb">
                                @if ($coverImage)
                                    <img
                                        src="{{ $coverImage }}"
                                        alt="{{ $publication->title }}"
                                        class="publication-cover-image"
                                    >
                                @elseif ($previewUrl)
                                    <iframe
                                        src="{{ $previewUrl }}"
                                        title="Preview PDF {{ $publication->title }}"
                                        loading="lazy"
                                    ></iframe>
                                @else
                                    <div
                                        class="publication-thumb-placeholder"
                                        style="background: linear-gradient(135deg, {{ $palette['from'] }} 0%, {{ $palette['via'] }} 50%, {{ $palette['to'] }} 100%);"
                                    >
                                        <div>
                                            <span class="line" style="color: {{ $palette['accent'] }}"></span>
                                            <strong>{{ $publication->title }}</strong>
                                            <small>{{ $publicationTypeName($publication) }}</small>
                                        </div>

                                            <small>{{ $publicationDate($publication) }}</small>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="publication-empty-card">
                            <h3>Publikasi belum ditemukan</h3>
                            <p>
                                Coba gunakan kata kunci lain, pilih tipe publikasi berbeda, atau hapus filter untuk melihat seluruh koleksi.
                            </p>
                        </div>
                    @endforelse

                    @if ($publicationPaginator instanceof \Illuminate\Pagination\AbstractPaginator)
                        <div class="publication-pagination">
                            <p>
                                Menampilkan {{ $publicationPaginator->firstItem() ?? 0 }}–{{ $publicationPaginator->lastItem() ?? 0 }}
                                dari {{ $publicationPaginator->total() }} publikasi
                            </p>

                            {{ $publicationPaginator->withQueryString()->links() }}
                        </div>
                    @endif
                </main>
            </div>
        </div>
    </section>

    <x-shared.cta-collaboration
        eyebrow="Kolaborasi Riset"
        title="Butuh kajian, policy brief, atau publikasi hukum kolaboratif?"
        body="Edulaw Project dapat menjadi mitra penyusunan riset, publikasi, dan diseminasi pengetahuan hukum yang mudah diakses."
        primary-label="Ajukan Kolaborasi"
        :secondary-url="route('publications.index')"
        secondary-label="Jelajahi Publikasi"
    />
</main>
@endsection
