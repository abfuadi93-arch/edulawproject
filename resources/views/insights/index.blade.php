@extends('layouts.app')

@section('title', 'Editorial - Edulaw Project')

@push('styles')
<style>
    .insight-clamp-1,
    .insight-clamp-2,
    .insight-clamp-3 {
        display: -webkit-box;
        overflow: hidden;
        -webkit-box-orient: vertical;
    }

    .insight-clamp-1 {
        -webkit-line-clamp: 1;
    }

    .insight-clamp-2 {
        -webkit-line-clamp: 2;
    }

    .insight-clamp-3 {
        -webkit-line-clamp: 3;
    }

    .insight-fallback {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: flex-end;
        padding: 1rem;
        color: #fff;
    }

    .insight-fallback::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255, 255, 255, .08), transparent 45%, rgba(0, 0, 0, .18));
    }

    .insight-scroll {
        scrollbar-width: thin;
        scrollbar-color: rgba(31, 60, 105, .28) transparent;
    }

    .insight-scroll::-webkit-scrollbar {
        height: 6px;
    }

    .insight-scroll::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: rgba(31, 60, 105, .25);
    }

    .insight-content :where(section, article, div, a, form, h1, h2, h3, p, span) {
        max-width: 100%;
        min-width: 0;
    }

    .insight-content :where(h1, h2, h3, p) {
        overflow-wrap: anywhere;
    }

    .editorial-full-bleed {
        margin-left: calc(50% - 50vw);
        margin-right: calc(50% - 50vw);
        max-width: none;
        width: auto;
        background: #fbf7ef;
    }

    .insight-content .editorial-navbar-shell {
        max-width: 80rem;
    }

    .insight-content .editorial-slider-shell {
        max-width: 80rem;
    }

    .editorial-pick-slider {
        scrollbar-width: none;
    }

    .editorial-pick-slider::-webkit-scrollbar {
        display: none;
    }

    @media (min-width: 1280px) {
        .editorial-pick-slider {
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\HtmlString;
    use Illuminate\Support\Str;

    $latestInsights = collect($latestInsights ?? []);
    $insightChannels = collect($insightChannels ?? []);
    $editorialPicks = collect($editorialPicks ?? []);
    $popularInsights = collect($popularInsights ?? []);
    $popularTags = collect($popularTags ?? []);
    $selectedCategory = $selectedCategory ?? request('category');
    $search = $search ?? request('q', '');
    $featuredOnly = (bool) ($featuredOnly ?? request()->boolean('featured'));
    $showFilteredArchive = (bool) ($showFilteredArchive ?? false);

    $archiveItems = $insights instanceof \Illuminate\Pagination\AbstractPaginator
        ? $insights->getCollection()
        : collect($insights ?? []);

    $insightImage = fn ($insight): ?string => $insight?->cover_image_url;

    $categoryName = function ($insight, string $fallback = 'Editorial'): string {
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
            ?? 'Edulaw Editorial';
    };

    $readingTime = function ($insight): string {
        if (! empty($insight?->reading_time)) {
            return $insight->reading_time.' menit baca';
        }

        $words = str_word_count(trim(strip_tags(($insight?->content ?? '').' '.($insight?->excerpt ?? ''))));

        return max(1, (int) ceil($words / 200)).' menit baca';
    };

    $publishedDate = function ($insight): string {
        if (empty($insight?->published_at)) {
            return 'Belum dijadwalkan';
        }

        try {
            return Carbon::parse($insight->published_at)->translatedFormat('d M Y');
        } catch (\Throwable $e) {
            return (string) $insight->published_at;
        }
    };

    $excerpt = fn ($insight, int $limit = 150): string => Str::limit(
        $insight?->excerpt ?: strip_tags($insight?->content ?? 'Editorial Edulaw Project tentang isu hukum dan kebijakan publik.'),
        $limit
    );

    $fallbackPalettes = [
        ['from' => '#061A3D', 'via' => '#1F3C69', 'to' => '#496987', 'accent' => '#F5B943'],
        ['from' => '#102A43', 'via' => '#1E5F74', 'to' => '#25B7A0', 'accent' => '#F5B943'],
        ['from' => '#111827', 'via' => '#2B313E', 'to' => '#AEB9C9', 'accent' => '#F5B943'],
        ['from' => '#0B1F33', 'via' => '#38506F', 'to' => '#F5B943', 'accent' => '#FFFFFF'],
        ['from' => '#1F2937', 'via' => '#1F3C69', 'to' => '#38A4D8', 'accent' => '#F5B943'],
    ];

    $fallbackPalette = function ($seedValue, int $index = 0) use ($fallbackPalettes): array {
        $seedText = is_object($seedValue)
            ? (($seedValue->slug ?? '').'|'.($seedValue->title ?? '').'|'.($seedValue->id ?? $index))
            : ((string) $seedValue.'|'.$index);

        return $fallbackPalettes[abs(crc32($seedText)) % count($fallbackPalettes)];
    };

    $allInsights = $latestInsights
        ->concat($archiveItems)
        ->concat($popularInsights)
        ->concat($editorialPicks)
        ->filter()
        ->unique('id')
        ->values();

    $lead = $allInsights->first();
    $leadCompanion = $allInsights->skip(1)->first();
    $latestNews = $allInsights->skip(1)->take(4)->values();
    $mustReadLeft = $allInsights->skip(5)->first() ?: $lead;
    $mustReadMain = $allInsights->skip(6)->first() ?: $leadCompanion ?: $lead;
    $mustReadSide = $allInsights->skip(7)->take(2)->values();
    $editorialRail = $editorialPicks->filter()->take(5)->values();

    $adminCategoryChannels = $insightChannels
        ->filter(fn (array $channel): bool => filled($channel['category'] ?? null))
        ->sortBy(fn (array $channel): int => (int) ($channel['category']?->sort_order ?? 99))
        ->values();

    $categoryBlocks = $adminCategoryChannels
        ->take(4)
        ->map(function (array $channel): array {
            $category = $channel['category'] ?? null;
            $title = $category?->name ?? ($channel['label'] ?? 'Editorial');

            return [
                'title' => $title,
                'description' => $category?->description ?: ($channel['description'] ?? null),
                'items' => collect($channel['articles'] ?? [])->take(4)->values(),
                'url' => ($channel['url'] ?? route('insights.index', ['q' => $title, 'archive' => 'latest'])).'#insight-archive',
            ];
        })
        ->values();

    $toolbarChannels = $adminCategoryChannels;

    $latestArchiveUrl = route('insights.index', ['archive' => 'latest']).'#insight-archive';
    $editorialArchiveUrl = route('insights.index', ['featured' => 1]).'#insight-archive';

    $renderImage = function ($item, int $index, string $class = 'absolute inset-0 h-full w-full object-cover') use ($insightImage, $fallbackPalette): HtmlString {
        $image = $insightImage($item);
        $palette = $fallbackPalette($item ?: 'insight', $index);
        $title = e($item?->title ?? 'Editorial Edulaw Project');
        $html = '<div class="insight-fallback" style="background: linear-gradient(135deg, '.e($palette['from']).' 0%, '.e($palette['via']).' 52%, '.e($palette['to']).' 100%);"></div>';

        if ($image) {
            $html .= '<img src="'.e($image).'" alt="'.$title.'" loading="lazy" class="'.e($class).'" onerror="this.remove()">';
        }

        return new HtmlString($html);
    };

    $authors = $allInsights
        ->flatMap(fn ($item) => $item && $item->relationLoaded('authors') ? $item->authors : collect())
        ->filter(fn ($author) => $author && $author->is_active !== false)
        ->unique('id')
        ->take(5)
        ->values();

    $authorFallbacks = collect([
        ['name' => 'Edulaw Editorial', 'role' => 'Editorial'],
        ['name' => 'Legal Research Team', 'role' => 'Riset Hukum'],
        ['name' => 'Policy Desk', 'role' => 'Kebijakan Publik'],
        ['name' => 'Program Team', 'role' => 'Literasi Hukum'],
        ['name' => 'Community Writer', 'role' => 'Opini Publik'],
    ]);
@endphp

<main class="bg-[#E7E7E7] text-brand-ink">
    <x-shared.page-header
        title="Welcome to Edulaw Editorial"
        :compact="true"
        eyebrow="Kanal Editorial"
        description="Narasi hukum yang menyalakan pemahaman, memperkuat kebijakan, dan membuka ruang diskusi publik."
        background-image="https://images.unsplash.com/photo-1589578527966-fdac0f44566c?auto=format&fit=crop&w=1800&q=85"
        background-alt="Analisis hukum dan diskusi kebijakan publik Edulaw Editorial"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Editorial'],
        ]"
    >
        <div class="flex flex-col gap-3 sm:flex-row">
            <a
                href="#insight-latest"
                class="inline-flex min-h-12 items-center justify-center rounded-full bg-brand-amber px-6 py-3 text-sm font-black text-brand-ink shadow-lg shadow-black/20 transition hover:-translate-y-0.5 hover:bg-[#D99A25]"
            >
                Baca Editorial Terbaru
            </a>

            <a
                href="{{ $latestArchiveUrl }}"
                class="inline-flex min-h-12 items-center justify-center rounded-full border border-white/25 bg-white/10 px-6 py-3 text-sm font-black text-white backdrop-blur transition hover:-translate-y-0.5 hover:border-brand-amber hover:bg-white/15"
            >
                Jelajahi Arsip
            </a>
        </div>
    </x-shared.page-header>

    <div class="py-7 sm:py-10">
        <div class="insight-content mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
        <section class="grid gap-8 lg:grid-cols-[minmax(0,1.12fr)_minmax(360px,0.88fr)] lg:items-center">
            <a href="{{ $lead ? route('insights.show', $lead->slug) : '#insight-latest' }}" class="group relative aspect-[16/10] min-h-[260px] overflow-hidden rounded-xl bg-slate-100 shadow-[0_18px_55px_rgba(15,23,42,0.10)] sm:min-h-[360px] lg:min-h-[440px]">
                {!! $renderImage($lead, 1, 'absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.03]') !!}
                <span class="absolute inset-0 bg-linear-to-t from-brand-navy/18 via-transparent to-transparent"></span>
            </a>

            <article class="py-2 lg:max-w-xl lg:pl-3">
                <div class="flex flex-wrap items-center gap-2 text-sm font-bold text-slate-500">
                    <span class="grid h-6 w-6 place-items-center rounded-full bg-brand-navy text-[11px] text-white">E</span>
                    <span>{{ $lead ? $categoryName($lead) : 'Edulaw Insight' }}</span>
                    <span>•</span>
                    <span>{{ $lead ? $publishedDate($lead) : 'Hari ini' }}</span>
                </div>

                <h2 class="mt-4 text-3xl font-black leading-[1.08] text-brand-ink sm:text-4xl lg:text-[3.25rem]">
                    <a href="{{ $lead ? route('insights.show', $lead->slug) : '#insight-latest' }}" class="underline-offset-4 hover:text-brand-navy hover:underline">
                        {{ $lead?->title ?: 'Analisis hukum, regulasi, dan kebijakan publik.' }}
                    </a>
                </h2>

                <p class="mt-5 max-w-2xl text-base leading-8 text-slate-600">
                    {{ $lead ? $excerpt($lead, 210) : 'Temukan perspektif hukum yang mendalam, analisis regulasi, dan pembaruan kebijakan publik berbasis riset.' }}
                </p>

                <p class="mt-5 text-sm font-black text-brand-coral">
                    {{ $lead ? $readingTime($lead) : '5 menit baca' }}
                </p>
            </article>
        </section>

        <section class="mt-12 bg-transparent py-3">
            <div class="grid gap-3 lg:grid-cols-2 lg:items-center">
                <form method="GET" action="{{ route('insights.index') }}#insight-archive" class="relative w-full">
                    <input type="hidden" name="archive" value="latest">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-brand-navy" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m21 21-4.3-4.3M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    <input name="q" value="{{ $search }}" type="search" placeholder="Cari editorial..." class="h-10 w-full rounded-full border border-slate-200 bg-white pl-10 pr-4 text-sm font-semibold outline-none transition focus:border-brand-navy focus:ring-4 focus:ring-brand-navy/10">
                </form>

                <div class="insight-scroll flex gap-2 overflow-x-auto pb-1 lg:justify-end lg:pb-0">
                    <a href="{{ route('insights.index') }}" class="shrink-0 whitespace-nowrap rounded-full px-4 py-2 text-xs font-black transition {{ blank($selectedCategory) && blank($search) && ! $featuredOnly ? 'bg-brand-navy text-white' : 'text-brand-navy hover:bg-slate-100' }}">
                        Semua
                    </a>

                    @foreach ($toolbarChannels as $channel)
                        @php
                            $channelCategory = $channel['category'] ?? null;
                            $toolbarChannelLabel = $channelCategory?->slug === 'edulaw-insight'
                                ? 'Edulaw Insight'
                                : ($channel['label'] ?? 'Editorial');
                            $channelUrl = $channelCategory
                                ? route('insights.index', ['category' => $channelCategory->slug]).'#insight-archive'
                                : route('insights.index', ['q' => $toolbarChannelLabel, 'archive' => 'latest']).'#insight-archive';
                            $channelActive = $channelCategory
                                ? $selectedCategory === $channelCategory->slug
                                : blank($selectedCategory) && Str::lower($search) === Str::lower($toolbarChannelLabel);
                        @endphp
                        <a href="{{ $channelUrl }}" class="shrink-0 whitespace-nowrap rounded-full px-4 py-2 text-xs font-black transition {{ $channelActive ? 'bg-brand-navy text-white' : 'text-brand-navy hover:bg-slate-100' }}">
                            {{ $toolbarChannelLabel }}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="insight-latest" class="mt-10">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-2xl font-black text-brand-ink">Latest News</h2>
                <a href="{{ $latestArchiveUrl }}" class="group inline-flex items-center gap-2 text-sm font-black text-brand-coral">
                    See all
                    <span class="transition group-hover:translate-x-1">→</span>
                </a>
            </div>

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @forelse ($latestNews as $index => $item)
                    <article class="group">
                        <a href="{{ route('insights.show', $item->slug) }}" class="relative block aspect-[4/3] overflow-hidden rounded-lg bg-slate-100">
                            {!! $renderImage($item, $index + 10) !!}
                        </a>
                        <div class="mt-3 flex items-center gap-2 text-[11px] font-bold text-slate-500">
                            <span class="h-2 w-2 rounded-full bg-brand-coral"></span>
                            <span>{{ $categoryName($item) }}</span>
                            <span>•</span>
                            <span>{{ $publishedDate($item) }}</span>
                        </div>
                        <h3 class="insight-clamp-3 mt-2 text-lg font-black leading-tight text-brand-ink underline-offset-4 group-hover:text-brand-navy group-hover:underline">
                            <a href="{{ route('insights.show', $item->slug) }}">{{ $item->title }}</a>
                        </h3>
                        <p class="mt-2 text-xs font-bold text-brand-coral">{{ $readingTime($item) }}</p>
                    </article>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-300 p-5 text-sm font-semibold text-slate-500 lg:col-span-4">
                        Artikel terbaru akan tampil setelah tersedia.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="mt-12">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-2xl font-black text-brand-ink">Must Read</h2>
                <a href="{{ $latestArchiveUrl }}" class="group inline-flex items-center gap-2 text-sm font-black text-brand-coral">
                    See all
                    <span class="transition group-hover:translate-x-1">→</span>
                </a>
            </div>

            <div class="grid gap-5 lg:grid-cols-[0.85fr_1.4fr_0.85fr]">
                @if ($mustReadLeft)
                    <article class="group">
                        <a href="{{ route('insights.show', $mustReadLeft->slug) }}" class="relative block aspect-[4/3] overflow-hidden rounded-lg bg-slate-100">
                            {!! $renderImage($mustReadLeft, 40) !!}
                        </a>
                        <div class="mt-3 flex items-center gap-2 text-[11px] font-bold text-slate-500">
                            <span class="h-2 w-2 rounded-full bg-brand-coral"></span>
                            <span>{{ $categoryName($mustReadLeft) }}</span>
                        </div>
                        <h3 class="insight-clamp-3 mt-2 text-lg font-black leading-tight text-brand-ink underline-offset-4 group-hover:text-brand-navy group-hover:underline">
                            <a href="{{ route('insights.show', $mustReadLeft->slug) }}">{{ $mustReadLeft->title }}</a>
                        </h3>
                        <p class="mt-2 text-xs font-bold text-brand-coral">{{ $readingTime($mustReadLeft) }}</p>
                    </article>
                @endif

                @if ($mustReadMain)
                    <article class="group">
                        <a href="{{ route('insights.show', $mustReadMain->slug) }}" class="relative flex min-h-[330px] items-end overflow-hidden rounded-lg bg-slate-100 p-5 text-white">
                            {!! $renderImage($mustReadMain, 41) !!}
                            <div class="absolute inset-0 bg-linear-to-t from-[#06132a]/90 via-[#06132a]/30 to-transparent"></div>
                            <div class="relative z-10">
                                <div class="mb-2 flex items-center gap-2 text-[11px] font-bold text-white/80">
                                    <span class="h-2 w-2 rounded-full bg-brand-coral"></span>
                                    <span>{{ $categoryName($mustReadMain) }}</span>
                                </div>
                                <h3 class="insight-clamp-3 text-2xl font-black leading-tight text-white underline-offset-4 group-hover:underline">
                                    {{ $mustReadMain->title }}
                                </h3>
                                <p class="insight-clamp-2 mt-3 text-sm leading-6 text-white/78">
                                    {{ $excerpt($mustReadMain, 130) }}
                                </p>
                                <p class="mt-3 text-xs font-black text-brand-amber">{{ $readingTime($mustReadMain) }}</p>
                            </div>
                        </a>
                    </article>
                @endif

                <div class="space-y-5">
                    @foreach ($mustReadSide as $index => $item)
                        <article class="group grid grid-cols-[112px_minmax(0,1fr)] gap-3">
                            <a href="{{ route('insights.show', $item->slug) }}" class="relative aspect-[4/3] overflow-hidden rounded-lg bg-slate-100">
                                {!! $renderImage($item, $index + 42) !!}
                            </a>
                            <div>
                                <div class="flex items-center gap-2 text-[11px] font-bold text-slate-500">
                                    <span class="h-2 w-2 rounded-full bg-brand-coral"></span>
                                    <span>{{ $categoryName($item) }}</span>
                                </div>
                                <h3 class="insight-clamp-3 mt-2 text-base font-black leading-tight text-brand-ink underline-offset-4 group-hover:text-brand-navy group-hover:underline">
                                    <a href="{{ route('insights.show', $item->slug) }}">{{ $item->title }}</a>
                                </h3>
                                <p class="mt-2 text-xs font-bold text-brand-coral">{{ $readingTime($item) }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="editorial-full-bleed mt-12 overflow-hidden py-10 sm:py-12">
            <div class="editorial-navbar-shell mx-auto px-5 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-xs font-black uppercase text-brand-coral">
                            Pilihan Editor
                        </p>
                        <h2 class="mt-2 font-display text-3xl font-black leading-tight text-brand-ink sm:text-4xl">
                            Editorial Pick
                        </h2>
                        <p class="mt-3 text-sm leading-6 text-slate-600 sm:text-base">
                            Tulisan pilihan redaksi untuk memahami isu hukum secara lebih jernih.
                        </p>
                    </div>

                    <a href="{{ $editorialArchiveUrl }}" class="group inline-flex w-fit items-center gap-2 text-sm font-black text-brand-navy underline-offset-4 transition hover:text-brand-coral hover:underline">
                        Lihat semua
                        <span class="transition group-hover:translate-x-1">→</span>
                    </a>
                </div>
            </div>

            @if ($editorialRail->isNotEmpty())
                <div class="editorial-slider-shell mx-auto mt-5 px-5 sm:px-6 lg:px-8">
                    <div class="relative overflow-hidden">
                        <button
                            type="button"
                            aria-label="Geser Editorial Pick ke kiri"
                            data-editorial-pick-prev
                            class="absolute left-0 top-1/2 z-10 grid h-9 w-9 -translate-x-2 -translate-y-1/2 place-items-center rounded-full border border-slate-200 bg-white text-brand-navy shadow-lg shadow-slate-900/10 transition hover:bg-brand-navy hover:text-white sm:-translate-x-4"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m15 18-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>

                        <div
                            data-editorial-pick-slider
                            class="editorial-pick-slider flex cursor-grab snap-x snap-mandatory gap-4 overflow-x-auto scroll-smooth pb-4 active:cursor-grabbing"
                        >
                            @foreach ($editorialRail as $index => $item)
                                <article class="min-w-0 shrink-0 basis-[62%] snap-start sm:basis-[calc(37.5%_-_0.75rem)] lg:basis-[calc(25%_-_0.75rem)] xl:basis-[calc(18.75%_-_0.75rem)]">
                                    <a
                                        href="{{ route('insights.show', $item->slug) }}"
                                        aria-label="Baca editorial: {{ $item->title }}"
                                        class="group/editorial flex h-full flex-col overflow-hidden rounded-[20px] border border-brand-navy/10 bg-white shadow-[0_10px_24px_rgba(15,23,42,0.055)] transition duration-300 hover:-translate-y-0.5 hover:border-brand-amber/60 hover:shadow-[0_14px_34px_rgba(15,23,42,0.09)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber"
                                    >
                                        <div class="relative aspect-[16/9] overflow-hidden bg-slate-100">
                                            {!! $renderImage($item, $index + 60, 'absolute inset-0 h-full w-full object-cover transition duration-700 group-hover/editorial:scale-105') !!}
                                            <div class="absolute inset-0 bg-linear-to-t from-brand-navy/24 via-transparent to-transparent"></div>
                                            <span class="absolute left-3 top-3 max-w-[calc(100%-1.5rem)] truncate rounded-full bg-brand-navy px-2.5 py-1 text-[9px] font-black uppercase text-white shadow-sm ring-1 ring-white/20">
                                                {{ $categoryName($item) }}
                                            </span>
                                        </div>

                                        <div class="flex flex-1 flex-col p-4">
                                            <h3 class="insight-clamp-2 text-sm font-black leading-snug text-brand-ink transition group-hover/editorial:text-brand-navy sm:text-base">
                                                {{ $item->title }}
                                            </h3>

                                            <p class="insight-clamp-2 mt-2 text-xs leading-5 text-slate-600">
                                                {{ $excerpt($item, 100) }}
                                            </p>

                                            <div class="mt-auto pt-4">
                                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[10px] font-bold uppercase text-slate-500">
                                                    <span>{{ $publishedDate($item) }}</span>
                                                    <span class="h-1 w-1 rounded-full bg-brand-amber"></span>
                                                    <span>{{ $readingTime($item) }}</span>
                                                </div>

                                                <div class="mt-2.5 flex items-center justify-between gap-3 border-t border-slate-100 pt-2.5">
                                                    <span class="insight-clamp-1 min-w-0 text-[11px] font-semibold text-slate-500">
                                                        {{ $authorName($item) }}
                                                    </span>
                                                    <span class="inline-flex shrink-0 items-center gap-1 text-[11px] font-black text-brand-navy">
                                                        Baca
                                                        <span class="transition group-hover/editorial:translate-x-1">→</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </article>
                            @endforeach
                        </div>

                        <button
                            type="button"
                            aria-label="Geser Editorial Pick ke kanan"
                            data-editorial-pick-next
                            class="absolute right-0 top-1/2 z-10 grid h-9 w-9 translate-x-2 -translate-y-1/2 place-items-center rounded-full border border-slate-200 bg-white text-brand-navy shadow-lg shadow-slate-900/10 transition hover:bg-brand-navy hover:text-white sm:translate-x-4"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @else
                <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
                    <div class="rounded-[24px] border border-dashed border-brand-navy/20 bg-white/75 p-6 text-sm font-semibold leading-6 text-slate-600">
                        Pilihan editor akan tampil setelah editorial pilihan redaksi tersedia.
                    </div>
                </div>
            @endif
        </section>

        <section class="mt-12">
            <div class="grid gap-x-10 gap-y-9 lg:grid-cols-2">
                @foreach ($categoryBlocks as $blockIndex => $block)
                    <section class="min-w-0 border-t border-slate-200 pt-5" aria-labelledby="insight-category-{{ Str::slug($block['title']) }}">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <h2 id="insight-category-{{ Str::slug($block['title']) }}" class="text-xl font-black leading-tight text-brand-ink sm:text-2xl">
                                    {{ $block['title'] }}
                                </h2>
                                @if (! empty($block['description']))
                                    <p class="insight-clamp-2 mt-1.5 text-sm leading-6 text-slate-500">
                                        {{ $block['description'] }}
                                    </p>
                                @endif
                            </div>

                            <a href="{{ $block['url'] }}" class="group/link inline-flex shrink-0 items-center gap-1.5 pt-1 text-xs font-black uppercase text-brand-navy transition hover:text-brand-coral" aria-label="Lihat semua tulisan {{ $block['title'] }}">
                                Lihat semua
                                <span class="transition group-hover/link:translate-x-1">→</span>
                            </a>
                        </div>

                        <div class="mt-3 divide-y divide-slate-200">
                            @forelse (collect($block['items'])->take(4) as $itemIndex => $item)
                                <article>
                                    <a
                                        href="{{ route('insights.show', $item->slug) }}"
                                        aria-label="Baca {{ $item->title }}"
                                        class="group/item flex gap-3 py-3.5 transition hover:bg-white/45 sm:gap-4"
                                    >
                                        <div class="relative h-[66px] w-[88px] shrink-0 overflow-hidden rounded-2xl bg-slate-100 sm:h-[72px] sm:w-[96px]">
                                            {!! $renderImage($item, 90 + ($blockIndex * 10) + $itemIndex, 'absolute inset-0 h-full w-full object-cover transition duration-500 group-hover/item:scale-105') !!}
                                        </div>

                                        <div class="min-w-0 flex-1 self-center">
                                            <h3 class="insight-clamp-2 text-sm font-bold leading-snug text-brand-ink transition group-hover/item:text-brand-navy sm:text-[15px]">
                                                {{ $item->title }}
                                            </h3>

                                            <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] font-bold text-slate-500">
                                                <span>{{ $publishedDate($item) }}</span>
                                                <span class="h-1 w-1 rounded-full bg-brand-amber"></span>
                                                <span>{{ $readingTime($item) }}</span>
                                            </div>
                                        </div>
                                    </a>
                                </article>
                            @empty
                                <div class="py-4 text-sm font-semibold leading-6 text-slate-500">
                                    Tulisan kategori ini akan tampil setelah tersedia.
                                </div>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </section>

        <section class="mt-12">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-2xl font-black text-brand-ink">Top Creator</h2>
                <a href="{{ route('about') }}" class="group inline-flex items-center gap-2 text-sm font-black text-brand-coral">
                    See all
                    <span class="transition group-hover:translate-x-1">→</span>
                </a>
            </div>

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-5">
                @forelse ($authors as $author)
                    <a href="{{ route('profiles.show', $author->slug) }}" class="flex items-center gap-3 rounded-xl transition hover:-translate-y-0.5 hover:text-brand-navy">
                        @if ($author->photo_url)
                            <img
                                src="{{ $author->photo_url }}"
                                alt="Foto profil {{ $author->name }}"
                                class="h-11 w-11 shrink-0 rounded-full object-cover ring-1 ring-slate-200"
                                loading="lazy"
                            >
                        @else
                            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-brand-navy text-sm font-black text-white">
                                {{ Str::upper(Str::substr($author->name, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <p class="font-black leading-tight text-brand-ink">{{ $author->name }}</p>
                            <p class="text-xs font-bold text-brand-coral">Contributor</p>
                        </div>
                    </a>
                @empty
                    @foreach ($authorFallbacks as $author)
                        <div class="flex items-center gap-3">
                            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-brand-navy text-sm font-black text-white">
                                {{ Str::upper(Str::substr($author['name'], 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-black leading-tight text-brand-ink">{{ $author['name'] }}</p>
                                <p class="text-xs font-bold text-brand-coral">{{ $author['role'] }}</p>
                            </div>
                        </div>
                    @endforeach
                @endforelse
            </div>
        </section>

        @if ($showFilteredArchive)
            <section id="insight-archive" class="mt-12 border-t border-slate-200 pt-10">
                <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                            Arsip Editorial
                        </p>
                        <h2 class="mt-2 text-3xl font-black leading-tight text-brand-ink">
                            {{ $featuredOnly ? 'Semua Editorial Pick' : 'Hasil Jelajah Editorial' }}
                        </h2>
                    </div>

                    <a href="{{ route('insights.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-full border border-slate-200 bg-white px-4 text-sm font-bold text-brand-navy transition hover:border-brand-navy">
                        Reset
                    </a>
                </div>

                <div class="grid gap-x-8 border-t border-slate-200 md:grid-cols-2">
                    @forelse ($archiveItems as $archiveIndex => $insight)
                        <article class="group border-b border-slate-200 py-5">
                            <a href="{{ route('insights.show', $insight->slug) }}" class="grid gap-4 sm:grid-cols-[150px_minmax(0,1fr)]">
                                <div class="relative aspect-[4/3] overflow-hidden rounded-lg bg-slate-100">
                                    {!! $renderImage($insight, $archiveIndex + 100) !!}
                                </div>
                                <div>
                                    <h3 class="insight-clamp-2 text-xl font-black leading-snug text-brand-ink underline-offset-4 transition group-hover:text-brand-navy group-hover:underline">
                                        {{ $insight->title }}
                                    </h3>
                                    <p class="insight-clamp-2 mt-3 text-sm leading-6 text-slate-600">
                                        {{ $excerpt($insight) }}
                                    </p>
                                    <div class="mt-4 text-sm font-medium text-slate-500">
                                        {{ $publishedDate($insight) }} · {{ $readingTime($insight) }}
                                    </div>
                                </div>
                            </a>
                        </article>
                    @empty
                        <div class="md:col-span-2">
                            <div class="border-b border-slate-200 py-10 text-center">
                                <h3 class="text-2xl font-black text-brand-ink">
                                    Editorial belum ditemukan.
                                </h3>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if ($insights instanceof \Illuminate\Pagination\AbstractPaginator && $insights->hasPages())
                    <div class="mt-10 flex flex-col gap-4 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm font-semibold text-slate-500">
                            Halaman <span class="font-black text-brand-ink">{{ $insights->currentPage() }}</span>
                            dari <span class="font-black text-brand-ink">{{ $insights->lastPage() }}</span>
                        </p>

                        <div class="flex items-center gap-3">
                            @if (! $insights->onFirstPage())
                                <a href="{{ $insights->previousPageUrl() }}#insight-archive" class="inline-flex min-h-10 items-center rounded-full border border-slate-200 bg-white px-4 text-sm font-black text-brand-navy transition hover:border-brand-navy">
                                    Sebelumnya
                                </a>
                            @endif

                            @if ($insights->hasMorePages())
                                <a href="{{ $insights->nextPageUrl() }}#insight-archive" class="inline-flex min-h-10 items-center rounded-full border border-brand-navy bg-brand-navy px-4 text-sm font-black text-white transition hover:bg-brand-ink">
                                    Berikutnya
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </section>
        @endif
    </div>

    <x-shared.cta-section
        class="mt-6"
        eyebrow="Kolaborasi Editorial"
        title="Punya isu hukum yang perlu dijelaskan ke publik?"
        body="Kembangkan isu hukum menjadi artikel, serial edukasi, diskusi, atau materi literasi publik yang mudah dipahami bersama Edulaw Project."
        background-image="https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=1600&q=85"
        background-alt="Ruang diskusi kolaborasi hukum"
        title-class="lg:text-[2.1rem]"
        :primary-url="route('collaboration.index')"
        primary-label="Ajukan Kolaborasi"
        :secondary-url="route('contact.index')"
        secondary-label="Hubungi Kami"
    />
    </div>
</main>
@endsection

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-editorial-pick-slider]').forEach((slider) => {
                    const section = slider.closest('section');
                    const previous = section?.querySelector('[data-editorial-pick-prev]');
                    const next = section?.querySelector('[data-editorial-pick-next]');
                    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    const scrollAmount = () => {
                        const firstCard = slider.querySelector('article');

                        return firstCard ? firstCard.getBoundingClientRect().width + 16 : Math.max(220, slider.clientWidth * 0.45);
                    };
                    const maxScroll = () => slider.scrollWidth - slider.clientWidth - 4;
                    const advance = () => {
                        if (maxScroll() <= 0) {
                            return;
                        }

                        if (slider.scrollLeft >= maxScroll()) {
                            slider.scrollTo({ left: 0, behavior: 'smooth' });
                            return;
                        }

                        slider.scrollBy({ left: scrollAmount(), behavior: 'smooth' });
                    };

                    let autoplay = null;
                    const stopAutoplay = () => {
                        if (autoplay) {
                            window.clearInterval(autoplay);
                            autoplay = null;
                        }
                    };
                    const startAutoplay = () => {
                        stopAutoplay();

                        if (! prefersReducedMotion && maxScroll() > 0) {
                            autoplay = window.setInterval(advance, 12000);
                        }
                    };
                    const restartAutoplay = () => {
                        startAutoplay();
                    };

                    previous?.addEventListener('click', () => {
                        slider.scrollBy({ left: -scrollAmount(), behavior: 'smooth' });
                        restartAutoplay();
                    });

                    next?.addEventListener('click', () => {
                        slider.scrollBy({ left: scrollAmount(), behavior: 'smooth' });
                        restartAutoplay();
                    });

                    let isDragging = false;
                    let startX = 0;
                    let scrollLeft = 0;
                    let hasMoved = false;

                    slider.addEventListener('pointerdown', (event) => {
                        if (event.pointerType === 'touch' || event.target.closest('a, button')) {
                            return;
                        }

                        isDragging = true;
                        hasMoved = false;
                        startX = event.pageX;
                        scrollLeft = slider.scrollLeft;
                        stopAutoplay();
                        slider.setPointerCapture(event.pointerId);
                    });

                    slider.addEventListener('pointermove', (event) => {
                        if (! isDragging) {
                            return;
                        }

                        const distance = event.pageX - startX;

                        if (Math.abs(distance) > 6) {
                            hasMoved = true;
                            event.preventDefault();
                            slider.scrollLeft = scrollLeft - distance;
                        }
                    });

                    const stopDragging = () => {
                        if (isDragging && hasMoved) {
                            restartAutoplay();
                        }

                        isDragging = false;
                        hasMoved = false;
                    };

                    slider.addEventListener('pointerup', stopDragging);
                    slider.addEventListener('pointercancel', stopDragging);
                    slider.addEventListener('mouseleave', stopDragging);
                    slider.addEventListener('mouseenter', stopAutoplay);
                    slider.addEventListener('mouseleave', startAutoplay);
                    slider.addEventListener('focusin', stopAutoplay);
                    slider.addEventListener('focusout', startAutoplay);
                    window.addEventListener('resize', restartAutoplay);

                    startAutoplay();
                });
            });
        </script>
    @endpush
@endonce
