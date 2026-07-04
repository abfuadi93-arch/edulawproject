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
    $editorPickLead = $editorialPicks->first() ?: $allInsights->skip(2)->first() ?: $lead;
    $editorPickGrid = $allInsights->skip(2)->take(4)->values();

    $channelByLabel = fn (string $label) => $insightChannels->firstWhere('label', $label) ?? [];
    $lawItems = collect($channelByLabel('Law & Governance')['articles'] ?? [])->take(2);
    $policyItems = collect($channelByLabel('Policy & Society')['articles'] ?? [])->take(2);
    $legalItems = collect($channelByLabel('Legal 101')['articles'] ?? [])->take(2);
    $regulatoryItems = collect($channelByLabel('Regulatory Update')['articles'] ?? [])->take(2);

    if ($lawItems->isEmpty()) {
        $lawItems = $allInsights->take(2);
    }

    if ($policyItems->isEmpty()) {
        $policyItems = $allInsights->skip(2)->take(2);
    }

    if ($legalItems->isEmpty()) {
        $legalItems = $allInsights->skip(4)->take(2);
    }

    if ($regulatoryItems->isEmpty()) {
        $regulatoryItems = $allInsights->skip(6)->take(2);
    }

    $toolbarChannelLabels = collect([
        'Edulaw Editorial',
        'Legal 101',
        'Law & Governance',
        'Regulatory Update',
    ]);

    $toolbarChannels = $insightChannels
        ->filter(fn (array $channel): bool => $toolbarChannelLabels->contains($channel['label'] ?? ''))
        ->sortBy(function (array $channel) use ($toolbarChannelLabels): int {
            $position = $toolbarChannelLabels->search($channel['label'] ?? '');

            return $position === false ? 99 : $position;
        })
        ->values();

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
                    <span>{{ $lead ? $categoryName($lead) : 'Edulaw Editorial' }}</span>
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

        <section class="sticky top-20 z-30 mt-12 border-y border-slate-200 bg-[#E7E7E7]/95 py-3 backdrop-blur">
            <div class="grid gap-3 lg:grid-cols-[minmax(260px,360px)_minmax(0,1fr)] lg:items-center">
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

        <section class="mt-12">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-2xl font-black text-brand-ink">Editor's Pick</h2>
                <a href="{{ $editorialArchiveUrl }}" class="group inline-flex items-center gap-2 text-sm font-black text-brand-coral">
                    See all
                    <span class="transition group-hover:translate-x-1">→</span>
                </a>
            </div>

            @if ($editorPickLead)
                <article class="group">
                    <a href="{{ route('insights.show', $editorPickLead->slug) }}" class="relative flex min-h-[360px] items-end overflow-hidden rounded-lg bg-slate-100 p-6 text-white">
                        {!! $renderImage($editorPickLead, 60) !!}
                        <div class="absolute inset-0 bg-linear-to-t from-[#06132a]/90 via-[#06132a]/30 to-transparent"></div>
                        <div class="relative z-10 max-w-3xl">
                            <p class="text-xs font-black uppercase tracking-[0.14em] text-white/72">
                                {{ $categoryName($editorPickLead) }} • {{ $publishedDate($editorPickLead) }}
                            </p>
                            <h3 class="insight-clamp-2 mt-3 text-3xl font-black leading-tight text-white underline-offset-4 group-hover:underline">
                                {{ $editorPickLead->title }}
                            </h3>
                            <p class="insight-clamp-2 mt-3 text-sm leading-6 text-white/78">
                                {{ $excerpt($editorPickLead, 190) }}
                            </p>
                        </div>
                    </a>
                </article>
            @endif

            <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($editorPickGrid as $index => $item)
                    <article class="group">
                        <a href="{{ route('insights.show', $item->slug) }}" class="relative block aspect-[4/3] overflow-hidden rounded-lg bg-slate-100">
                            {!! $renderImage($item, $index + 61) !!}
                        </a>
                        <div class="mt-3 flex items-center gap-2 text-[11px] font-bold text-slate-500">
                            <span class="h-2 w-2 rounded-full bg-brand-coral"></span>
                            <span>{{ $categoryName($item) }}</span>
                        </div>
                        <h3 class="insight-clamp-3 mt-2 text-base font-black leading-tight text-brand-ink underline-offset-4 group-hover:text-brand-navy group-hover:underline">
                            <a href="{{ route('insights.show', $item->slug) }}">{{ $item->title }}</a>
                        </h3>
                        <p class="mt-2 text-xs font-bold text-brand-coral">{{ $readingTime($item) }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="mt-12 grid gap-8 lg:grid-cols-2">
            @foreach ([
                ['title' => 'Law & Governance', 'items' => $lawItems],
                ['title' => 'Policy & Society', 'items' => $policyItems],
                ['title' => 'Legal 101', 'items' => $legalItems],
                ['title' => 'Regulatory Update', 'items' => $regulatoryItems],
            ] as $block)
                <div>
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-2xl font-black text-brand-ink">{{ $block['title'] }}</h2>
                        <a href="{{ route('insights.index', ['q' => $block['title'], 'archive' => 'latest']) }}#insight-archive" class="text-sm font-black text-brand-coral">→</a>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        @foreach (collect($block['items'])->take(2) as $index => $item)
                            <article class="group">
                                <a href="{{ route('insights.show', $item->slug) }}" class="relative block aspect-[4/3] overflow-hidden rounded-lg bg-slate-100">
                                    {!! $renderImage($item, $index + 80) !!}
                                </a>
                                <div class="mt-3 flex items-center gap-2 text-[11px] font-bold text-slate-500">
                                    <span class="h-2 w-2 rounded-full bg-brand-teal"></span>
                                    <span>{{ $categoryName($item) }}</span>
                                </div>
                                <h3 class="insight-clamp-3 mt-2 text-base font-black leading-tight text-brand-ink underline-offset-4 group-hover:text-brand-navy group-hover:underline">
                                    <a href="{{ route('insights.show', $item->slug) }}">{{ $item->title }}</a>
                                </h3>
                                <p class="mt-2 text-xs font-bold text-brand-coral">{{ $readingTime($item) }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endforeach
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
