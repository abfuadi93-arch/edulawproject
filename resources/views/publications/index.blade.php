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

    $fallbackPalettes = [
        ['from' => '#001b36', 'via' => '#173f62', 'to' => '#28557a', 'overlay' => 'rgba(0, 27, 54, .84)'],
        ['from' => '#155e53', 'via' => '#236f65', 'to' => '#3b8275', 'overlay' => 'rgba(21, 94, 83, .84)'],
        ['from' => '#765b32', 'via' => '#98764a', 'to' => '#bd9660', 'overlay' => 'rgba(102, 76, 38, .80)'],
        ['from' => '#5a1f35', 'via' => '#7f3047', 'to' => '#b4535f', 'overlay' => 'rgba(90, 31, 53, .84)'],
    ];

    $fallbackPalette = fn ($publication, int $index = 0): array => $fallbackPalettes[$index % count($fallbackPalettes)];

    $featured = $featuredPublication ?? null;
@endphp

<main class="overflow-x-clip bg-[#f7f8fa] text-brand-ink">
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
        :highlights="[
            'Riset berbasis bukti',
            'Publikasi untuk kepentingan publik',
            'Pengetahuan yang dapat dirujuk',
        ]"
        :stats="[
            ['value' => number_format($totalPublications, 0, ',', '.'), 'label' => 'Dokumen Terbit'],
            ['value' => number_format($typeCollection->count(), 0, ',', '.'), 'label' => 'Jenis Publikasi'],
        ]"
        panel-label="Statistik riset dan publikasi"
    />

    @if ($featured)
        @php
            $featuredCover = edulaw_file_url($featured->cover_image ?? null);
            $featuredDownloadUrl = $downloadUrl($featured);
            $featuredPalette = $fallbackPalette($featured);
        @endphp
        <section class="bg-white py-9 sm:py-10 lg:py-11" aria-labelledby="featured-publication-heading">
            <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
                <p class="mb-4 text-[11px] font-extrabold uppercase tracking-[0.16em] text-brand-navy"><span class="mr-1 text-brand-amber" aria-hidden="true">★</span> Publikasi Utama</p>

                <article class="grid overflow-hidden rounded-[14px] bg-[#f7f8fa] lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
                    <a href="{{ route('publications.show', $featured->slug) }}" class="group grid min-h-[310px] place-items-center bg-[#e9efed] p-6 focus-visible:outline-2 focus-visible:outline-offset-[-3px] focus-visible:outline-brand-amber sm:min-h-[390px] sm:p-8">
                        <span class="relative flex aspect-[1/1.34] w-[72%] max-w-[270px] flex-col justify-between overflow-hidden rounded-md p-6 text-white shadow-2xl shadow-slate-900/20 transition duration-300 group-hover:-translate-y-1" style="background: linear-gradient(155deg, {{ $featuredPalette['from'] }}, {{ $featuredPalette['via'] }} 68%, {{ $featuredPalette['to'] }});">
                            @if ($featuredCover)
                                <img src="{{ $featuredCover }}" alt="Sampul {{ $featured->title }}" class="absolute inset-0 size-full object-cover" fetchpriority="high">
                            @endif
                            <span class="absolute inset-0" style="background: linear-gradient(155deg, {{ $featuredPalette['overlay'] }}, {{ $featuredPalette['from'] }} 70%, {{ $featuredPalette['to'] }});"></span>
                            <span class="absolute -right-12 -top-12 size-40 rounded-full border border-white/10"></span>
                            <span class="absolute -bottom-16 -left-14 size-48 rounded-full border border-white/10"></span>

                            <span class="relative">
                                <span class="block text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#efc66b]">{{ $publicationTypeName($featured) }}</span>
                                <span class="mt-8 line-clamp-6 block font-display text-xl font-black leading-snug text-white sm:text-2xl">{{ $featured->title }}</span>
                            </span>
                            <span class="relative text-[11px] font-bold uppercase tracking-[0.14em] text-white/60">Edulaw Project</span>
                        </span>
                    </a>

                    <div class="flex min-w-0 flex-col justify-center px-5 py-7 sm:px-8 lg:px-10 lg:py-9">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-brand-navy/8 px-3 py-1 text-[11px] font-black uppercase tracking-[0.1em] text-brand-navy">{{ $publicationTypeName($featured) }}</span>
                            <span class="rounded-full bg-[#fff1c9] px-3 py-1 text-[11px] font-black uppercase tracking-[0.1em] text-[#875b12]">Pilihan Riset</span>
                        </div>
                        <h2 id="featured-publication-heading" class="mt-3 text-balance font-display text-2xl font-black leading-tight text-brand-navy sm:text-3xl">{{ $featured->title }}</h2>
                        <p class="mt-3 line-clamp-4 text-base leading-7 text-slate-600">{{ $publicationExcerpt($featured, 280) }}</p>
                        <div class="mt-4 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm font-semibold text-slate-500">
                            <span>{{ $publicationAuthors($featured) }}</span>
                            <span aria-hidden="true">·</span>
                            <time datetime="{{ optional($featured->published_at)->toDateString() }}">{{ $publicationDate($featured) }}</time>
                            @if ($featured->page_count)
                                <span aria-hidden="true">·</span><span>{{ $featured->page_count }} halaman</span>
                            @endif
                        </div>
                        <div class="mt-5 flex flex-wrap gap-3">
                            <a href="{{ route('publications.show', $featured->slug) }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-brand-navy px-5 text-sm font-black text-white transition hover:bg-brand-ink">Baca Publikasi <span class="ml-2" aria-hidden="true">→</span></a>
                            @if ($featuredDownloadUrl)
                                <a href="{{ $featuredDownloadUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-brand-navy/20 bg-white px-5 text-sm font-black text-brand-navy transition hover:border-brand-navy">Unduh Dokumen</a>
                            @endif
                        </div>
                    </div>
                </article>
            </div>
        </section>
    @endif

    <section id="publication-catalog" class="py-9 sm:py-10 lg:py-11" aria-labelledby="publication-catalog-heading">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-brand-navy">Repository Publikasi</p>
                    <h2 id="publication-catalog-heading" class="mt-1 font-display text-2xl font-black text-brand-navy sm:text-3xl">Jelajahi Riset & Publikasi</h2>
                    <p class="mt-1.5 max-w-3xl text-base leading-7 text-slate-600">Temukan dokumen berdasarkan judul, topik, dan jenis publikasi yang dibutuhkan.</p>
                </div>
                <p class="text-sm font-bold text-slate-500"><strong class="text-brand-navy">{{ number_format($totalPublications, 0, ',', '.') }}</strong> dokumen tersedia</p>
            </div>

            <form method="GET" action="{{ route('publications.index') }}#publication-catalog" class="mt-5 grid gap-2 rounded-[14px] bg-white p-3 sm:grid-cols-2 lg:grid-cols-[minmax(280px,1fr)_220px_auto_auto_auto]">
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
                <nav aria-label="Jenis publikasi" class="mt-2 flex gap-2 overflow-x-auto pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
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

            <div class="mt-7 {{ $selectedView === 'grid' ? 'grid gap-x-6 gap-y-8 sm:grid-cols-2 lg:grid-cols-3' : 'space-y-4' }}">
                @forelse ($publicationItems as $publicationIndex => $publication)
                    @php
                        $coverImage = edulaw_file_url($publication->cover_image ?? null);
                        $currentDownloadUrl = $downloadUrl($publication);
                        $palette = $fallbackPalette($publication, $publicationIndex);
                        $authorProfiles = $publicationAuthorProfiles($publication);
                    @endphp

                    @if ($selectedView === 'grid')
                    <article class="group relative mx-auto flex w-full max-w-sm flex-col">
                        <a href="{{ route('publications.show', $publication->slug) }}" class="relative z-10 mx-auto flex aspect-[1/1.34] w-[76%] max-w-60 flex-col justify-between overflow-hidden rounded-md p-5 text-white shadow-xl shadow-slate-900/15 transition duration-300 group-hover:-translate-y-1 group-hover:shadow-2xl focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-brand-amber" style="background: linear-gradient(155deg, {{ $palette['from'] }}, {{ $palette['via'] }} 68%, {{ $palette['to'] }});">
                            @if ($coverImage)
                                <img src="{{ $coverImage }}" alt="Sampul {{ $publication->title }}" loading="lazy" class="absolute inset-0 size-full object-cover" onerror="this.remove()">
                            @endif
                            <span class="absolute inset-0" style="background: linear-gradient(155deg, {{ $palette['overlay'] }}, {{ $palette['from'] }} 70%, {{ $palette['to'] }});"></span>
                            <span class="absolute -right-10 -top-10 size-36 rounded-full border border-white/10"></span>
                            <span class="absolute -bottom-16 -left-12 size-44 rounded-full border border-white/10"></span>

                            <span class="relative">
                                <span class="block text-[11px] font-extrabold uppercase tracking-[0.15em] text-[#efc66b]">{{ $publicationTypeName($publication) }}</span>
                                <span class="mt-7 line-clamp-5 block text-lg font-black leading-snug tracking-[-0.012em] text-white">{{ $publication->title }}</span>
                            </span>
                            <span class="relative text-[11px] font-bold uppercase tracking-[0.12em] text-white/55">Edulaw Project</span>
                        </a>

                        <div class="-mt-8 flex flex-1 flex-col rounded-[14px] border border-[#d9e4e0] bg-white px-5 pb-5 pt-12 transition group-hover:border-[#d9a24c]/50">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-brand-navy/8 px-2.5 py-1 text-[11px] font-black uppercase tracking-[0.09em] text-brand-navy">{{ $publicationTypeName($publication) }}</span>
                                @if ($publication->featured)
                                    <span class="rounded-full bg-[#fff1c9] px-2.5 py-1 text-[11px] font-black uppercase tracking-[0.09em] text-[#875b12]">Pilihan</span>
                                @endif
                            </div>

                            <h3 class="mt-3 line-clamp-3 text-lg font-black leading-snug text-brand-ink transition group-hover:text-brand-navy">
                                <a href="{{ route('publications.show', $publication->slug) }}">{{ $publication->title }}</a>
                            </h3>
                            <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">{{ $publicationExcerpt($publication, 150) }}</p>

                            <div class="mt-4 flex items-center gap-2 text-xs font-semibold text-slate-500">
                                @if ($authorProfiles->isNotEmpty())
                                    <span class="inline-flex shrink-0 -space-x-1.5">
                                        @foreach ($authorProfiles->take(3) as $author)
                                            @if ($author->photo_url)
                                                <img src="{{ $author->photo_url }}" alt="Foto profil {{ $author->name }}" class="h-7 w-7 rounded-full border-2 border-white object-cover" loading="lazy">
                                            @else
                                                <span class="grid h-7 w-7 place-items-center rounded-full border-2 border-white bg-brand-navy text-[11px] font-black text-white">{{ $authorInitials($author->name) }}</span>
                                            @endif
                                        @endforeach
                                    </span>
                                @endif
                                <span class="min-w-0 truncate">{{ $publicationAuthors($publication) }}</span>
                            </div>
                            <p class="mt-2 text-xs font-semibold text-slate-500">{{ $publicationDate($publication) }}@if ($publication->page_count) · {{ $publication->page_count }} halaman @endif</p>

                            <div class="mt-auto flex flex-wrap items-center justify-between gap-3 pt-5">
                                <a href="{{ route('publications.show', $publication->slug) }}" class="text-sm font-black text-brand-navy">Baca ringkasan <span aria-hidden="true">→</span></a>
                                @if ($currentDownloadUrl)
                                    <a href="{{ $currentDownloadUrl }}" target="_blank" rel="noopener noreferrer" class="text-xs font-black text-[#875b12]">Unduh dokumen</a>
                                @endif
                            </div>
                        </div>
                    </article>
                    @else
                    <article class="group grid min-w-0 overflow-hidden rounded-[14px] bg-white sm:grid-cols-[180px_minmax(0,1fr)]">
                        <a href="{{ route('publications.show', $publication->slug) }}" class="grid min-h-[220px] place-items-center bg-[#e9efed] p-5 focus-visible:outline-2 focus-visible:outline-offset-[-3px] focus-visible:outline-brand-amber sm:min-h-0">
                            <span class="relative flex aspect-[1/1.34] w-28 flex-col justify-between overflow-hidden rounded p-3 text-white shadow-lg" style="background: linear-gradient(155deg, {{ $palette['from'] }}, {{ $palette['via'] }} 68%, {{ $palette['to'] }});">
                                @if ($coverImage)
                                    <img src="{{ $coverImage }}" alt="Sampul {{ $publication->title }}" loading="lazy" class="absolute inset-0 size-full object-cover" onerror="this.remove()">
                                @endif
                                <span class="absolute inset-0" style="background: linear-gradient(155deg, {{ $palette['overlay'] }}, {{ $palette['from'] }} 70%, {{ $palette['to'] }});"></span>
                                <span class="relative text-[11px] font-black uppercase tracking-[0.08em] text-[#efc66b]">{{ $publicationTypeName($publication) }}</span>
                                <span class="relative line-clamp-4 text-xs font-black leading-snug text-white">{{ $publication->title }}</span>
                            </span>
                        </a>

                        <div class="flex min-w-0 flex-col justify-center p-5 sm:p-6">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-brand-navy/8 px-2.5 py-1 text-[11px] font-black uppercase tracking-[0.09em] text-brand-navy">{{ $publicationTypeName($publication) }}</span>
                                @if ($publication->featured)
                                    <span class="rounded-full bg-[#fff1c9] px-2.5 py-1 text-[11px] font-black uppercase tracking-[0.09em] text-[#875b12]">Pilihan</span>
                                @endif
                            </div>
                            <h3 class="mt-3 line-clamp-2 text-xl font-black leading-snug text-brand-ink transition group-hover:text-brand-navy">
                                <a href="{{ route('publications.show', $publication->slug) }}">{{ $publication->title }}</a>
                            </h3>
                            <p class="mt-2 line-clamp-2 text-base leading-7 text-slate-600">{{ $publicationExcerpt($publication, 180) }}</p>
                            <p class="mt-3 text-sm font-semibold text-slate-500">{{ $publicationAuthors($publication) }} · {{ $publicationDate($publication) }}@if ($publication->page_count) · {{ $publication->page_count }} halaman @endif</p>
                            <div class="mt-4 flex flex-wrap items-center gap-5">
                                <a href="{{ route('publications.show', $publication->slug) }}" class="text-sm font-black text-brand-navy">Baca ringkasan <span aria-hidden="true">→</span></a>
                                @if ($currentDownloadUrl)
                                    <a href="{{ $currentDownloadUrl }}" target="_blank" rel="noopener noreferrer" class="text-sm font-black text-[#875b12]">Unduh dokumen</a>
                                @endif
                            </div>
                        </div>
                    </article>
                    @endif
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
