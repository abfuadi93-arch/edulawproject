@extends('layouts.app')

@section('title', 'Insight dan Analisis Hukum | Edulaw Project')
@section('meta_description', 'Baca insight dan analisis hukum mengenai regulasi, kebijakan publik, tata kelola, teknologi hukum, serta isu aktual yang relevan bagi masyarakat.')

@push('head')
    @php
        $insightListSchemaItems = collect($insights->items())
            ->map(fn ($item): array => [
                'name' => $item->title,
                'url' => route('insights.show', $item->slug),
                'image' => filled($item->cover_image) ? $item->cover_image_url : null,
            ])
            ->all();
    @endphp
    @if ($insightListSchemaItems !== [])
        <x-structured-data :data="\App\Support\StructuredData::itemList($insightListSchemaItems, 'Insight dan Analisis Hukum')" />
    @endif
@endpush

@section('content')
@php
    use Illuminate\Pagination\AbstractPaginator;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $featuredEditorials = collect($featuredEditorials ?? []);
    $categorySections = collect($categorySections ?? []);
    $latestEditorials = collect($latestEditorials ?? []);
    $popularEditorials = collect($popularEditorials ?? []);
    $contributors = collect($editorialContributors ?? []);
    $orderedChannels = collect($insightChannels ?? [])->values();
    $archiveItems = $insights instanceof AbstractPaginator ? $insights->getCollection() : collect($insights ?? []);
    $selectedCategory = $selectedCategory ?? request('category');
    $selectedTag = $selectedTag ?? request('tag');
    $search = $search ?? request('q', '');
    $featuredOnly = (bool) ($featuredOnly ?? request()->boolean('featured'));
    $selectedSort = $selectedSort ?? request('sort', 'latest');
    $selectedView = request('view') === 'list' ? 'list' : 'grid';
    $showFilteredArchive = true;
    $hasEditorialFilters = filled($selectedCategory) || filled($selectedTag) || filled($search) || $featuredOnly || request()->filled('author') || $selectedSort !== 'latest';

    $categoryName = fn ($article): string => $article?->display_category
        ?? $article?->categoryRelation?->name
        ?? 'Editorial';

    $publishedDate = function ($article): string {
        if (blank($article?->published_at)) {
            return '';
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

        return 'Edulaw Project';
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

    $latestArchiveUrl = route('insights.index', ['archive' => 'latest']).'#insight-archive';
@endphp

<div class="overflow-x-clip bg-[#f7f8fa] text-brand-ink">
    <x-insight.editorial-hero
        :article-count="$publishedEditorialCount ?? 0"
        :category-count="$editorialCategoryCount ?? $orderedChannels->count()"
    />

    <x-insight.featured-editorial
        :articles="$featuredEditorials"
        :category-name="$categoryName"
        :published-date="$publishedDate"
        :reading-time="$readingTime"
        :author-name="$authorName"
        :excerpt="$excerpt"
    />

    <x-insight.editorial-picks
        :articles="$latestEditorials"
        :category-name="$categoryName"
        :published-date="$publishedDate"
        :reading-time="$readingTime"
        :archive-url="$latestArchiveUrl"
    />

    @if ($categorySections->isNotEmpty())
        <section class="bg-white py-9 sm:py-10 lg:py-11" aria-labelledby="editorial-categories-heading">
            <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-brand-navy">Kategori Editorial</p>
                        <h2 id="editorial-categories-heading" class="mt-1 text-balance font-display text-2xl font-black text-brand-navy sm:text-3xl">Jelajahi Berdasarkan Tema</h2>
                        <p class="mt-1.5 max-w-2xl text-base leading-7 text-slate-600">Empat kanal editorial untuk membantu pembaca menemukan jenis analisis yang paling relevan.</p>
                    </div>
                    <a href="{{ $latestArchiveUrl }}" class="text-sm font-extrabold text-brand-navy underline decoration-brand-amber decoration-2 underline-offset-4">Semua Kategori <span aria-hidden="true">→</span></a>
                </div>

                <div class="mt-7 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($categorySections as $block)
                        <x-insight.editorial-category-block
                            :block="$block"
                        />
                    @endforeach
                </div>
            </div>
        </section>
    @else
        <x-insight.category-section :channels="$orderedChannels" />
    @endif

    <x-insight.editorial-latest-list
        :popular-articles="$popularEditorials"
        :popular-has-views="$popularHasViews ?? false"
        :contributors="$contributors"
        :category-name="$categoryName"
        :published-date="$publishedDate"
        :archive-url="$latestArchiveUrl"
    />

    @if ($showFilteredArchive)
        <section id="insight-archive" class="bg-[#f7f8fa] py-9 sm:py-10 lg:py-11">
            <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-brand-navy">Arsip Editorial</p>
                        <h2 class="mt-1 font-display text-2xl font-black text-brand-navy sm:text-3xl">{{ $featuredOnly ? 'Semua Pilihan Editor' : ($hasEditorialFilters ? 'Hasil Jelajah Editorial' : 'Semua Editorial') }}</h2>
                        <p class="mt-1.5 text-base leading-7 text-slate-600">Cari dan telusuri artikel berdasarkan kategori, kata kunci, dan urutan publikasi.</p>
                        @if (filled($selectedTag))
                            <p class="mt-2 text-sm font-bold text-brand-teal">Topik: {{ $selectedTagName ?? $selectedTag }}</p>
                        @endif
                    </div>
                    @if ($hasEditorialFilters)
                        <a href="{{ route('insights.index', ['archive' => 'latest']) }}#insight-archive" class="text-sm font-extrabold text-brand-navy underline decoration-brand-amber decoration-2 underline-offset-4">Atur ulang</a>
                    @endif
                </div>

                <x-insight.editorial-toolbar
                    :channels="$orderedChannels"
                    :selected-category="$selectedCategory"
                    :selected-tag="$selectedTag"
                    :search="$search"
                    :featured-only="$featuredOnly"
                    :selected-sort="$selectedSort"
                    :selected-view="$selectedView"
                />

                <div class="mt-6 {{ $selectedView === 'grid' ? 'grid gap-x-6 gap-y-8 sm:grid-cols-2 lg:grid-cols-3' : 'divide-y divide-slate-200 border-y border-slate-200' }}">
                    @forelse ($archiveItems as $article)
                        @if ($selectedView === 'grid')
                            <article class="group min-w-0">
                                <a href="{{ route('insights.show', $article->slug) }}" class="block focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-brand-amber">
                                    <div class="relative aspect-[16/10] overflow-hidden rounded-[13px] bg-brand-navy">
                                        @if (filled($article->cover_image) && edulaw_file_exists($article->cover_image))
                                            <img src="{{ $article->cover_image_url }}" alt="{{ $article->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.025] motion-reduce:transition-none">
                                        @else
                                            <div class="absolute inset-0 bg-linear-to-br from-brand-navy via-[#244972] to-[#0f766e]"></div>
                                        @endif
                                    </div>
                                    <div class="pt-4">
                                        <p class="text-[11px] font-extrabold uppercase tracking-[0.1em] text-brand-coral">{{ $categoryName($article) }}</p>
                                        <h3 class="mt-1.5 line-clamp-3 text-base font-black leading-snug text-brand-ink transition group-hover:text-brand-navy">{{ $article->title }}</h3>
                                        <p class="mt-2.5 text-xs font-semibold text-slate-500">{{ $publishedDate($article) }} · {{ $readingTime($article) }}</p>
                                    </div>
                                </a>
                            </article>
                        @else
                            <article class="group py-4">
                                <a href="{{ route('insights.show', $article->slug) }}" class="grid gap-4 focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-brand-amber sm:grid-cols-[190px_minmax(0,1fr)]">
                                    <div class="relative aspect-[16/10] overflow-hidden rounded-xl bg-brand-navy">
                                        @if (filled($article->cover_image) && edulaw_file_exists($article->cover_image))
                                            <img src="{{ $article->cover_image_url }}" alt="{{ $article->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.025] motion-reduce:transition-none">
                                        @else
                                            <div class="absolute inset-0 bg-linear-to-br from-brand-navy via-[#244972] to-[#0f766e]"></div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 self-center">
                                        <p class="text-[11px] font-extrabold uppercase tracking-[0.1em] text-brand-coral">{{ $categoryName($article) }}</p>
                                        <h3 class="mt-1.5 line-clamp-2 text-lg font-black leading-snug text-brand-ink group-hover:text-brand-navy">{{ $article->title }}</h3>
                                        @if ($excerpt($article, 150) !== '')
                                            <p class="mt-2 line-clamp-2 text-base leading-7 text-slate-600">{{ $excerpt($article, 150) }}</p>
                                        @endif
                                        <p class="mt-2 text-xs font-semibold text-slate-500">{{ $publishedDate($article) }} · {{ $readingTime($article) }}</p>
                                    </div>
                                </a>
                            </article>
                        @endif
                    @empty
                        <div class="col-span-full rounded-[13px] border border-dashed border-slate-300 bg-white py-10 text-center text-sm font-semibold text-slate-500">Editorial belum ditemukan.</div>
                    @endforelse
                </div>

                @if ($insights instanceof AbstractPaginator && $insights->hasPages())
                    <x-shared.pagination :paginator="$insights" fragment="insight-archive" />
                @endif
            </div>
        </section>
    @endif

    <x-shared.cta-section
        heading-id="editorial-contribution-heading"
        eyebrow="Kontribusi Editorial"
        title="Punya gagasan hukum yang penting untuk ruang publik?"
        body="Edulaw membuka ruang bagi penulis dan mitra untuk mengembangkan artikel, analisis, serta kolaborasi pengetahuan hukum yang relevan dan dapat dipertanggungjawabkan."
        :primary-url="route('collaboration.index')"
        primary-label="Ajukan Kolaborasi"
        :secondary-url="route('about')"
        secondary-label="Tentang Editorial"
    />
</div>
@endsection
