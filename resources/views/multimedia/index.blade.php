@extends('layouts.app')

@section('title', 'Multimedia Edukasi Hukum | Edulaw Project')
@section('meta_description', 'Jelajahi video, Shorts/Reels, dan dokumentasi kegiatan Edulaw Project dari kanal resmi YouTube, Instagram, dan Google Photos kami.')

@push('styles')
    <style>
        @media (min-width: 1024px) and (max-width: 1279px) {
            body header nav[aria-label="Navigasi utama"],
            body header nav[aria-label="Navigasi utama"] + div {
                display: none !important;
            }

            body header button[aria-controls="mobile-navigation"] {
                display: inline-flex !important;
            }

            body header #mobile-navigation:not([style*="display: none"]) {
                display: block !important;
            }
        }
    </style>
@endpush

@push('head')
    @php
        $multimediaSchemaItems = collect($youtubeVideos)
            ->concat($shortsReels)
            ->concat($photoAlbums)
            ->unique('id')
            ->map(function ($item): ?array {
                $itemUrl = \App\Support\EdulawSite::resolveUrl($item->media_url);

                return $itemUrl ? [
                    'name' => $item->title,
                    'url' => $itemUrl,
                    'image' => $item->thumbnail_url,
                ] : null;
            })
            ->filter()
            ->values()
            ->all();
    @endphp

    @if ($multimediaSchemaItems !== [])
        <x-structured-data :data="\App\Support\StructuredData::itemList($multimediaSchemaItems, 'Multimedia Edukasi Hukum')" />
    @endif

    @foreach ($youtubeVideos as $youtubeVideo)
        @if ($videoSchema = \App\Support\StructuredData::video($youtubeVideo))
            <x-structured-data :data="$videoSchema" />
        @endif
    @endforeach
@endpush

@section('content')
@php
    use App\Support\EdulawSite;
    use Illuminate\Support\Str;

    $youtubeItems = collect($youtubeVideos ?? [])->values();
    $shortItems = collect($shortsReels ?? [])->values();
    $albumItems = collect($photoAlbums ?? [])->values();
    $featuredVideo = $featuredYoutubeVideo ?? $youtubeItems->first();
    $secondaryVideos = $youtubeItems
        ->reject(fn ($item) => $featuredVideo && $item->id === $featuredVideo->id)
        ->take(3)
        ->values();

    $youtubeUrl = EdulawSite::resolveUrl(EdulawSite::value('social.youtube_url'));
    $instagramUrl = EdulawSite::resolveUrl(EdulawSite::value('social.instagram_url'));
    $collaborationUrl = route('collaboration.index');
    $contactUrl = route('contact.index');
    $hasMoreYoutubeVideos = (int) data_get($counts, 'youtubeVideos', 0) > 4;

    $dateLabel = fn ($item) => $item?->published_at?->locale('id')->translatedFormat('d M Y') ?: 'Kanal resmi Edulaw';
    $description = fn ($item) => trim(strip_tags((string) $item?->description))
        ?: 'Pembahasan hukum pilihan dari kanal resmi Edulaw Project.';
    $shortPlatform = fn ($item) => $item?->platform === 'youtube' ? 'youtube' : 'instagram';
@endphp

<main class="overflow-x-clip bg-[#f6f8fb] text-brand-ink">
    <x-shared.page-header
        title="Multimedia Literasi Hukum Edulaw"
        :compact="true"
        eyebrow="MULTIMEDIA EDULAW"
        description="Video, Shorts/Reels, dan dokumentasi kegiatan Edulaw dari kanal resmi kami."
        background-image="https://images.unsplash.com/photo-1551818255-e6e10975bc17?auto=format&fit=crop&w=1800&q=85"
        background-alt="Kegiatan produksi konten dan diskusi Edulaw"
        grid-class="gap-5 px-5 py-6 sm:w-full sm:px-6 lg:min-h-[190px] lg:grid-cols-2 lg:items-center lg:px-8 lg:py-6"
        title-class="text-3xl sm:text-4xl lg:text-[2.35rem]"
        description-class="max-w-xl text-sm leading-6 text-white/90 lg:ml-auto lg:text-right"
        :overlay-opacity="0.62"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Multimedia'],
        ]"
    >
        <div class="flex w-full flex-col gap-2.5 sm:flex-row lg:justify-end">
            <a href="#video" class="inline-flex min-h-10 items-center justify-center rounded-full bg-brand-amber px-5 py-2 text-sm font-black text-brand-ink shadow-md transition hover:-translate-y-0.5 hover:bg-[#e7a72d] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                Lihat Video YouTube
            </a>
            <a href="#album-foto" class="inline-flex min-h-10 items-center justify-center rounded-full border border-white/35 bg-white/10 px-5 py-2 text-sm font-black text-white backdrop-blur transition hover:-translate-y-0.5 hover:border-brand-amber hover:text-brand-amber focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                Jelajahi Dokumentasi
            </a>
        </div>
    </x-shared.page-header>

    <nav aria-label="Navigasi section Multimedia" class="border-b border-slate-200 bg-white/95">
        <div class="mx-auto max-w-7xl overflow-x-auto px-4 py-3 sm:px-6 lg:px-8">
            <div class="flex w-max gap-2 lg:w-auto">
                @foreach ([
                    ['label' => 'Video', 'href' => '#video'],
                    ['label' => 'Shorts & Reels', 'href' => '#shorts-reels'],
                    ['label' => 'Album Foto', 'href' => '#album-foto'],
                ] as $tab)
                    <a
                        href="{{ $tab['href'] }}"
                        @class([
                            'shrink-0 rounded-full border px-4 py-2 text-xs font-black transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy',
                            'border-brand-navy bg-brand-navy text-white' => $loop->first,
                            'border-slate-200 bg-slate-50 text-slate-600 hover:border-brand-navy hover:text-brand-navy' => ! $loop->first,
                        ])
                    >
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </nav>

    <section id="video" class="scroll-mt-24 py-14 lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <x-multimedia.section-heading
                    platform="youtube"
                    eyebrow="YouTube"
                    title="Video Pilihan Edulaw"
                    description="Diskusi, webinar, dan pembahasan hukum dari kanal YouTube Edulaw."
                />

                @if ($hasMoreYoutubeVideos && $youtubeUrl)
                    <a href="{{ $youtubeUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Lihat semua video di YouTube Edulaw (membuka tab baru)" class="inline-flex items-center gap-2 self-start text-sm font-black text-brand-navy transition hover:text-brand-coral focus-visible:rounded focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy lg:self-auto">
                        Lihat Semua di YouTube
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                @endif
            </div>

            @if ($featuredVideo)
                <div class="mt-7 grid items-start gap-5 xl:grid-cols-[minmax(0,1.32fr)_minmax(390px,0.88fr)]">
                    <article data-featured-media class="group overflow-hidden rounded-3xl border border-slate-200 bg-[#07111f] shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-slate-900/10">
                        <a href="{{ $featuredVideo->media_url }}" target="_blank" rel="noopener noreferrer" aria-label="Tonton {{ $featuredVideo->title }} di YouTube (membuka tab baru)" class="block focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-navy">
                            <div class="relative aspect-video overflow-hidden bg-linear-to-br from-brand-navy via-[#123d68] to-[#28659d]">
                                @if ($featuredVideo->thumbnail_url)
                                    <img src="{{ $featuredVideo->thumbnail_url }}" alt="{{ $featuredVideo->title }}" class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.025]">
                                @else
                                    <div class="absolute inset-0 grid place-items-center text-white/75">
                                        <svg class="h-14 w-14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M8 5v14l11-7L8 5Z" stroke="currentColor" stroke-width="1.7"/></svg>
                                    </div>
                                @endif

                                <div class="absolute inset-x-0 bottom-0 h-3/4 bg-linear-to-t from-[#07111f]/95 via-[#07111f]/38 to-transparent"></div>
                                <x-multimedia.platform-badge platform="youtube" :dark="true" class="absolute left-4 top-4 sm:left-5 sm:top-5" />

                                <div class="absolute inset-x-0 bottom-0 p-5 sm:p-7">
                                    <p class="text-xs font-bold text-white/72">{{ $dateLabel($featuredVideo) }}</p>
                                    <h3 class="line-clamp-2 mt-2 max-w-3xl text-xl font-black leading-tight text-white sm:text-2xl lg:text-3xl">{{ $featuredVideo->title }}</h3>
                                    <p class="line-clamp-2 mt-2 max-w-2xl text-sm leading-6 text-white/78">{{ $description($featuredVideo) }}</p>
                                    <span class="mt-4 inline-flex min-h-10 items-center gap-2 rounded-full bg-brand-amber px-4 py-2 text-sm font-black text-brand-ink transition group-hover:bg-white">
                                        Tonton di YouTube
                                        <svg class="h-4 w-4 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </article>

                    @if ($secondaryVideos->isNotEmpty())
                        <div @class(['grid gap-4 sm:grid-cols-2', 'xl:grid-cols-1' => $secondaryVideos->count() === 1, 'xl:grid-cols-2' => $secondaryVideos->count() > 1])>
                            @foreach ($secondaryVideos as $item)
                                <article data-secondary-media class="group h-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-slate-900/10">
                                    <a href="{{ $item->media_url }}" target="_blank" rel="noopener noreferrer" aria-label="Tonton {{ $item->title }} di YouTube (membuka tab baru)" class="flex h-full flex-col focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy">
                                        <div class="relative aspect-video overflow-hidden bg-linear-to-br from-brand-navy to-[#28659d]">
                                            @if ($item->thumbnail_url)
                                                <img src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                                            @else
                                                <div class="grid h-full place-items-center text-white/75"><svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M8 5v14l11-7L8 5Z" stroke="currentColor" stroke-width="1.7"/></svg></div>
                                            @endif
                                            <x-multimedia.platform-badge platform="youtube" :dark="true" class="absolute left-3 top-3" />
                                        </div>

                                        <div class="flex flex-1 items-start justify-between gap-3 p-4">
                                            <div class="min-w-0">
                                                <h3 class="line-clamp-2 text-sm font-black leading-snug text-brand-ink group-hover:text-brand-navy">{{ $item->title }}</h3>
                                                <p class="mt-2 text-xs font-bold text-slate-500">{{ $dateLabel($item) }}</p>
                                            </div>
                                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-navy transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </div>
                                    </a>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div data-video-info class="flex min-h-40 items-center rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:min-h-full">
                            <div>
                                <x-multimedia.platform-badge platform="youtube" />
                                <h3 class="mt-3 text-lg font-black text-brand-ink">Video terbaru lainnya akan segera tersedia.</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Ikuti kanal YouTube Edulaw untuk pembahasan hukum berikutnya.</p>
                                @if ($youtubeUrl)
                                    <a href="{{ $youtubeUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Kunjungi YouTube Edulaw (membuka tab baru)" class="mt-4 inline-flex items-center gap-1.5 text-sm font-black text-brand-navy hover:text-brand-coral focus-visible:rounded focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy">
                                        Kunjungi YouTube
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <x-multimedia.empty-state platform="youtube" title="Video pilihan segera hadir" description="Diskusi dan pembahasan hukum Edulaw akan ditampilkan dari kanal YouTube resmi kami." :url="$youtubeUrl" link-label="Kunjungi YouTube" class="mt-7" />
            @endif
        </div>
    </section>

    <section id="shorts-reels" class="scroll-mt-24 bg-white py-14 lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-multimedia.section-heading platform="instagram" eyebrow="Instagram" title="Hukum dalam Format Singkat" description="Cuplikan ringkas, fakta hukum, dan dokumentasi pendek dari kanal Edulaw." />

            @if ($shortItems->isNotEmpty())
                <div class="mt-7 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4 lg:gap-5">
                    @foreach ($shortItems as $item)
                        @php($platform = $shortPlatform($item))
                        <article data-short-media class="group min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-[#07111f] shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-slate-900/10">
                            <a href="{{ $item->media_url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $platform === 'youtube' ? 'Tonton' : 'Lihat' }} {{ $item->title }} di {{ $platform === 'youtube' ? 'YouTube' : 'Instagram' }} (membuka tab baru)" class="block focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy">
                                <div class="relative aspect-[4/5] overflow-hidden bg-linear-to-br from-brand-navy via-[#7b2948] to-brand-coral">
                                    @if ($item->thumbnail_url)
                                        <img src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                                    @endif
                                    <div class="absolute inset-x-0 bottom-0 h-2/3 bg-linear-to-t from-[#07111f]/92 via-[#07111f]/30 to-transparent"></div>
                                    <x-multimedia.platform-badge :platform="$platform" :label="$platform === 'youtube' ? 'YouTube Shorts' : 'Instagram'" :dark="true" class="absolute left-3 top-3" />
                                    <div class="absolute inset-x-0 bottom-0 p-4">
                                        <div class="flex items-start justify-between gap-2">
                                            <h3 class="line-clamp-2 text-sm font-black leading-snug text-white sm:text-base">{{ $item->title }}</h3>
                                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-amber transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </div>
                                        <p class="mt-2 text-xs font-bold text-white/65">{{ $dateLabel($item) }}</p>
                                    </div>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            @else
                <x-multimedia.empty-state platform="instagram" title="Konten pendek segera hadir" description="Nantikan video singkat seputar isu hukum, regulasi, dan kegiatan Edulaw." :url="$instagramUrl" link-label="Kunjungi Instagram" class="mt-7" />
            @endif
        </div>
    </section>

    <section id="album-foto" class="scroll-mt-24 py-14 lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-multimedia.section-heading platform="google_photos" eyebrow="Google Photos" title="Dokumentasi Kegiatan" description="Album diskusi, kelas, kolaborasi, dan kegiatan Edulaw." />

            @if ($albumItems->isNotEmpty())
                <div class="mt-7 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($albumItems as $item)
                        <article data-album-media class="group min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-slate-900/10">
                            <a href="{{ $item->media_url }}" target="_blank" rel="noopener noreferrer" aria-label="Buka album {{ $item->title }} di Google Photos (membuka tab baru)" class="flex h-full flex-col focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy">
                                <div class="relative aspect-[4/3] overflow-hidden bg-linear-to-br from-brand-navy via-slate-700 to-brand-amber">
                                    @if ($item->thumbnail_url)
                                        <img src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                                    @endif
                                    <x-multimedia.platform-badge platform="google_photos" :dark="true" class="absolute left-3 top-3" />
                                </div>

                                <div class="flex flex-1 flex-col p-5">
                                    <p class="text-xs font-bold text-slate-500">{{ $dateLabel($item) }}@if ($item->photo_count) · {{ number_format($item->photo_count, 0, ',', '.') }} foto @endif</p>
                                    <h3 class="line-clamp-2 mt-2 text-lg font-black leading-snug text-brand-ink group-hover:text-brand-navy">{{ $item->title }}</h3>
                                    <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-black text-brand-navy">
                                        Buka Album
                                        <svg class="h-4 w-4 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            @else
                <x-multimedia.empty-state platform="google_photos" title="Dokumentasi kegiatan akan segera tersedia" description="Album kegiatan Edulaw akan ditampilkan melalui Google Photos." class="mt-7" />
            @endif
        </div>
    </section>

    <section class="bg-white px-4 py-7 sm:px-6 lg:px-8 lg:py-9">
        <div class="relative mx-auto grid max-w-7xl items-center gap-6 overflow-hidden rounded-3xl bg-brand-navy px-6 py-8 text-white shadow-lg shadow-brand-navy/12 sm:px-8 lg:grid-cols-[1fr_auto] lg:px-10 lg:py-9">
            <svg class="pointer-events-none absolute -right-24 top-1/2 h-80 w-80 -translate-y-1/2 text-brand-teal/10" viewBox="0 0 320 320" fill="none" aria-hidden="true"><circle cx="160" cy="160" r="118" stroke="currentColor"/><circle cx="160" cy="160" r="76" stroke="currentColor"/></svg>
            <div class="relative">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-brand-amber">Kolaborasi Multimedia</p>
                <h2 class="mt-2 text-2xl font-black tracking-tight text-white sm:text-3xl">Punya gagasan konten hukum?</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-white/75">Edulaw membuka ruang kolaborasi untuk video, Shorts/Reels, dokumentasi foto, dan konten edukasi hukum.</p>
            </div>
            <div class="relative flex flex-col gap-2.5 sm:flex-row lg:flex-col">
                <a href="{{ $collaborationUrl }}" class="inline-flex min-h-11 items-center justify-center rounded-full bg-brand-amber px-5 py-2.5 text-sm font-black text-brand-ink transition hover:-translate-y-0.5 hover:bg-[#e7a72d] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">Ajukan Kolaborasi</a>
                <a href="{{ $contactUrl }}" class="inline-flex min-h-10 items-center justify-center text-sm font-black text-white/85 transition hover:text-brand-amber focus-visible:rounded focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">Hubungi Edulaw</a>
            </div>
        </div>
    </section>
</main>
@endsection
