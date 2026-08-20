@extends('layouts.app')

@section('title', 'Multimedia Edukasi Hukum | Edulaw Project')
@section('meta_description', 'Jelajahi video, Shorts/Reels, dan dokumentasi kegiatan Edulaw Project dari kanal resmi YouTube, Instagram, dan Google Photos kami.')
@section('canonical_url', route('multimedia.index'))

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
        $youtubeSchemaVideos = collect($youtubeVideos->items())
            ->when($featuredYoutubeVideo, fn ($items) => $items->prepend($featuredYoutubeVideo))
            ->unique('id');

        $multimediaSchemaItems = $youtubeSchemaVideos
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

    @foreach ($youtubeSchemaVideos as $youtubeVideo)
        @if ($videoSchema = \App\Support\StructuredData::video($youtubeVideo))
            <x-structured-data :data="$videoSchema" />
        @endif
    @endforeach
@endpush

@section('content')
@php
    use App\Support\EdulawSite;
    $shortItems = collect($shortsReels ?? [])->values();
    $albumItems = collect($photoAlbums ?? [])->values();
    $featuredVideo = $featuredYoutubeVideo;

    $youtubeUrl = EdulawSite::resolveUrl(EdulawSite::value('social.youtube_url'));
    $instagramUrl = EdulawSite::resolveUrl(EdulawSite::value('social.instagram_url'));
    $collaborationUrl = route('collaboration.index');
    $contactUrl = route('contact.index');

    $dateLabel = fn ($item) => $item?->published_at?->locale('id')->translatedFormat('d M Y') ?: 'Kanal resmi Edulaw';
    $shortPlatform = fn ($item) => $item?->platform === 'youtube' ? 'youtube' : 'instagram';
@endphp

<main class="overflow-x-clip bg-[#f6f8fb] text-brand-ink">
    <x-shared.page-header
        title="Multimedia Literasi Hukum Edulaw"
        :compact="true"
        eyebrow="Kanal Multimedia"
        :channel-header="true"
        description="Video, Shorts/Reels, dan dokumentasi kegiatan Edulaw dari kanal resmi kami."
        background-image="https://images.unsplash.com/photo-1551818255-e6e10975bc17?auto=format&fit=crop&w=1800&q=85"
        background-alt="Kegiatan produksi konten dan diskusi Edulaw"
        grid-class="gap-5 px-5 py-7 sm:w-full sm:px-6 lg:min-h-[240px] lg:grid-cols-2 lg:items-center lg:px-8 lg:py-8"
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
        <div class="mx-auto max-w-7xl overflow-x-auto px-5 py-3 sm:px-6 lg:px-8">
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
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <x-multimedia.section-heading
                    platform="youtube"
                    eyebrow="YouTube"
                    title="Video Pilihan Edulaw"
                    description="Diskusi, webinar, dan pembahasan hukum dari kanal YouTube Edulaw."
                />

                @if ($youtubeUrl)
                    <a href="{{ $youtubeUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Lihat kanal YouTube Edulaw (membuka tab baru)" class="inline-flex items-center gap-2 self-start text-sm font-black text-brand-navy transition hover:text-brand-coral focus-visible:rounded focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy lg:self-auto">
                        Lihat Kanal YouTube
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                @endif
            </div>

            @if ($featuredVideo)
                <x-multimedia.featured-card :item="$featuredVideo" class="mt-7" />

                @if ($youtubeVideos->isNotEmpty())
                    <div class="mt-9 flex items-center justify-between gap-4">
                        <h3 class="text-lg font-black text-brand-ink">Video Lainnya</h3>
                        <p class="text-xs font-bold text-slate-500">{{ $youtubeVideos->total() }} video</p>
                    </div>

                    <div class="mt-4 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($youtubeVideos as $item)
                            <x-multimedia.media-card :item="$item" />
                        @endforeach
                    </div>

                    <x-multimedia.pagination :paginator="$youtubeVideos" />
                @endif
            @else
                <x-multimedia.empty-state platform="youtube" title="Video pilihan segera hadir" description="Diskusi dan pembahasan hukum Edulaw akan ditampilkan dari kanal YouTube resmi kami." :url="$youtubeUrl" link-label="Kunjungi YouTube" class="mt-7" />
            @endif
        </div>
    </section>

    <section id="shorts-reels" class="scroll-mt-24 bg-white py-14 lg:py-16">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <x-multimedia.section-heading platform="instagram" eyebrow="Instagram" title="Hukum dalam Format Singkat" description="Cuplikan ringkas, fakta hukum, dan dokumentasi pendek dari kanal Edulaw." />
                @if ($instagramUrl)
                    <a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Lihat Instagram Edulaw (membuka tab baru)" class="inline-flex items-center gap-2 self-start text-sm font-black text-brand-navy transition hover:text-brand-coral focus-visible:rounded focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy lg:self-auto">
                        Lihat Instagram
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                @endif
            </div>

            @if ($shortItems->isNotEmpty())
                <div class="mt-7 grid grid-cols-2 gap-4 md:grid-cols-3 lg:gap-5">
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
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
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

    <section class="bg-white px-5 py-7 sm:px-6 lg:px-8 lg:py-9">
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
