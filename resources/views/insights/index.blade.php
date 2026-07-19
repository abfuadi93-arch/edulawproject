@extends('layouts.app')

@section('title', 'Editorial - Edulaw Project')

@section('content')
@php
    use Illuminate\Pagination\AbstractPaginator;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $latestInsights = collect($latestInsights ?? []);
    $editorialPicks = collect($editorialPicks ?? []);
    $popularInsights = collect($popularInsights ?? []);
    $insightChannels = collect($insightChannels ?? []);
    $contributors = collect($editorialContributors ?? []);
    $archiveItems = $insights instanceof AbstractPaginator ? $insights->getCollection() : collect($insights ?? []);
    $selectedCategory = $selectedCategory ?? request('category');
    $search = $search ?? request('q', '');
    $featuredOnly = (bool) ($featuredOnly ?? request()->boolean('featured'));
    $showFilteredArchive = (bool) ($showFilteredArchive ?? false);

    $categoryName = fn ($article): string => $article?->display_category
        ?? $article?->categoryRelation?->name
        ?? 'Editorial';

    $publishedDate = function ($article): string {
        if (blank($article?->published_at)) {
            return 'Belum dijadwalkan';
        }

        try {
            return Carbon::parse($article->published_at)->translatedFormat('d M Y');
        } catch (Throwable) {
            return (string) $article->published_at;
        }
    };

    $readingTime = function ($article): string {
        if (filled($article?->reading_time)) {
            return $article->reading_time.' menit baca';
        }

        $words = str_word_count(strip_tags((string) ($article?->content ?? '')));

        return max(1, (int) ceil($words / 200)).' menit baca';
    };

    $authorName = function ($article): string {
        if ($article && $article->relationLoaded('authors') && $article->authors->isNotEmpty()) {
            return $article->authors
                ->sortBy(fn ($author) => $author->pivot?->author_order ?? 999)
                ->pluck('name')
                ->filter()
                ->join(', ');
        }

        return $article?->creator?->name ?: 'Edulaw Project';
    };

    $excerpt = function ($article, int $limit = 200): string {
        if (! $article) {
            return '';
        }

        $text = Str::squish(strip_tags((string) ($article->excerpt ?: $article->content)));
        $title = Str::squish(strip_tags((string) $article->title));

        if ($title !== '' && Str::startsWith(Str::lower($text), Str::lower($title))) {
            $text = ltrim(Str::substr($text, Str::length($title)), " \t\n\r\0\x0B:-–—|.");
        }

        return Str::limit($text, $limit);
    };

    $featuredArticle = $latestInsights->first();
    $usedInsightIds = collect([$featuredArticle?->id])
        ->filter()
        ->unique();

    $latestArticles = $latestInsights
        ->whereNotIn('id', $usedInsightIds->all())
        ->take(5)
        ->values();
    $usedInsightIds = $usedInsightIds
        ->merge($latestArticles->pluck('id'))
        ->filter()
        ->unique();

    $editorialPicksDisplay = $editorialPicks
        ->filter()
        ->whereNotIn('id', $usedInsightIds->all())
        ->unique('id')
        ->take(4)
        ->values();
    $usedInsightIds = $usedInsightIds
        ->merge($editorialPicksDisplay->pluck('id'))
        ->filter()
        ->unique();

    $primaryChannelOrder = collect(['Regulatory Update', 'Edulaw Insight', 'Legal 101', 'Law & Governance']);
    $orderedChannels = $primaryChannelOrder->map(fn (string $label) => $insightChannels->firstWhere('label', $label))
        ->filter()
        ->values();

    $popularInsightsDisplay = $popularInsights
        ->whereNotIn('id', $usedInsightIds->all())
        ->unique('id')
        ->take(5)
        ->values();

    if ($popularInsightsDisplay->isEmpty()) {
        $popularInsightsDisplay = $latestInsights
            ->whereNotIn('id', $usedInsightIds->all())
            ->unique('id')
            ->take(5)
            ->values();
    }

    $usedInsightIds = $usedInsightIds
        ->merge($popularInsightsDisplay->pluck('id'))
        ->filter()
        ->unique();

    $categoryChannels = $insightChannels
        ->filter(fn (array $channel): bool => collect($channel['articles'] ?? [])->isNotEmpty())
        ->sortBy(fn (array $channel): array => [
            (int) (($channel['category']?->sort_order ?? 999)),
            -1 * (int) ($channel['article_count'] ?? 0),
            $primaryChannelOrder->search($channel['label']) === false ? 99 : $primaryChannelOrder->search($channel['label']),
            $channel['label'],
        ])
        ->take(4)
        ->values();

    $categoryBlocks = $categoryChannels->map(function (array $channel) use (&$usedInsightIds): array {
        $category = $channel['category'] ?? null;
        $channelArticles = collect($channel['articles'] ?? [])
            ->unique('id')
            ->values();

        $items = $channelArticles
            ->whereNotIn('id', $usedInsightIds->all())
            ->take(3)
            ->values();

        $usedInsightIds = $usedInsightIds
            ->merge($items->pluck('id'))
            ->filter()
            ->unique();

        return [
            'title' => $category?->name ?: $channel['label'],
            'description' => $category?->description ?: ($channel['description'] ?? null),
            'items' => $items,
            'url' => ($channel['url'] ?? route('insights.index')).'#insight-archive',
        ];
    })
        ->filter(fn (array $block): bool => collect($block['items'] ?? [])->isNotEmpty())
        ->values();

    $latestArchiveUrl = route('insights.index', ['archive' => 'latest']).'#insight-archive';
    $editorialArchiveUrl = route('insights.index', ['featured' => 1]).'#insight-archive';
    $allCategoriesUrl = route('insights.index', ['archive' => 'latest']).'#insight-archive';
@endphp

<div class="overflow-x-clip bg-white text-brand-ink">
    <x-insight.editorial-hero :archive-url="$latestArchiveUrl" />

    <x-insight.featured-editorial
        :article="$featuredArticle"
        :excerpt="$excerpt"
        :category-name="$categoryName"
        :published-date="$publishedDate"
        :reading-time="$readingTime"
        :author-name="$authorName"
    />

    <x-insight.editorial-toolbar
        :channels="$orderedChannels"
        :selected-category="$selectedCategory"
        :search="$search"
        :featured-only="$featuredOnly"
    />

    <x-insight.editorial-latest-list
        :articles="$latestArticles"
        :excerpt="$excerpt"
        :category-name="$categoryName"
        :published-date="$publishedDate"
        :reading-time="$readingTime"
        :author-name="$authorName"
        :archive-url="$latestArchiveUrl"
    />

    <x-insight.editorial-picks
        :articles="$editorialPicksDisplay"
        :excerpt="$excerpt"
        :category-name="$categoryName"
        :published-date="$publishedDate"
        :author-name="$authorName"
        :archive-url="$editorialArchiveUrl"
    />

    @if ($categoryBlocks->isNotEmpty())
        <section class="bg-[#fbfaf7] py-12 sm:py-14 lg:py-16">
            <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-3xl">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-brand-coral">Kanal pengetahuan</p>
                        <h2 class="mt-2 text-balance font-display text-3xl font-bold text-brand-ink sm:text-4xl">Jelajahi Berdasarkan Kategori</h2>
                    </div>
                    <a href="{{ $allCategoriesUrl }}" class="inline-flex min-h-10 w-fit items-center justify-center rounded-full border border-brand-amber/50 bg-white px-4 text-sm font-bold text-brand-navy shadow-sm transition hover:border-brand-amber hover:bg-brand-amber-soft focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">Lihat Semua Kategori</a>
                </div>
                <div class="mt-8 grid gap-5 md:grid-cols-2 lg:gap-6">
                    @foreach ($categoryBlocks as $blockIndex => $block)
                        <x-insight.editorial-category-block
                            :block="$block"
                            :index="$blockIndex"
                            :category-name="$categoryName"
                            :published-date="$publishedDate"
                        />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-insight.editorial-most-read
        :articles="$popularInsightsDisplay"
        :category-name="$categoryName"
        :reading-time="$readingTime"
    />

    <x-insight.editorial-contributors :contributors="$contributors" />

    @if ($showFilteredArchive)
        <section id="insight-archive" class="border-t border-slate-200 bg-white pt-12 pb-12 sm:pt-14 sm:pb-14 lg:pt-12 lg:pb-12">
            <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-brand-coral">Arsip Editorial</p>
                        <h2 class="mt-2 font-display text-3xl font-bold text-brand-ink sm:text-4xl">{{ $featuredOnly ? 'Semua Pilihan Editor' : 'Hasil Jelajah Editorial' }}</h2>
                    </div>
                    <a href="{{ route('insights.index') }}" class="inline-flex min-h-10 w-fit items-center rounded-md border border-slate-200 px-4 text-sm font-semibold text-brand-navy hover:border-brand-navy focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">Atur ulang</a>
                </div>

                <div class="mt-8 divide-y divide-slate-200 border-y border-slate-200">
                    @forelse ($archiveItems as $article)
                        <article class="group py-6">
                            <a href="{{ route('insights.show', $article->slug) }}" class="grid gap-5 rounded-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber sm:grid-cols-[170px_minmax(0,1fr)]">
                                <div class="aspect-[16/10] overflow-hidden rounded-xl bg-slate-100">
                                    <img src="{{ $article->cover_image_url }}" alt="{{ $article->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.025] motion-reduce:transition-none">
                                </div>
                                <div class="min-w-0 self-center">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-brand-coral">{{ $categoryName($article) }}</p>
                                    <h3 class="mt-2 line-clamp-2 text-xl font-bold leading-snug text-brand-ink transition group-hover:text-brand-navy">{{ $article->title }}</h3>
                                    @if ($excerpt($article, 150) !== '')
                                        <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">{{ $excerpt($article, 150) }}</p>
                                    @endif
                                    <p class="mt-3 text-xs font-semibold text-slate-500">{{ $publishedDate($article) }} · {{ $readingTime($article) }}</p>
                                </div>
                            </a>
                        </article>
                    @empty
                        <div class="py-12 text-center text-sm font-semibold text-slate-500">Editorial belum ditemukan.</div>
                    @endforelse
                </div>

                @if ($insights instanceof AbstractPaginator && $insights->hasPages())
                    <nav aria-label="Navigasi halaman arsip" class="mt-8 flex items-center justify-between gap-4">
                        <p class="text-sm font-medium text-slate-500">Halaman {{ $insights->currentPage() }} dari {{ $insights->lastPage() }}</p>
                        <div class="flex gap-2">
                            @if (! $insights->onFirstPage())
                                <a href="{{ $insights->previousPageUrl() }}#insight-archive" class="inline-flex min-h-10 items-center rounded-md border border-slate-200 px-4 text-sm font-semibold text-brand-navy hover:border-brand-navy">Sebelumnya</a>
                            @endif
                            @if ($insights->hasMorePages())
                                <a href="{{ $insights->nextPageUrl() }}#insight-archive" class="inline-flex min-h-10 items-center rounded-md bg-brand-navy px-4 text-sm font-semibold text-white hover:bg-brand-ink">Berikutnya</a>
                            @endif
                        </div>
                    </nav>
                @endif
            </div>
        </section>
    @endif

    <x-shared.cta-section
        class="mt-0"
        eyebrow="Kolaborasi Editorial"
        title="Punya isu hukum yang perlu dijelaskan kepada publik?"
        body="Kembangkan isu hukum menjadi artikel, serial edukasi, diskusi, atau materi literasi publik bersama Edulaw Project."
        background-image="https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=1600&q=85"
        background-alt="Diskusi kolaborasi editorial"
        title-class="font-bold"
        :primary-url="route('collaboration.index')"
        primary-label="Ajukan Kolaborasi"
        :secondary-url="route('contact.index')"
        secondary-label="Hubungi Kami"
    />
</div>
@endsection
