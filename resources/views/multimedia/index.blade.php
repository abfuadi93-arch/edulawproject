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

<main class="overflow-x-clip bg-transparent text-brand-ink">
    <x-shared.page-header
        title="Multimedia Literasi Hukum Edulaw"
        :compact="true"
        eyebrow="Kanal Multimedia"
        :channel-header="true"
        description="Video, Shorts/Reels, dan dokumentasi kegiatan Edulaw dari kanal resmi kami."
        background-image="https://images.unsplash.com/photo-1551818255-e6e10975bc17?auto=format&fit=crop&w=1800&q=85"
        background-alt="Kegiatan produksi konten dan diskusi Edulaw"
        grid-class="gap-7 px-5 py-8 sm:w-full sm:px-6 lg:min-h-[240px] lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center lg:px-8 lg:py-8"
        title-class="text-3xl sm:text-4xl lg:text-[2.35rem]"
        description-class="max-w-xl text-base leading-7 text-white/90 lg:ml-auto lg:text-right"
        :overlay-opacity="0.62"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Multimedia'],
        ]"
    >
        <div class="grid w-full gap-1.5 rounded-2xl border border-white/15 bg-white/10 p-1.5 text-left shadow-2xl shadow-black/20 backdrop-blur sm:grid-cols-3 lg:w-auto">
            @foreach ([
                ['label' => 'Video', 'meta' => 'YouTube Edulaw', 'href' => '#video', 'icon' => '▶'],
                ['label' => 'Shorts & Reels', 'meta' => 'Instagram', 'href' => '#shorts-reels', 'icon' => '▯'],
                ['label' => 'Dokumentasi', 'meta' => 'Google Photos', 'href' => '#album-foto', 'icon' => '▦'],
            ] as $channel)
                <a href="{{ $channel['href'] }}" class="flex min-h-14 min-w-0 items-center gap-2.5 rounded-xl px-2.5 py-2 text-white transition hover:bg-white/10 sm:min-w-32">
                    <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-white text-sm font-black text-brand-navy">{{ $channel['icon'] }}</span>
                    <span class="min-w-0"><strong class="block text-xs font-black">{{ $channel['label'] }}</strong><span class="mt-0.5 block truncate text-[11px] font-bold text-white/70">{{ $channel['meta'] }}</span></span>
                </a>
            @endforeach
        </div>
    </x-shared.page-header>

    <section id="video" class="scroll-mt-24 py-9 sm:py-10 lg:py-11">
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
                <div class="mt-7 flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.2em] text-brand-navy"><span class="text-[#d99a25]">★</span> Video Utama</div>
                <x-multimedia.featured-card :item="$featuredVideo" class="mt-4" />

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

    <section id="shorts-reels" class="scroll-mt-24 bg-white py-9 sm:py-10 lg:py-11">
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
                <div class="mt-7 grid gap-5 lg:grid-cols-[1.05fr_.95fr]">
                    <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($shortItems->take(2) as $item)
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

                    <aside class="flex flex-col justify-center rounded-3xl border border-[#ebdcb9] bg-[#fff8ea] p-7 sm:p-8">
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-[#a8660a]">Format Cepat</p>
                        <h3 class="mt-2 text-2xl font-black leading-tight text-brand-ink sm:text-3xl">Konten pendek untuk memahami hukum dengan lebih cepat.</h3>
                        <p class="mt-3 text-base leading-7 text-slate-600">Shorts dan Reels dipisahkan dari video panjang agar pembaca dapat memilih format sesuai waktu dan kebutuhan. Visual vertikal tetap dipertahankan tanpa membuat halaman terasa seperti feed media sosial.</p>
                        <div class="mt-5 flex flex-wrap gap-2.5">
                            @if ($instagramUrl)
                                <a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center rounded-xl bg-brand-navy px-4 text-sm font-black text-white">Lihat Instagram ↗</a>
                            @endif
                            <a href="{{ route('multimedia.index') }}" class="inline-flex min-h-11 items-center rounded-xl border border-brand-navy/20 bg-white px-4 text-sm font-black text-brand-navy">Semua Multimedia</a>
                        </div>
                    </aside>
                </div>
            @else
                <x-multimedia.empty-state platform="instagram" title="Konten pendek segera hadir" description="Nantikan video singkat seputar isu hukum, regulasi, dan kegiatan Edulaw." :url="$instagramUrl" link-label="Kunjungi Instagram" class="mt-7" />
            @endif
        </div>
    </section>

    <section id="album-foto" class="scroll-mt-24 py-9 sm:py-10 lg:py-11">
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

    <x-shared.cta-section
        heading-id="multimedia-collaboration-heading"
        eyebrow="Kolaborasi Multimedia"
        title="Punya gagasan konten hukum?"
        body="Edulaw membuka ruang kolaborasi untuk video, Shorts/Reels, dokumentasi foto, dan konten edukasi hukum."
        :primary-url="$collaborationUrl"
        primary-label="Ajukan Kolaborasi"
        :secondary-url="$contactUrl"
        secondary-label="Hubungi Edulaw"
    />
</main>
@endsection
