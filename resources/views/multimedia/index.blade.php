@extends('layouts.app')

@section('title', 'Multimedia - Edulaw Project')

@section('content')
@php
    use App\Models\Multimedia;
    use Illuminate\Support\Str;

    $youtubeItems = collect($youtubeVideos ?? [])->values();
    $shortItems = collect($shortsReels ?? [])->values();
    $albumItems = collect($photoAlbums ?? [])->values();
    $counts = collect($counts ?? []);
    $featuredVideo = $featuredYoutubeVideo ?? $youtubeItems->first();

    $indexUrl = route('multimedia.index');
    $contactUrl = route('contact.index');
    $collaborationUrl = route('collaboration.index');

    $externalUrl = fn ($item) => $item?->embed_url ?: $item?->media_url ?: $indexUrl;
    $isExternalUrl = fn ($url) => filled($url) && Str::startsWith($url, ['http://', 'https://']);
    $itemDate = fn ($item) => optional($item?->published_at)->translatedFormat('d M Y') ?: 'Belum terjadwal';
    $itemDescription = fn ($item, int $limit = 150) => Str::limit(
        trim(strip_tags((string) $item?->description)) ?: 'Ringkasan konten sedang disiapkan.',
        $limit
    );
    $isGooglePhotos = fn ($item) => $item?->platform === 'google_photos'
        || Str::contains((string) $item?->media_url, ['photos.app.goo.gl', 'photos.google.com'])
        || Str::contains((string) $item?->embed_url, ['photos.app.goo.gl', 'photos.google.com']);
    $shortBadge = fn ($item) => match ($item?->platform) {
        'instagram' => 'INSTAGRAM',
        'tiktok' => 'TIKTOK',
        'youtube' => 'SHORTS',
        default => Str::upper($item?->display_type ?: 'REELS'),
    };

    $youtubeSideItems = $youtubeItems
        ->reject(fn ($item) => $featuredVideo && $item?->id === $featuredVideo?->id)
        ->take(4)
        ->values();
    $youtubeMoreItems = $youtubeItems
        ->reject(fn ($item) => $featuredVideo && $item?->id === $featuredVideo?->id)
        ->skip(4)
        ->values();

    $statPills = collect([
        ['label' => 'YouTube Video', 'count' => $counts->get('youtubeVideos'), 'href' => '#youtube-videos'],
        ['label' => 'Shorts & Reels', 'count' => $counts->get('shortsReels'), 'href' => '#shorts-reels'],
        ['label' => 'Photo Album', 'count' => $counts->get('photoAlbums'), 'href' => '#photo-albums'],
    ]);

    $fallbackIcon = function (string $type = 'video'): string {
        if ($type === 'album') {
            return '<svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 5h14v14H5V5Zm3 11 3.2-3.2 2.2 2.2 2.1-2.6L19 16M8.5 9.5h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        }

        if ($type === 'short') {
            return '<svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 4h6a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm2 5v6l4-3-4-3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>';
        }

        return '<svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M8 5v14l11-7-11-7Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>';
    };
@endphp

<main class="bg-[#f6f8fb] text-brand-ink">
    <x-shared.page-header
        title="Multimedia Literasi Hukum Edulaw"
        :compact="true"
        eyebrow="MULTIMEDIA EDULAW"
        description="Jelajahi video YouTube, Shorts/Reels, dan album foto kegiatan Edulaw Project."
        background-image="https://images.unsplash.com/photo-1551818255-e6e10975bc17?auto=format&fit=crop&w=1800&q=85"
        background-alt="Konten audiovisual literasi hukum Edulaw"
        grid-class="gap-5 px-5 py-7 sm:w-full sm:px-6 lg:min-h-[240px] lg:grid-cols-2 lg:items-center lg:px-8 lg:py-8"
        title-class="text-3xl sm:text-4xl lg:text-[2.45rem]"
        description-class="max-w-2xl text-sm leading-6 text-white/84 lg:ml-auto lg:text-right"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Multimedia'],
        ]"
    >
        <div class="flex flex-col gap-3 sm:flex-row lg:flex-col xl:flex-row">
            <a
                href="#youtube-videos"
                class="inline-flex min-h-11 items-center justify-center rounded-full bg-brand-amber px-5 py-2.5 text-sm font-black text-brand-ink shadow-lg shadow-black/20 transition hover:-translate-y-0.5 hover:bg-[#D99A25]"
            >
                Lihat Video YouTube
            </a>

            <a
                href="#photo-albums"
                class="inline-flex min-h-11 items-center justify-center rounded-full border border-white/25 bg-white/10 px-5 py-2.5 text-sm font-black text-white backdrop-blur transition hover:-translate-y-0.5 hover:border-brand-amber hover:bg-white/15"
            >
                Jelajahi Dokumentasi
            </a>
        </div>
    </x-shared.page-header>

    <section class="border-b border-slate-200 bg-white py-4">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex gap-2 overflow-x-auto pb-1">
                @foreach ($statPills as $index => $pill)
                    <a
                        href="{{ $pill['href'] }}"
                        class="shrink-0 rounded-full border px-4 py-2 text-xs font-black uppercase tracking-[0.12em] transition
                            {{ $index === 0
                                ? 'border-brand-navy bg-brand-navy text-white shadow-sm'
                                : 'border-slate-200 bg-white text-slate-600 hover:border-brand-navy hover:bg-brand-navy hover:text-white' }}"
                    >
                        {{ $pill['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section id="youtube-videos" class="py-10 lg:py-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                    YouTube Video
                </p>

                <h2 class="mt-2 text-2xl font-black tracking-tight text-brand-ink sm:text-3xl">
                    Konten Video dari YouTube
                </h2>

                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Diskusi, webinar, dokumentasi, dan pembahasan hukum Edulaw yang dapat ditonton secara lengkap.
                </p>
            </div>

            @if ($featuredVideo)
                @php
                    $featuredUrl = $externalUrl($featuredVideo);
                    $featuredThumbnail = $featuredVideo->thumbnail_url;
                @endphp

                <div class="mt-7 grid gap-6 lg:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.9fr)]">
                    <article class="group overflow-hidden rounded-3xl border border-slate-200 bg-[#07111f] text-white shadow-sm shadow-slate-900/10">
                        <a
                            href="{{ $featuredUrl }}"
                            @if ($isExternalUrl($featuredUrl)) target="_blank" rel="noopener noreferrer" @endif
                            class="block h-full"
                        >
                            <div class="relative aspect-video overflow-hidden bg-linear-to-br from-brand-navy via-[#123d68] to-[#28659d]">
                                @if ($featuredThumbnail)
                                    <img
                                        src="{{ $featuredThumbnail }}"
                                        alt="{{ $featuredVideo->title }}"
                                        loading="lazy"
                                        onerror="this.remove()"
                                        class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105"
                                    >
                                @else
                                    <div class="absolute inset-0 grid place-items-center text-white/80">
                                        {!! $fallbackIcon('video') !!}
                                    </div>
                                @endif

                                <div class="absolute inset-0 bg-linear-to-t from-[#07111f]/94 via-[#07111f]/28 to-transparent"></div>

                                <span class="absolute left-5 top-5 rounded-full bg-brand-amber px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-brand-ink">
                                    YouTube
                                </span>

                                <span class="absolute inset-0 m-auto flex h-16 w-16 items-center justify-center rounded-full bg-white/95 text-brand-navy shadow-xl transition group-hover:scale-105 group-hover:bg-brand-amber">
                                    <svg class="ml-0.5 h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="M8 5v14l11-7L8 5Z"/>
                                    </svg>
                                </span>

                                <div class="absolute bottom-0 left-0 right-0 p-5 sm:p-7">
                                    <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-white/70">
                                        <span>{{ $itemDate($featuredVideo) }}</span>
                                        @if ($featuredVideo->duration)
                                            <span class="h-1 w-1 rounded-full bg-white/40"></span>
                                            <span>{{ $featuredVideo->duration }}</span>
                                        @endif
                                    </div>

                                    <h3 class="line-clamp-2 mt-3 max-w-3xl text-2xl font-black leading-tight sm:text-3xl">
                                        {{ $featuredVideo->title }}
                                    </h3>

                                    <p class="line-clamp-2 mt-3 max-w-2xl text-sm leading-7 text-white/76">
                                        {{ $itemDescription($featuredVideo, 170) }}
                                    </p>

                                    <span class="mt-5 inline-flex min-h-11 items-center rounded-full bg-white px-5 text-sm font-black text-brand-navy transition group-hover:bg-brand-amber">
                                        Tonton Video
                                    </span>
                                </div>
                            </div>
                        </a>
                    </article>

                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($youtubeSideItems as $item)
                            @php
                                $url = $externalUrl($item);
                                $thumbnail = $item->thumbnail_url;
                            @endphp

                            <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg hover:shadow-slate-900/10">
                                <a
                                    href="{{ $url }}"
                                    @if ($isExternalUrl($url)) target="_blank" rel="noopener noreferrer" @endif
                                    class="block h-full"
                                >
                                    <div class="relative aspect-video overflow-hidden bg-linear-to-br from-brand-navy via-[#123d68] to-[#28659d]">
                                        @if ($thumbnail)
                                            <img
                                                src="{{ $thumbnail }}"
                                                alt="{{ $item->title }}"
                                                loading="lazy"
                                                onerror="this.remove()"
                                                class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                            >
                                            <div class="absolute inset-0 bg-linear-to-t from-brand-navy/58 via-transparent to-transparent"></div>
                                        @else
                                            <div class="absolute inset-0 grid place-items-center text-white/78">
                                                {!! $fallbackIcon('video') !!}
                                            </div>
                                        @endif

                                        <span class="absolute left-3 top-3 rounded-full bg-white/92 px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.12em] text-brand-navy">
                                            YouTube
                                        </span>

                                        <span class="absolute inset-0 m-auto flex h-10 w-10 items-center justify-center rounded-full bg-white/92 text-brand-navy shadow-lg transition group-hover:bg-brand-amber">
                                            <svg class="ml-0.5 h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                <path d="M8 5v14l11-7L8 5Z"/>
                                            </svg>
                                        </span>
                                    </div>

                                    <div class="p-4">
                                        <h3 class="line-clamp-2 min-h-10 text-sm font-black leading-snug text-brand-ink group-hover:text-brand-navy">
                                            {{ $item->title }}
                                        </h3>

                                        <p class="mt-2 text-xs font-bold text-brand-coral">
                                            {{ $itemDate($item) }}@if ($item->duration) · {{ $item->duration }} @endif
                                        </p>
                                    </div>
                                </a>
                            </article>
                        @endforeach

                        @for ($placeholder = $youtubeSideItems->count(); $placeholder < 4; $placeholder++)
                            <div class="grid min-h-40 place-items-center rounded-2xl border border-dashed border-slate-300 bg-white/70 p-4 text-center">
                                <p class="text-sm font-bold leading-6 text-slate-500">
                                    Video berikutnya sedang disiapkan.
                                </p>
                            </div>
                        @endfor
                    </div>
                </div>

                @if ($youtubeMoreItems->isNotEmpty())
                    <div class="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($youtubeMoreItems as $item)
                            @php
                                $url = $externalUrl($item);
                                $thumbnail = $item->thumbnail_url;
                            @endphp

                            <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg hover:shadow-slate-900/10">
                                <a
                                    href="{{ $url }}"
                                    @if ($isExternalUrl($url)) target="_blank" rel="noopener noreferrer" @endif
                                    class="block h-full"
                                >
                                    <div class="relative aspect-video overflow-hidden bg-linear-to-br from-brand-navy via-[#123d68] to-[#28659d]">
                                        @if ($thumbnail)
                                            <img src="{{ $thumbnail }}" alt="{{ $item->title }}" loading="lazy" onerror="this.remove()" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                            <div class="absolute inset-0 bg-linear-to-t from-brand-navy/58 via-transparent to-transparent"></div>
                                        @else
                                            <div class="absolute inset-0 grid place-items-center text-white/78">{!! $fallbackIcon('video') !!}</div>
                                        @endif

                                        <span class="absolute inset-0 m-auto flex h-10 w-10 items-center justify-center rounded-full bg-white/92 text-brand-navy shadow-lg transition group-hover:bg-brand-amber">
                                            <svg class="ml-0.5 h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7L8 5Z"/></svg>
                                        </span>
                                    </div>

                                    <div class="p-4">
                                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-brand-navy">YouTube</p>
                                        <h3 class="line-clamp-2 mt-2 text-base font-black leading-snug text-brand-ink group-hover:text-brand-navy">{{ $item->title }}</h3>
                                        <p class="mt-2 text-xs font-bold text-brand-coral">{{ $itemDate($item) }}@if ($item->duration) · {{ $item->duration }} @endif</p>
                                    </div>
                                </a>
                            </article>
                        @endforeach
                    </div>
                @endif
            @else
                <div class="mt-7 rounded-2xl border border-dashed border-slate-300 bg-white p-6">
                    <div class="flex items-center gap-4">
                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-brand-mist text-brand-navy">
                            {!! $fallbackIcon('video') !!}
                        </div>

                        <div>
                            <h3 class="text-base font-black text-brand-ink">Video YouTube belum tersedia.</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-600">Konten diskusi dan dokumentasi akan ditampilkan di bagian ini.</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <section id="shorts-reels" class="bg-white py-10 lg:py-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                    Shorts & Reels
                </p>

                <h2 class="mt-2 text-2xl font-black tracking-tight text-brand-ink sm:text-3xl">
                    Konten Short dan Reels
                </h2>

                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Cuplikan pendek, ringkasan isu, dan konten visual singkat untuk memahami hukum secara cepat.
                </p>
            </div>

            @if ($shortItems->isNotEmpty())
                <div class="mt-7 grid grid-flow-col auto-cols-[minmax(210px,240px)] gap-5 overflow-x-auto pb-2 sm:grid-flow-row sm:grid-cols-2 sm:overflow-visible md:grid-cols-3 lg:grid-cols-5">
                    @foreach ($shortItems as $item)
                        @php
                            $url = $externalUrl($item);
                            $thumbnail = $item->thumbnail_url;
                        @endphp

                        <article class="group overflow-hidden rounded-3xl border border-slate-200 bg-[#07111f] shadow-sm transition hover:-translate-y-1 hover:shadow-lg hover:shadow-slate-900/10">
                            <a
                                href="{{ $url }}"
                                @if ($isExternalUrl($url)) target="_blank" rel="noopener noreferrer" @endif
                                class="block"
                            >
                                <div class="relative aspect-[4/5] overflow-hidden bg-linear-to-br from-brand-navy via-[#7b2948] to-brand-coral">
                                    @if ($thumbnail)
                                        <img
                                            src="{{ $thumbnail }}"
                                            alt="{{ $item->title }}"
                                            loading="lazy"
                                            onerror="this.remove()"
                                            class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                        >
                                        <div class="absolute inset-0 bg-linear-to-t from-brand-navy/82 via-brand-navy/18 to-transparent"></div>
                                    @else
                                        <div class="absolute inset-0 grid place-items-center text-white/80">
                                            {!! $fallbackIcon('short') !!}
                                        </div>
                                    @endif

                                    <span class="absolute left-3 top-3 rounded-full bg-white/92 px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.12em] text-brand-ink">
                                        {{ $shortBadge($item) }}
                                    </span>

                                    <span class="absolute inset-0 m-auto flex h-11 w-11 items-center justify-center rounded-full bg-white/92 text-brand-navy shadow-lg transition group-hover:bg-brand-amber">
                                        <svg class="ml-0.5 h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M8 5v14l11-7L8 5Z"/>
                                        </svg>
                                    </span>

                                    <div class="absolute bottom-0 left-0 right-0 p-4">
                                        <p class="text-[10px] font-black uppercase tracking-[0.12em] text-white/58">
                                            {{ $item->display_platform }} · {{ $itemDate($item) }}
                                        </p>
                                        <h3 class="line-clamp-2 mt-2 text-base font-black leading-snug text-white">
                                            {{ $item->title }}
                                        </h3>
                                    </div>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="mt-7 rounded-2xl border border-slate-200 bg-[#f8fafc] p-6">
                    <div class="flex items-center gap-4">
                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-brand-amber-soft text-brand-navy">
                            {!! $fallbackIcon('short') !!}
                        </div>

                        <div>
                            <h3 class="text-base font-black text-brand-ink">Shorts dan Reels sedang disiapkan.</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-600">Nantikan ringkasan isu hukum dalam format singkat dan mudah dibagikan.</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <section id="photo-albums" class="py-10 lg:py-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                    Photo Album
                </p>

                <h2 class="mt-2 text-2xl font-black tracking-tight text-brand-ink sm:text-3xl">
                    Konten Photo dari Album Google Photos
                </h2>

                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Dokumentasi kegiatan, diskusi, kelas, dan kolaborasi Edulaw Project dalam bentuk album foto.
                </p>
            </div>

            @if ($albumItems->isNotEmpty())
                <div class="mt-7 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($albumItems as $item)
                        @php
                            $url = $externalUrl($item);
                            $thumbnail = $item->thumbnail_url;
                            $badge = $isGooglePhotos($item) ? 'GOOGLE PHOTOS' : 'PHOTO ALBUM';
                        @endphp

                        <article class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-900/5 transition hover:-translate-y-1 hover:shadow-lg hover:shadow-slate-900/10">
                            <a
                                href="{{ $url }}"
                                @if ($isExternalUrl($url)) target="_blank" rel="noopener noreferrer" @endif
                                class="block h-full"
                            >
                                <div class="relative aspect-video overflow-hidden bg-linear-to-br from-brand-navy via-slate-700 to-brand-amber">
                                    @if ($thumbnail)
                                        <img
                                            src="{{ $thumbnail }}"
                                            alt="{{ $item->title }}"
                                            loading="lazy"
                                            onerror="this.remove()"
                                            class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                        >
                                        <div class="absolute inset-0 bg-linear-to-t from-brand-navy/66 via-transparent to-transparent"></div>
                                    @else
                                        <div class="absolute inset-0 grid place-items-center text-white/80">
                                            {!! $fallbackIcon('album') !!}
                                        </div>
                                    @endif

                                    <span class="absolute left-4 top-4 rounded-full bg-white/92 px-3 py-1 text-[10px] font-black uppercase tracking-[0.12em] text-brand-navy">
                                        {{ $badge }}
                                    </span>
                                </div>

                                <div class="p-5">
                                    <div class="flex items-center justify-between gap-3 text-xs font-bold text-slate-500">
                                        <span>{{ $itemDate($item) }}</span>
                                        <span>{{ $item->photo_count ? number_format($item->photo_count, 0, ',', '.').' foto' : $item->display_platform }}</span>
                                    </div>

                                    <h3 class="line-clamp-2 mt-3 text-lg font-black leading-snug text-brand-ink group-hover:text-brand-navy">
                                        {{ $item->title }}
                                    </h3>

                                    <p class="line-clamp-2 mt-3 min-h-12 text-sm leading-6 text-slate-600">
                                        {{ $itemDescription($item, 132) }}
                                    </p>

                                    <span class="mt-5 inline-flex items-center gap-2 text-sm font-black text-brand-navy">
                                        Buka Album
                                        <svg class="h-4 w-4 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M7 17 17 7M9 7h8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="mt-7 rounded-2xl border border-dashed border-slate-300 bg-white p-6">
                    <div class="flex items-center gap-4">
                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-brand-mist text-brand-navy">
                            {!! $fallbackIcon('album') !!}
                        </div>

                        <div>
                            <h3 class="text-base font-black text-brand-ink">Album foto belum tersedia.</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-600">Dokumentasi kegiatan Edulaw akan ditampilkan di bagian ini.</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <x-shared.cta-section
        eyebrow="KOLABORASI MULTIMEDIA"
        title="Punya gagasan konten hukum yang layak dibagikan?"
        body="Edulaw membuka ruang kolaborasi untuk produksi video, Shorts/Reels, dokumentasi foto, dan konten edukasi hukum berbasis isu publik."
        :primary-url="$collaborationUrl"
        primary-label="Ajukan Kolaborasi"
        :secondary-url="$contactUrl"
        secondary-label="Hubungi Edulaw"
        background-image="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1800&q=85"
        background-alt="Kolaborasi konten multimedia hukum"
    />
</main>
@endsection
