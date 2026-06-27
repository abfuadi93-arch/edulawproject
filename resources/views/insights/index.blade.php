@extends('layouts.app')

@section('title', 'Insight - Edulaw Project')

@push('styles')
<style>
    .insight-visual-fallback {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
        padding: 1.25rem;
        color: #ffffff;
    }

    .insight-visual-fallback::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 86% 16%, rgba(255, 255, 255, .20), transparent 28%),
            radial-gradient(circle at 14% 86%, rgba(245, 185, 67, .18), transparent 34%),
            linear-gradient(120deg, rgba(255, 255, 255, .10), transparent 42%, rgba(0, 0, 0, .28));
        pointer-events: none;
    }

    .insight-visual-fallback > * {
        position: relative;
        z-index: 1;
    }

    .insight-fallback-mark {
        display: block;
        width: 44px;
        height: 7px;
        border-radius: 999px;
    }

    .insight-clamp-2,
    .insight-clamp-3,
    .insight-clamp-4 {
        display: -webkit-box;
        overflow: hidden;
        -webkit-box-orient: vertical;
    }

    .insight-clamp-2 {
        -webkit-line-clamp: 2;
    }

    .insight-clamp-3 {
        -webkit-line-clamp: 3;
    }

    .insight-clamp-4 {
        -webkit-line-clamp: 4;
    }

    .insight-control-scroll {
        scrollbar-width: thin;
        scrollbar-color: rgba(31, 60, 105, .32) transparent;
    }

    .insight-control-scroll::-webkit-scrollbar {
        height: 7px;
    }

    .insight-control-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .insight-control-scroll::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: rgba(31, 60, 105, .26);
    }
</style>
@endpush

@section('content')
@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $latestInsights = collect($latestInsights ?? []);
    $latestLead = $latestInsights->first();
    $latestSide = $latestInsights->skip(1)->take(3)->values();
    $insightChannels = collect($insightChannels ?? []);
    $editorialPicks = collect($editorialPicks ?? []);
    $popularInsights = collect($popularInsights ?? []);
    $insightCategories = collect($insightCategories ?? []);
    $selectedCategory = $selectedCategory ?? request('category');
    $search = $search ?? request('q', '');
    $featuredOnly = (bool) ($featuredOnly ?? request()->boolean('featured'));
    $showFilteredArchive = (bool) ($showFilteredArchive ?? false);

    $archiveItems = $insights instanceof \Illuminate\Pagination\AbstractPaginator
        ? $insights->getCollection()
        : collect($insights ?? []);

    $archiveTotal = $insights instanceof \Illuminate\Pagination\AbstractPaginator
        ? $insights->total()
        : $archiveItems->count();

    $insightImage = function ($insight): ?string {
        return $insight?->cover_image_url;
    };

    $categoryName = function ($insight, string $fallback = 'Insight'): string {
        return $insight?->display_category
            ?? $insight?->categoryRelation?->name
            ?? $insight?->category?->name
            ?? $fallback;
    };

    $authorName = function ($insight): string {
        if ($insight && $insight->relationLoaded('authors') && $insight->authors->isNotEmpty()) {
            return $insight->authors->pluck('name')->filter()->join(', ');
        }

        return $insight?->author_name
            ?? $insight?->creator?->name
            ?? 'Edulaw Project';
    };

    $readingTime = function ($insight): string {
        if (! empty($insight?->reading_time)) {
            return $insight->reading_time.' menit baca';
        }

        $text = trim(strip_tags(($insight?->content ?? '').' '.($insight?->excerpt ?? '')));
        $words = str_word_count($text);

        return max(1, (int) ceil($words / 200)).' menit baca';
    };

    $publishedDate = function ($insight): string {
        if (empty($insight?->published_at)) {
            return 'Belum dijadwalkan';
        }

        try {
            return Carbon::parse($insight->published_at)->translatedFormat('d F Y');
        } catch (\Throwable $e) {
            return (string) $insight->published_at;
        }
    };

    $excerpt = function ($insight, int $limit = 150): string {
        return Str::limit(
            $insight?->excerpt ?: strip_tags($insight?->content ?? 'Insight Edulaw Project tentang isu hukum dan kebijakan publik.'),
            $limit
        );
    };

    $authorInitial = function ($insight) use ($authorName): string {
        return Str::upper(Str::substr($authorName($insight), 0, 1));
    };

    $fallbackPalettes = [
        ['from' => '#061A3D', 'via' => '#1F3C69', 'to' => '#496987', 'accent' => '#F5B943'],
        ['from' => '#102A43', 'via' => '#1E5F74', 'to' => '#25B7A0', 'accent' => '#F5B943'],
        ['from' => '#111827', 'via' => '#2B313E', 'to' => '#AEB9C9', 'accent' => '#F5B943'],
        ['from' => '#0B1F33', 'via' => '#38506F', 'to' => '#F5B943', 'accent' => '#FFFFFF'],
        ['from' => '#1F2937', 'via' => '#1F3C69', 'to' => '#38A4D8', 'accent' => '#F5B943'],
        ['from' => '#172033', 'via' => '#4B5563', 'to' => '#25B7A0', 'accent' => '#FFF3CF'],
    ];

    $fallbackPalette = function ($seedValue, int $index = 0) use ($fallbackPalettes): array {
        if (is_object($seedValue)) {
            $seedText = ($seedValue->slug ?? '').'|'.($seedValue->title ?? '').'|'.($seedValue->id ?? $index);
        } else {
            $seedText = (string) $seedValue.'|'.$index;
        }

        $seed = abs(crc32($seedText));

        return $fallbackPalettes[$seed % count($fallbackPalettes)];
    };

    $latestArchiveUrl = route('insights.index', ['archive' => 'latest']).'#insight-archive';
    $editorialArchiveUrl = route('insights.index', ['featured' => 1]).'#insight-archive';

    $trendingIssues = [
        [
            'title' => 'Revisi UU TNI',
            'description' => 'Membaca ulang batas kewenangan, tata kelola pertahanan, dan akuntabilitas sipil.',
        ],
        [
            'title' => 'Putusan MK',
            'description' => 'Catatan atas arah konstitusionalitas, dampak putusan, dan tindak lanjut kebijakan.',
        ],
        [
            'title' => 'AI & Hukum',
            'description' => 'Isu tanggung jawab, perlindungan data, dan etika teknologi dalam ruang hukum.',
        ],
        [
            'title' => 'Demokrasi Digital',
            'description' => 'Perkembangan partisipasi publik, platform digital, dan perlindungan kebebasan sipil.',
        ],
    ];
@endphp

<div class="bg-slate-50">
    <x-shared.page-header
        title="Insight"
        :compact="true"
        eyebrow="Kanal Insight"
        description="Analisis hukum, pembaruan regulasi, dan isu kebijakan publik yang disajikan secara ringkas, relevan, dan mudah dipahami."
        background-image="https://images.unsplash.com/photo-1589578527966-fdac0f44566c?auto=format&fit=crop&w=1800&q=85"
        background-alt="Analisis hukum dan insight Edulaw Project"
        grid-class="gap-6 px-4 py-9 sm:w-full sm:px-6 lg:min-h-[250px] lg:grid-cols-[1.02fr_0.98fr] lg:items-center lg:px-8 lg:py-12"
        content-class="lg:ml-auto lg:max-w-xl"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Insight'],
        ]"
    />

    <section class="bg-white py-12 lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                        Terbaru
                    </p>

                    <h2 class="mt-2 text-3xl font-black leading-tight text-brand-ink">
                        Tulisan hukum terbaru dari kanal Insight Edulaw.
                    </h2>
                </div>

                <a href="{{ $latestArchiveUrl }}" class="group inline-flex items-center gap-2 text-sm font-black text-brand-navy transition hover:text-brand-ink">
                    Lihat semua terbaru
                    <span class="transition group-hover:translate-x-1">→</span>
                </a>
            </div>

            @if ($latestLead)
                @php
                    $leadImage = $insightImage($latestLead);
                    $leadPalette = $fallbackPalette($latestLead, 0);
                @endphp

                <div class="grid gap-6 lg:grid-cols-[minmax(0,1.08fr)_minmax(340px,0.92fr)]">
                    <article class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-900/5 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10">
                        <a href="{{ route('insights.show', $latestLead->slug) }}" class="flex h-full flex-col">
                            <div class="relative min-h-[22rem] overflow-hidden bg-slate-100 sm:min-h-[26rem] lg:min-h-[30rem]">
                                <div
                                    class="insight-visual-fallback"
                                    style="background: linear-gradient(135deg, {{ $leadPalette['from'] }} 0%, {{ $leadPalette['via'] }} 52%, {{ $leadPalette['to'] }} 100%);"
                                >
                                    <div>
                                        <span class="insight-fallback-mark" style="background: {{ $leadPalette['accent'] }};"></span>
                                        <p class="mt-5 max-w-48 text-xs font-black uppercase tracking-[0.22em] text-white/75">
                                            Edulaw Insight
                                        </p>
                                    </div>

                                    <strong class="max-w-72 text-2xl font-black leading-tight text-white">
                                        {{ $categoryName($latestLead) }}
                                    </strong>
                                </div>

                                @if ($leadImage)
                                    <img
                                        src="{{ $leadImage }}"
                                        alt="{{ $latestLead->title }}"
                                        loading="lazy"
                                        class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105"
                                        onerror="this.remove()"
                                    >
                                @endif

                                <div class="absolute inset-0 bg-linear-to-t from-brand-navy/72 via-brand-navy/18 to-transparent"></div>

                                <span class="absolute left-5 top-5 edulaw-badge edulaw-badge-amber-solid">
                                    {{ $categoryName($latestLead) }}
                                </span>
                            </div>

                            <div class="flex flex-1 flex-col p-6 sm:p-8">
                                <div class="flex flex-wrap items-center gap-2 text-xs font-black uppercase tracking-[0.16em] text-slate-500">
                                    <span>{{ $publishedDate($latestLead) }}</span>
                                    <span class="text-slate-300">/</span>
                                    <span>{{ $readingTime($latestLead) }}</span>
                                </div>

                                <h3 class="mt-4 text-3xl font-black leading-tight text-brand-ink transition group-hover:text-brand-navy sm:text-4xl">
                                    {{ $latestLead->title }}
                                </h3>

                                <p class="mt-4 max-w-3xl text-base leading-8 text-slate-600">
                                    {{ $excerpt($latestLead, 230) }}
                                </p>

                                <div class="mt-auto flex flex-col gap-5 pt-7 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-mist text-sm font-black text-brand-navy">
                                            {{ $authorInitial($latestLead) }}
                                        </div>

                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-black text-brand-ink">
                                                {{ $authorName($latestLead) }}
                                            </p>

                                            <p class="text-xs font-semibold text-slate-500">
                                                Kanal editorial Edulaw
                                            </p>
                                        </div>
                                    </div>

                                    <span class="inline-flex items-center gap-2 text-sm font-black text-brand-navy">
                                        Baca selengkapnya <span class="transition group-hover:translate-x-1">→</span>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </article>

                    <div class="grid gap-4 lg:grid-rows-3">
                        @forelse ($latestSide as $sideIndex => $item)
                            @php
                                $sideImage = $insightImage($item);
                                $sidePalette = $fallbackPalette($item, $sideIndex + 1);
                            @endphp

                            <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-900/5 transition duration-300 hover:-translate-y-0.5 hover:border-brand-silver hover:shadow-lg hover:shadow-slate-900/10">
                                <a href="{{ route('insights.show', $item->slug) }}" class="grid min-h-[11.25rem] grid-cols-[132px_minmax(0,1fr)] sm:grid-cols-[190px_minmax(0,1fr)] lg:h-full">
                                    <div class="relative overflow-hidden bg-slate-100">
                                        <div
                                            class="insight-visual-fallback !p-4"
                                            style="background: linear-gradient(135deg, {{ $sidePalette['from'] }} 0%, {{ $sidePalette['via'] }} 55%, {{ $sidePalette['to'] }} 100%);"
                                        >
                                            <span class="insight-fallback-mark !h-1.5 !w-9" style="background: {{ $sidePalette['accent'] }};"></span>
                                            <strong class="text-sm font-black leading-tight text-white">
                                                {{ $categoryName($item) }}
                                            </strong>
                                        </div>

                                        @if ($sideImage)
                                            <img
                                                src="{{ $sideImage }}"
                                                alt="{{ $item->title }}"
                                                loading="lazy"
                                                class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                                onerror="this.remove()"
                                            >
                                        @endif

                                        <div class="absolute inset-0 bg-brand-navy/12"></div>
                                    </div>

                                    <div class="flex min-w-0 flex-col p-4 sm:p-5">
                                        <p class="text-[10px] font-black uppercase tracking-[0.15em] text-brand-navy">
                                            {{ $categoryName($item) }}
                                        </p>

                                        <h3 class="insight-clamp-2 mt-2 text-base font-black leading-snug text-brand-ink transition group-hover:text-brand-navy sm:text-lg">
                                            {{ $item->title }}
                                        </h3>

                                        <div class="mt-auto flex flex-wrap items-center gap-x-2 gap-y-1 pt-4 text-xs font-semibold text-slate-500">
                                            <span>{{ $publishedDate($item) }}</span>
                                            <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                            <span>{{ $readingTime($item) }}</span>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6">
                                <p class="text-sm font-semibold leading-7 text-slate-500">
                                    Artikel berikutnya akan tampil setelah lebih banyak insight diterbitkan.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @else
                <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                        Belum Ada Insight
                    </p>

                    <h3 class="mt-3 text-2xl font-black text-brand-ink">
                        Tulisan terbaru akan tampil di sini.
                    </h3>

                    <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-slate-600">
                        Setelah artikel berstatus published tersedia, laman ini akan otomatis menampilkan susunan editorial terbaru.
                    </p>
                </div>
            @endif
        </div>
    </section>

    <section class="bg-brand-paper py-12 lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-7">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                    Kanal
                </p>

                <h2 class="mt-2 text-3xl font-black leading-tight text-brand-ink">
                    Jelajahi Kanal Insight
                </h2>
            </div>

            <div class="grid gap-5 lg:grid-cols-2">
                @foreach ($insightChannels as $channelIndex => $channel)
                    @php
                        $channelArticles = collect($channel['articles'] ?? []);
                        $channelLead = $channelArticles->first();
                        $channelImage = $insightImage($channelLead);
                        $channelPalette = $fallbackPalette($channelLead ?: $channel['label'], $channelIndex);
                    @endphp

                    <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-900/5">
                        <div class="grid sm:grid-cols-[180px_minmax(0,1fr)]">
                            <a href="{{ $channel['url'] }}" class="relative min-h-[13rem] overflow-hidden bg-slate-100 sm:min-h-full">
                                <div
                                    class="insight-visual-fallback"
                                    style="background: linear-gradient(135deg, {{ $channelPalette['from'] }} 0%, {{ $channelPalette['via'] }} 52%, {{ $channelPalette['to'] }} 100%);"
                                >
                                    <span class="insight-fallback-mark" style="background: {{ $channelPalette['accent'] }};"></span>
                                    <strong class="max-w-44 text-xl font-black leading-tight text-white">
                                        {{ $channel['label'] }}
                                    </strong>
                                </div>

                                @if ($channelImage)
                                    <img
                                        src="{{ $channelImage }}"
                                        alt="{{ $channel['label'] }}"
                                        loading="lazy"
                                        class="absolute inset-0 h-full w-full object-cover"
                                        onerror="this.remove()"
                                    >
                                    <div class="absolute inset-0 bg-linear-to-t from-brand-navy/55 via-brand-navy/10 to-transparent"></div>
                                @endif
                            </a>

                            <div class="p-5 sm:p-6">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-amber-soft text-brand-navy">
                                        @switch($channel['icon'])
                                            @case('book')
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M6 4h9a3 3 0 0 1 3 3v13H8a3 3 0 0 0-3-3V5a1 1 0 0 1 1-1Zm2 0v13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                @break

                                            @case('column')
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M5 20h14M7 9v8m5-8v8m5-8v8M4 7l8-4 8 4H4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                @break

                                            @case('document')
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M7 3h7l4 4v14H7V3Zm7 0v5h4M9.5 12h5M9.5 16h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                @break

                                            @default
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="m12 3 1.7 5.2H19l-4.3 3.1 1.6 5.2L12 13.3l-4.3 3.2 1.6-5.2L5 8.2h5.3L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                                </svg>
                                        @endswitch
                                    </div>

                                    <div class="min-w-0">
                                        <h3 class="text-xl font-black leading-tight text-brand-ink">
                                            {{ $channel['label'] }}
                                        </h3>
                                    </div>
                                </div>

                                <div class="mt-5 space-y-3">
                                    @forelse ($channelArticles as $article)
                                        <a href="{{ route('insights.show', $article->slug) }}" class="group block border-b border-slate-100 pb-3 last:border-b-0 last:pb-0">
                                            <h4 class="insight-clamp-2 text-sm font-black leading-snug text-brand-ink transition group-hover:text-brand-navy">
                                                {{ $article->title }}
                                            </h4>

                                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                                {{ $publishedDate($article) }} · {{ $readingTime($article) }}
                                            </p>
                                        </a>
                                    @empty
                                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-4">
                                            <p class="text-sm leading-6 text-slate-500">
                                                Belum ada artikel published untuk kanal ini.
                                            </p>
                                        </div>
                                    @endforelse
                                </div>

                                <a href="{{ $channel['url'] }}#insight-archive" class="mt-5 inline-flex items-center gap-2 text-sm font-black text-brand-navy transition hover:text-brand-ink">
                                    Lihat semua {{ $channel['label'] }} <span>→</span>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-[#edf3f8] py-14 lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-[2rem] border border-white/80 bg-white/70 p-5 shadow-sm shadow-slate-900/5 sm:p-6 lg:p-8">
                <div class="mb-7 flex flex-col gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                            KURASI
                        </p>

                        <h2 class="mt-2 text-3xl font-black leading-tight text-brand-ink">
                            Editorial Pick
                        </h2>

                        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">
                            Pilihan tulisan dari redaksi Edulaw untuk membaca isu hukum dengan konteks yang lebih utuh.
                        </p>
                    </div>

                    <a href="{{ $editorialArchiveUrl }}" class="group inline-flex items-center gap-2 text-sm font-black text-brand-navy transition hover:text-brand-ink">
                        Lihat semua editorial pick
                        <span class="transition group-hover:translate-x-1">→</span>
                    </a>
                </div>

                @if ($editorialPicks->isNotEmpty())
                    <div class="grid items-stretch gap-5 md:grid-cols-2 lg:grid-cols-4">
                        <aside class="flex h-full min-h-0 flex-col overflow-hidden rounded-3xl bg-linear-to-br from-[#061A3D] via-brand-navy to-[#29496f] p-6 text-white shadow-xl shadow-brand-navy/15 md:col-span-2 lg:col-span-1 lg:min-h-[34rem]">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-amber">
                                    Kurasi Edulaw
                                </p>

                                <span class="mt-5 block h-1 w-14 rounded-full bg-brand-amber"></span>

                                <h3 class="mt-7 text-3xl font-black leading-tight text-white">
                                    Pilihan Redaksi
                                </h3>

                                <p class="mt-4 text-sm leading-7 text-white/78">
                                    Kumpulan analisis pilihan untuk memahami hukum, kebijakan publik, dan tata kelola negara secara lebih jernih.
                                </p>
                            </div>

                            <div class="mt-7 space-y-3">
                                @foreach (['Konteks isu', 'Argumentasi hukum', 'Dampak kebijakan'] as $point)
                                    <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/8 px-3.5 py-3">
                                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-amber text-brand-black">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>

                                        <span class="text-sm font-black text-white">
                                            {{ $point }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                            <a href="{{ $editorialArchiveUrl }}" class="group mt-auto inline-flex items-center gap-2 pt-8 text-sm font-black text-brand-amber transition hover:text-white">
                                Lihat kurasi lainnya
                                <span class="transition group-hover:translate-x-1">→</span>
                            </a>
                        </aside>

                        @foreach ($editorialPicks as $pickIndex => $pick)
                            @php
                                $pickImage = $insightImage($pick);
                                $pickPalette = $fallbackPalette($pick, $pickIndex + 6);
                            @endphp

                            <article class="group flex h-full overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-900/5 ring-1 ring-transparent transition duration-300 hover:-translate-y-1 hover:border-brand-amber/60 hover:shadow-xl hover:shadow-slate-900/10 hover:ring-brand-amber/20">
                                <a href="{{ route('insights.show', $pick->slug) }}" class="flex min-h-full w-full flex-col">
                                    <div class="relative h-44 overflow-hidden bg-slate-100 sm:h-48">
                                        <div
                                            class="insight-visual-fallback"
                                            style="background: linear-gradient(135deg, {{ $pickPalette['from'] }} 0%, {{ $pickPalette['via'] }} 52%, {{ $pickPalette['to'] }} 100%);"
                                        >
                                            <span class="insight-fallback-mark" style="background: {{ $pickPalette['accent'] }};"></span>
                                            <strong class="max-w-48 text-lg font-black leading-tight text-white">
                                                {{ $categoryName($pick) }}
                                            </strong>
                                        </div>

                                        @if ($pickImage)
                                            <img
                                                src="{{ $pickImage }}"
                                                alt="{{ $pick->title }}"
                                                loading="lazy"
                                                class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105"
                                                onerror="this.remove()"
                                            >
                                        @endif

                                        <div class="absolute inset-0 bg-linear-to-t from-brand-navy/58 via-transparent to-transparent"></div>

                                        <span class="absolute left-4 top-4 edulaw-badge edulaw-badge-amber-solid">
                                            Pilihan Editor
                                        </span>
                                    </div>

                                    <div class="flex flex-1 flex-col p-5">
                                        <div class="flex flex-wrap items-center gap-2 text-[11px] font-black uppercase tracking-[0.12em]">
                                            <span class="edulaw-badge edulaw-badge-amber">
                                                {{ $categoryName($pick) }}
                                            </span>

                                            <span class="text-slate-400">
                                                {{ $publishedDate($pick) }}
                                            </span>
                                        </div>

                                        <h3 class="insight-clamp-3 mt-3 text-lg font-black leading-tight text-brand-ink transition group-hover:text-brand-navy">
                                            {{ $pick->title }}
                                        </h3>

                                        <p class="insight-clamp-2 mt-3 text-sm leading-6 text-slate-600">
                                            {{ $excerpt($pick, 118) }}
                                        </p>

                                        <div class="mt-auto pt-5">
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-mist text-xs font-black text-brand-navy">
                                                    {{ $authorInitial($pick) }}
                                                </div>

                                                <div class="min-w-0">
                                                    <p class="truncate text-xs font-black text-brand-ink">
                                                        {{ $authorName($pick) }}
                                                    </p>

                                                    <p class="text-[11px] font-semibold text-slate-500">
                                                        {{ $readingTime($pick) }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="mt-5 inline-flex items-center gap-2 text-sm font-black text-brand-navy">
                                                Baca pilihan <span class="transition group-hover:translate-x-1">→</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center">
                        <p class="text-sm leading-7 text-slate-500">
                            Pilihan editor akan tampil setelah artikel published tersedia.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="border-t border-slate-200 bg-slate-50 py-16 lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-3">
                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-900/5">
                    <h2 class="text-xl font-black leading-tight text-brand-ink">
                        Paling Banyak Dibaca
                    </h2>

                    <div class="mt-5 space-y-4">
                        @forelse ($popularInsights->take(3) as $popularIndex => $popular)
                            <a href="{{ route('insights.show', $popular->slug) }}" class="group grid grid-cols-[2rem_minmax(0,1fr)] gap-3 border-b border-slate-100 pb-4 last:border-b-0 last:pb-0">
                                <div class="font-display text-2xl font-black leading-none text-brand-navy">
                                    {{ $popularIndex + 1 }}
                                </div>

                                <div class="min-w-0">
                                    <h3 class="insight-clamp-2 text-sm font-black leading-snug text-brand-ink transition group-hover:text-brand-navy">
                                        {{ $popular->title }}
                                    </h3>

                                    <p class="mt-1 text-xs font-semibold text-slate-500">
                                        {{ $categoryName($popular) }} · {{ $publishedDate($popular) }}
                                    </p>
                                </div>
                            </a>
                        @empty
                            <p class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-500">
                                Daftar populer akan tampil setelah ada artikel published.
                            </p>
                        @endforelse
                    </div>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-900/5">
                    <h2 class="text-xl font-black leading-tight text-brand-ink">
                        Kategori Insight
                    </h2>

                    <div class="mt-5 flex flex-wrap gap-2">
                        @forelse ($insightCategories as $category)
                            <a
                                href="{{ route('insights.index', ['category' => $category->slug]) }}#insight-archive"
                                class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-black text-brand-navy transition hover:border-brand-navy hover:bg-brand-navy hover:text-white"
                            >
                                <span>{{ $category->name }}</span>

                                @if (isset($category->published_insights_count))
                                    <span class="rounded-full bg-brand-mist px-2 py-0.5 text-[10px] text-brand-ink group-hover:bg-white/20">
                                        {{ $category->published_insights_count }}
                                    </span>
                                @endif
                            </a>
                        @empty
                            <p class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-500">
                                Kategori aktif akan tampil setelah ditambahkan dari dashboard.
                            </p>
                        @endforelse
                    </div>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-900/5">
                    <h2 class="text-xl font-black leading-tight text-brand-ink">
                        Trending Issue
                    </h2>

                    <div class="mt-5 space-y-4">
                        @foreach ($trendingIssues as $issue)
                            <a href="{{ route('insights.index', ['q' => $issue['title']]) }}#insight-archive" class="group flex items-center justify-between gap-4 border-b border-slate-100 pb-4 last:border-b-0 last:pb-0">
                                <div class="min-w-0">
                                    <h3 class="text-sm font-black text-brand-ink transition group-hover:text-brand-navy">
                                        {{ $issue['title'] }}
                                    </h3>

                                    <p class="mt-1 text-xs leading-5 text-slate-500">
                                        {{ $issue['description'] }}
                                    </p>
                                </div>

                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-amber-soft text-brand-navy transition group-hover:bg-brand-navy group-hover:text-white">
                                    <svg class="h-4 w-4 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </article>
            </div>
        </div>
    </section>

    @if ($showFilteredArchive)
        <section id="insight-archive" class="border-t border-slate-200 bg-white py-12 lg:py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                            Arsip Insight
                        </p>

                        <h2 class="mt-2 text-3xl font-black leading-tight text-brand-ink">
                            {{ $featuredOnly ? 'Semua Editorial Pick' : 'Hasil Jelajah Insight' }}
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            {{ number_format($archiveTotal, 0, ',', '.') }} tulisan ditemukan.
                        </p>
                    </div>

                    <a href="{{ route('insights.index') }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-black text-brand-navy transition hover:bg-brand-paper">
                        Reset tampilan
                    </a>
                </div>

                <form method="GET" action="{{ route('insights.index') }}#insight-archive" class="grid gap-4 rounded-3xl border border-slate-200 bg-slate-50 p-4 lg:grid-cols-[minmax(0,1fr)_420px] lg:items-center">
                    <input type="hidden" name="archive" value="latest">

                    @if ($featuredOnly)
                        <input type="hidden" name="featured" value="1">
                    @endif

                    <div class="insight-control-scroll flex gap-2 overflow-x-auto pb-1">
                        <a
                            href="{{ route('insights.index', array_filter(['q' => $search ?: null, 'featured' => $featuredOnly ? 1 : null, 'archive' => 'latest'])) }}#insight-archive"
                            class="shrink-0 rounded-full border px-5 py-2.5 text-sm font-bold shadow-sm transition
                                {{ blank($selectedCategory)
                                    ? 'border-brand-navy bg-brand-navy text-white'
                                    : 'border-slate-200 bg-white text-brand-ink hover:border-brand-silver hover:bg-brand-paper' }}"
                        >
                            Semua
                        </a>

                        @foreach ($insightCategories as $category)
                            <a
                                href="{{ route('insights.index', array_filter(['category' => $category->slug, 'q' => $search ?: null, 'featured' => $featuredOnly ? 1 : null, 'archive' => 'latest'])) }}#insight-archive"
                                class="shrink-0 rounded-full border px-5 py-2.5 text-sm font-bold shadow-sm transition
                                    {{ $selectedCategory === $category->slug
                                        ? 'border-brand-navy bg-brand-navy text-white'
                                        : 'border-slate-200 bg-white text-brand-ink hover:border-brand-silver hover:bg-brand-paper' }}"
                            >
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>

                    <div class="relative">
                        @if ($selectedCategory)
                            <input type="hidden" name="category" value="{{ $selectedCategory }}">
                        @endif

                        <input
                            type="search"
                            name="q"
                            value="{{ $search }}"
                            placeholder="Cari judul, penulis, atau topik hukum..."
                            class="block w-full rounded-full border border-slate-200 bg-white py-3 pl-5 pr-14 text-sm font-semibold text-brand-ink outline-none transition placeholder:text-slate-400 focus:border-brand-silver focus:ring-4 focus:ring-brand-mist"
                        >

                        <button
                            type="submit"
                            aria-label="Cari insight"
                            class="absolute right-2 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full text-brand-ink transition hover:bg-brand-paper"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m21 21-4.3-4.3M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                </form>

                <div class="mt-7 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @forelse ($archiveItems as $archiveIndex => $insight)
                        @php
                            $archiveImage = $insightImage($insight);
                            $archivePalette = $fallbackPalette($insight, $archiveIndex + 12);
                        @endphp

                        <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-900/5 transition duration-300 hover:-translate-y-1 hover:border-brand-silver hover:shadow-xl hover:shadow-slate-900/10">
                            <a href="{{ route('insights.show', $insight->slug) }}" class="flex h-full flex-col">
                                <div class="relative h-52 overflow-hidden bg-slate-100">
                                    <div
                                        class="insight-visual-fallback"
                                        style="background: linear-gradient(135deg, {{ $archivePalette['from'] }} 0%, {{ $archivePalette['via'] }} 52%, {{ $archivePalette['to'] }} 100%);"
                                    >
                                        <span class="insight-fallback-mark" style="background: {{ $archivePalette['accent'] }};"></span>
                                        <strong class="max-w-56 text-lg font-black leading-tight text-white">
                                            {{ $categoryName($insight) }}
                                        </strong>
                                    </div>

                                    @if ($archiveImage)
                                        <img
                                            src="{{ $archiveImage }}"
                                            alt="{{ $insight->title }}"
                                            loading="lazy"
                                            class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                            onerror="this.remove()"
                                        >
                                    @endif

                                    <div class="absolute inset-0 bg-linear-to-t from-brand-navy/45 via-transparent to-transparent"></div>
                                </div>

                                <div class="flex flex-1 flex-col p-5">
                                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-brand-navy">
                                        {{ $categoryName($insight) }}
                                    </p>

                                    <h3 class="insight-clamp-2 mt-3 text-xl font-black leading-tight text-brand-ink transition group-hover:text-brand-navy">
                                        {{ $insight->title }}
                                    </h3>

                                    <p class="insight-clamp-3 mt-3 text-sm leading-6 text-slate-600">
                                        {{ $excerpt($insight) }}
                                    </p>

                                    <div class="mt-auto pt-5 text-xs font-semibold text-slate-500">
                                        {{ $publishedDate($insight) }} · {{ $readingTime($insight) }}
                                    </div>
                                </div>
                            </a>
                        </article>
                    @empty
                        <div class="md:col-span-2 lg:col-span-3">
                            <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                                    Tidak Ada Insight
                                </p>

                                <h3 class="mt-3 text-2xl font-black text-brand-ink">
                                    Insight belum ditemukan.
                                </h3>

                                <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-slate-600">
                                    Coba gunakan kata kunci lain, pilih kategori berbeda, atau hapus filter untuk melihat seluruh insight.
                                </p>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if ($insights instanceof \Illuminate\Pagination\AbstractPaginator && $insights->hasPages())
                    <div class="mt-10 flex flex-col gap-4 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm font-semibold text-slate-500">
                            Halaman
                            <span class="font-black text-brand-ink">{{ $insights->currentPage() }}</span>
                            dari
                            <span class="font-black text-brand-ink">{{ $insights->lastPage() }}</span>
                        </p>

                        <div class="flex items-center gap-3">
                            @if ($insights->onFirstPage())
                                <span class="inline-flex cursor-not-allowed items-center rounded-full border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-black text-slate-300">
                                    ← Sebelumnya
                                </span>
                            @else
                                <a
                                    href="{{ $insights->previousPageUrl() }}#insight-archive"
                                    class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-black text-brand-navy transition hover:border-brand-navy hover:bg-brand-paper"
                                >
                                    ← Sebelumnya
                                </a>
                            @endif

                            @if ($insights->hasMorePages())
                                <a
                                    href="{{ $insights->nextPageUrl() }}#insight-archive"
                                    class="inline-flex items-center rounded-full border border-brand-navy bg-brand-navy px-4 py-2 text-sm font-black text-white transition hover:bg-brand-ink"
                                >
                                    Berikutnya →
                                </a>
                            @else
                                <span class="inline-flex cursor-not-allowed items-center rounded-full border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-black text-slate-300">
                                    Berikutnya →
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @endif

    <x-shared.cta-section
        eyebrow="Kolaborasi Insight"
        title="Punya isu hukum yang perlu dijelaskan ke publik?"
        body="Edulaw Project terbuka untuk mengembangkan artikel, serial edukasi, diskusi, dan materi literasi hukum berbasis isu aktual."
        :primary-url="route('collaboration.index')"
        primary-label="Ajukan Kolaborasi"
        :secondary-url="route('contact.index')"
        secondary-label="Hubungi Kami"
    />
</div>
@endsection
