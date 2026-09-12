@extends('layouts.app')

@section('title', 'Riset dan Publikasi Hukum | Edulaw Project')
@section('meta_description', 'Jelajahi hasil riset, policy brief, laporan, dan publikasi hukum Edulaw Project yang menyajikan analisis berbasis bukti untuk kepentingan publik.')

@push('head')
    @php
        $publicationListSchemaItems = collect($publications->items())
            ->filter(fn ($item): bool => \App\Support\PublicContentQuality::publication($item))
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

@push('styles')
<style>
    [data-publication-page] .publication-feature { height: auto; min-height: 365px; }
    [data-publication-page] .publication-feature-cover { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    [data-publication-page] .publication-feature-fallback { width: 200px; height: 264px; }
    [data-publication-page] .publication-feature-fallback:not([hidden]),
    [data-publication-page] .repository-cover > span:not([hidden]) { display: block; }
    [data-publication-page] [data-repository-card] { align-self: stretch; }
    [data-publication-page] dl[aria-label="Statistik riset dan publikasi"] { width: 100%; max-width: 340px; justify-self: end; }
    [data-publication-page] dl[aria-label="Statistik riset dan publikasi"] > div { padding: 10px 14px; }
    [data-publication-page] .publication-avatar-fallback:not([hidden]) { display: block; }
    [data-publication-page] .publication-toolbar > input[type="search"] { width: 100%; }
    @media (max-width: 1023px) {
        [data-publication-page] dl[aria-label="Statistik riset dan publikasi"] { justify-self: start; }
    }
    @media (max-width: 767px) {
        [data-publication-page] .publication-feature-fallback { width: 150px; height: 210px; }
    }
    @media (max-width: 359px) {
        [data-publication-page] [data-repository-card] { flex-direction: column; }
        [data-publication-page] .repository-cover { min-height: 128px; }
    }
</style>
@endpush

@section('content')
@php
    use Illuminate\Pagination\AbstractPaginator;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $publicationPaginator = $publications ?? collect();
    $publicationItems = $publicationPaginator instanceof AbstractPaginator
        ? $publicationPaginator->getCollection()
        : collect($publicationPaginator);
    $typeCollection = collect($publicationTypes ?? $types ?? []);
    $search = $search ?? request('q');
    $selectedType = $selectedType ?? request('type');
    $selectedView = request('view') === 'list' ? 'list' : 'grid';
    $totalPublications = $publicationPaginator instanceof AbstractPaginator
        ? $publicationPaginator->total()
        : $publicationItems->count();

    $publicationTypeName = fn ($publication): string => $publication?->type?->name
        ?? $publication?->publicationType?->name
        ?? $publication?->publication_type
        ?? 'Publikasi';

    $publicationTypeSlug = fn ($type): string => is_string($type)
        ? Str::slug($type)
        : ($type->slug ?? Str::slug($type->name ?? ''));

    $publicationAuthors = function ($publication): string {
        if (isset($publication->authors) && $publication->authors->count()) {
            return $publication->authors->pluck('name')->filter()->join(', ');
        }

        return $publication->author_name
            ?? $publication->source_name
            ?? 'Edulaw Project';
    };

    $publicationAuthorProfiles = fn ($publication) => isset($publication->authors)
        ? $publication->authors
            ->filter(fn ($author) => $author->is_active !== false)
            ->sortBy(fn ($author) => $author->pivot?->author_order ?? 999)
            ->values()
        : collect();

    $authorInitials = fn (string $name): string => Str::of($name)
        ->explode(' ')
        ->filter()
        ->map(fn ($part) => Str::substr($part, 0, 1))
        ->take(2)
        ->implode('') ?: 'E';

    $publicationDate = function ($publication): string {
        if (filled($publication->publication_date_text ?? null)) {
            return $publication->publication_date_text;
        }

        if (! $publication->published_at) {
            return 'Belum diketahui';
        }

        try {
            return $publication->published_at instanceof Carbon
                ? $publication->published_at->translatedFormat('d M Y')
                : Carbon::parse($publication->published_at)->translatedFormat('d M Y');
        } catch (Throwable) {
            return (string) $publication->published_at;
        }
    };

    $publicationExcerpt = function ($publication, int $limit = 200): string {
        $text = Str::squish(strip_tags((string) ($publication->excerpt ?: ($publication->description ?? ''))));

        return Str::limit(
            $text ?: 'Publikasi Edulaw Project untuk mendukung literasi hukum, riset kebijakan, dan penguatan pengetahuan publik.',
            $limit,
        );
    };

    $downloadUrl = function ($publication): ?string {
        if (! empty($publication->pdf_file)) {
            return route('publications.download', $publication->slug);
        }

        return filled($publication->external_url ?? null) ? $publication->external_url : null;
    };

    $featured = $featuredPublication ?? null;
    if ($featured && blank($search) && blank($selectedType) && (int) request('page', 1) === 1) {
        $publicationItems = $publicationItems->reject(fn ($item) => $item->id === $featured->id)
            ->concat($publicationItems->filter(fn ($item) => $item->id === $featured->id));
    }
@endphp

<main data-publication-page class="overflow-x-clip bg-[#f7f8fa] text-brand-ink">
    <x-shared.primary-hero
        title="Riset & Publikasi"
        eyebrow="Kanal Riset & Publikasi"
        description="Repository kajian, policy brief, naskah akademik, working paper, research report, dan buku digital untuk memperkuat literasi hukum dan kebijakan publik."
        background-image="https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=1800&q=85"
        background-alt="Riset dan publikasi hukum Edulaw Project"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Riset & Publikasi'],
        ]"
        :stats="[
            ['value' => number_format($totalPublications, 0, ',', '.'), 'label' => 'Dokumen Terbit'],
            ['value' => number_format($typeCollection->count(), 0, ',', '.'), 'label' => 'Jenis Publikasi'],
        ]"
        panel-label="Statistik riset dan publikasi"
        uniform-height
    />

    @if ($featured)
        @php
            $featuredCover = app(\App\Services\PdfCoverGenerator::class)->displayCover($featured->cover_image, $featured->pdf_file, $featured->slug);
            $featuredDownloadUrl = $downloadUrl($featured);
        @endphp
        <section class="channel-section bg-white" aria-labelledby="featured-publication-heading">
            <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
                <p class="channel-feature-label"><span class="text-[#D99A25]" aria-hidden="true">★</span> Publikasi Utama</p>

                <article class="publication-feature channel-feature-card overflow-hidden rounded-[14px] border border-[#dbe2ea] bg-white" data-channel-feature-card>
                    <div class="grid h-full md:grid-cols-[minmax(15rem,.8fr)_minmax(0,1.7fr)] lg:grid-cols-[365px_minmax(0,1fr)]">
                        <div class="relative flex min-h-[300px] items-center justify-center sm:min-h-[365px] lg:min-h-0">
                            <a href="{{ route('publications.show', $featured->slug) }}" class="relative flex size-full min-h-[300px] items-center justify-center bg-linear-to-br from-[#e9eef4] to-[#dbe5ed] overflow-hidden focus-visible:outline-2 focus-visible:outline-offset-[-3px] focus-visible:outline-brand-navy sm:min-h-[365px] lg:min-h-0" aria-label="Baca {{ $featured->title }}">
                                @if ($featuredCover)
                                    <img src="{{ $featuredCover }}" alt="Sampul {{ $featured->title }}" class="publication-feature-cover object-cover" fetchpriority="high" onerror="this.style.display='none';this.nextElementSibling.hidden=false">
                                @endif
                                <span @if ($featuredCover) hidden @endif class="publication-feature-fallback rounded-lg border border-slate-200 bg-white p-5 text-center text-sm text-slate-400">Pratinjau PDF belum tersedia</span>
                            </a>
                        </div>
                        <div class="flex min-w-0 flex-col justify-center p-6 sm:p-8 lg:p-6">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="channel-feature-badge bg-[#fff4d7] text-[#80500a]">{{ $publicationTypeName($featured) }}</span>
                                <span class="channel-feature-badge bg-emerald-50 text-emerald-700">Pilihan Riset</span>
                            </div>
                            <h2 id="featured-publication-heading" class="channel-feature-title">
                                <a href="{{ route('publications.show', $featured->slug) }}" class="rounded-sm transition hover:text-brand-navy focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy">{{ $featured->title }}</a>
                            </h2>
                            <p class="channel-feature-summary line-clamp-2">{{ $publicationExcerpt($featured, 280) }}</p>
                            <div class="channel-feature-meta border-y border-slate-100 py-3">
                                <dl class="grid gap-3 sm:grid-cols-2 {{ $featured->page_count ? 'lg:grid-cols-3' : '' }}">
                                    <div>
                                        <dt class="text-[11px] font-black uppercase tracking-[0.11em] text-slate-500">Penulis</dt>
                                        <dd class="mt-1 text-sm font-black text-brand-ink [overflow-wrap:anywhere]">{{ $publicationAuthors($featured) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-[11px] font-black uppercase tracking-[0.11em] text-slate-500">Tanggal Terbit</dt>
                                        <dd class="mt-1 text-sm font-black text-brand-ink"><time datetime="{{ optional($featured->published_at)->toDateString() }}">{{ $publicationDate($featured) }}</time></dd>
                                    </div>
                                    @if ($featured->page_count)
                                        <div>
                                            <dt class="text-[11px] font-black uppercase tracking-[0.11em] text-slate-500">Halaman</dt>
                                            <dd class="mt-1 text-sm font-black text-brand-ink">{{ $featured->page_count }}</dd>
                                        </div>
                                    @endif
                                </dl>
                                <div class="channel-feature-actions">
                                    <a href="{{ route('publications.show', $featured->slug) }}" class="channel-feature-primary-action">Baca Publikasi <span aria-hidden="true">→</span></a>
                                    @if ($featuredDownloadUrl)
                                        <a href="{{ $featuredDownloadUrl }}" target="_blank" rel="noopener noreferrer" class="channel-feature-secondary-action">Unduh Dokumen</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    @endif

    <section id="publication-catalog" class="channel-section" aria-labelledby="publication-catalog-heading">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-brand-navy">Repository Publikasi</p>
                    <h2 id="publication-catalog-heading" class="mt-1 font-display text-2xl font-black text-brand-navy sm:text-3xl">Jelajahi Riset & Publikasi</h2>
                    <p class="mt-1.5 max-w-3xl text-base leading-7 text-slate-600">Temukan dokumen berdasarkan judul, topik, dan jenis publikasi yang dibutuhkan.</p>
                </div>
                <p class="text-sm font-bold text-slate-500"><strong class="text-brand-navy">{{ number_format($totalPublications, 0, ',', '.') }}</strong> dokumen tersedia</p>
            </div>

            <form method="GET" action="{{ route('publications.index') }}#publication-catalog" class="publication-toolbar mt-5 grid gap-2 rounded-xl border border-slate-200 bg-white p-3 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_180px_auto_auto]">
                <input type="hidden" name="view" value="{{ $selectedView }}">
                <label class="sr-only" for="publication-search">Cari publikasi</label>
                <input id="publication-search" type="search" name="q" value="{{ $search }}" placeholder="Cari judul, topik, atau kata kunci..." class="h-11 min-w-0 rounded-lg border border-slate-200 bg-[#f8fafc] px-4 text-sm font-medium text-brand-ink outline-none placeholder:text-slate-400 focus:border-brand-navy focus:bg-white focus:ring-2 focus:ring-brand-navy/10">

                <label class="sr-only" for="publication-type">Jenis publikasi</label>
                <select id="publication-type" name="type" class="h-11 min-w-0 rounded-lg border border-slate-200 bg-[#f8fafc] px-3 text-sm font-bold text-brand-navy outline-none focus:border-brand-navy focus:ring-2 focus:ring-brand-navy/10">
                    <option value="">Semua Jenis</option>
                    @foreach ($typeCollection as $type)
                        @php
                            $typeName = is_string($type) ? $type : ($type->name ?? '');
                            $typeSlug = $publicationTypeSlug($type);
                        @endphp
                        <option value="{{ $typeSlug }}" @selected($selectedType === $typeSlug)>{{ $typeName }}</option>
                    @endforeach
                </select>

                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-navy px-5 text-sm font-black text-white transition hover:bg-brand-ink">Terapkan</button>

                <div class="flex h-11 items-center rounded-lg border border-slate-200 bg-[#f8fafc] p-1" aria-label="Pilihan tampilan publikasi">
                    <a href="{{ route('publications.index', array_filter(['q' => $search, 'type' => $selectedType, 'view' => 'grid'])) }}#publication-catalog" aria-label="Tampilan grid" aria-current="{{ $selectedView === 'grid' ? 'true' : 'false' }}" class="grid h-9 w-9 place-items-center rounded-md {{ $selectedView === 'grid' ? 'bg-brand-navy text-white' : 'text-slate-500 hover:text-brand-navy' }}">
                        <svg viewBox="0 0 20 20" class="h-4 w-4" fill="currentColor" aria-hidden="true"><path d="M2.5 2.5h6v6h-6v-6Zm9 0h6v6h-6v-6Zm-9 9h6v6h-6v-6Zm9 0h6v6h-6v-6Z"/></svg>
                    </a>
                    <a href="{{ route('publications.index', array_filter(['q' => $search, 'type' => $selectedType, 'view' => 'list'])) }}#publication-catalog" aria-label="Tampilan daftar" aria-current="{{ $selectedView === 'list' ? 'true' : 'false' }}" class="grid h-9 w-9 place-items-center rounded-md {{ $selectedView === 'list' ? 'bg-brand-navy text-white' : 'text-slate-500 hover:text-brand-navy' }}">
                        <svg viewBox="0 0 20 20" class="h-4 w-4" fill="currentColor" aria-hidden="true"><path d="M2.5 3.5h3v3h-3v-3Zm5 0h10v3h-10v-3Zm-5 5h3v3h-3v-3Zm5 0h10v3h-10v-3Zm-5 5h3v3h-3v-3Zm5 0h10v3h-10v-3Z"/></svg>
                    </a>
                </div>

                @if (filled($search) || filled($selectedType))
                    <a href="{{ route('publications.index') }}#publication-catalog" class="inline-flex h-11 items-center justify-center rounded-lg px-4 text-sm font-black text-slate-500 transition hover:bg-slate-50 hover:text-brand-navy">Atur Ulang</a>
                @endif
            </form>

            @if ($typeCollection->isNotEmpty())
                <nav aria-label="Jenis publikasi" class="mt-3 flex flex-wrap gap-2">
                    <a href="{{ route('publications.index', array_filter(['q' => $search, 'view' => $selectedView])) }}#publication-catalog" class="inline-flex min-h-8 shrink-0 items-center rounded-full px-3 text-xs font-bold {{ blank($selectedType) ? 'bg-brand-navy text-white' : 'bg-slate-100 text-brand-navy' }}">Semua</a>
                    @foreach ($typeCollection as $type)
                        @php
                            $typeName = is_string($type) ? $type : ($type->name ?? '');
                            $typeSlug = $publicationTypeSlug($type);
                        @endphp
                        <a href="{{ route('publications.index', array_filter(['q' => $search, 'type' => $typeSlug, 'view' => $selectedView])) }}#publication-catalog" class="inline-flex min-h-8 shrink-0 items-center rounded-full px-3 text-xs font-bold {{ $selectedType === $typeSlug ? 'bg-brand-navy text-white' : 'bg-slate-100 text-brand-navy' }}">{{ $typeName }}</a>
                    @endforeach
                </nav>
            @endif

            <div class="mt-8 {{ $selectedView === 'grid' ? 'grid gap-6 lg:grid-cols-2' : 'space-y-4' }}">
                @forelse ($publicationItems as $publicationIndex => $publication)
                    @php
                        $coverImage = app(\App\Services\PdfCoverGenerator::class)->displayCover($publication->cover_image, $publication->pdf_file, $publication->slug);
                        $currentDownloadUrl = $downloadUrl($publication);
                        $authorProfiles = $publicationAuthorProfiles($publication);
                    @endphp

                    <article data-repository-card class="group flex min-w-0 items-start overflow-hidden rounded-xl border border-slate-200 bg-white transition duration-200 hover:-translate-y-px hover:border-slate-300 hover:shadow-sm">
                        <a href="{{ route('publications.show', $publication->slug) }}" class="repository-cover relative block w-[90px] shrink-0 self-stretch overflow-hidden bg-slate-100 sm:w-[120px]">
                            @if ($coverImage)
                                <img src="{{ $coverImage }}" alt="Sampul {{ $publication->title }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover" onerror="this.style.display='none';this.nextElementSibling.hidden=false">
                            @endif
                            <span @if ($coverImage) hidden @endif class="aspect-[210/297] rounded-lg border border-slate-200 bg-white p-3 text-center text-xs text-slate-400">Pratinjau PDF belum tersedia</span>
                        </a>
                        <div class="min-w-0 flex-1 p-4 sm:p-5 lg:p-6">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="rounded-full bg-brand-navy/8 px-2 py-1 text-[10px] font-black uppercase tracking-wide text-brand-navy">{{ $publicationTypeName($publication) }}</span>
                                @if ($publication->featured)
                                    <span class="rounded-full bg-[#fff1c9] px-2 py-1 text-[10px] font-bold text-[#875b12]">Pilihan</span>
                                @endif
                            </div>
                            <h3 class="mt-2 text-base font-black leading-snug text-brand-ink sm:text-lg [overflow-wrap:anywhere]">
                                <a href="{{ route('publications.show', $publication->slug) }}">{{ $publication->title }}</a>
                            </h3>
                            <div class="mt-3 flex items-center gap-2 text-xs font-semibold text-slate-600">
                                @php $primaryAuthor = $authorProfiles->first(); @endphp
                                @if ($primaryAuthor?->photo_url)
                                    <img src="{{ $primaryAuthor->photo_url }}" alt="" width="24" height="24" class="size-6 shrink-0 rounded-full object-cover" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.hidden=false">
                                    <span hidden class="publication-avatar-fallback size-6 shrink-0 rounded-full bg-slate-100 text-center text-[9px] leading-6 text-brand-navy">{{ $authorInitials($publicationAuthors($publication)) }}</span>
                                @else
                                    <span class="grid size-6 shrink-0 place-items-center rounded-full bg-slate-100 text-[9px] font-bold text-brand-navy" aria-hidden="true">{{ $authorInitials($publicationAuthors($publication)) }}</span>
                                @endif
                                <span class="min-w-0 [overflow-wrap:anywhere]">{{ $publicationAuthors($publication) }}</span>
                            </div>
                            <time class="mt-1.5 block text-xs text-slate-500" datetime="{{ optional($publication->published_at)->toDateString() }}">{{ $publicationDate($publication) }}</time>
                            <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2">
                                <a href="{{ route('publications.show', $publication->slug) }}" class="text-sm font-bold text-brand-navy">Baca Publikasi →</a>
                                @if ($currentDownloadUrl)
                                    <a href="{{ $currentDownloadUrl }}" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-slate-600 hover:text-brand-navy">Unduh PDF</a>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-[14px] border border-dashed border-slate-300 bg-white px-6 py-10 text-center">
                        <h3 class="font-display text-xl font-black text-brand-navy">Publikasi belum ditemukan</h3>
                        <p class="mx-auto mt-2 max-w-lg text-base leading-7 text-slate-600">Coba gunakan kata kunci lain, pilih jenis publikasi berbeda, atau hapus filter untuk melihat seluruh koleksi.</p>
                        @if (filled($search) || filled($selectedType))
                            <a href="{{ route('publications.index') }}#publication-catalog" class="mt-4 inline-flex min-h-11 items-center rounded-lg bg-brand-navy px-5 text-sm font-black text-white">Hapus Filter</a>
                        @endif
                    </div>
                @endforelse
            </div>

            @if ($publicationPaginator instanceof AbstractPaginator && $publicationPaginator->hasPages())
                <div class="mt-8 border-t border-slate-200 pt-6">
                    <x-shared.pagination :paginator="$publicationPaginator" fragment="publication-catalog" label="Navigasi halaman riset dan publikasi" />
                </div>
            @endif
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
