@extends('layouts.app')

@section('title', 'Insight - Edulaw Project')

@section('content')
@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Carbon;

    $insights = $insights ?? collect();
    $insightCategories = $insightCategories ?? collect();
    $selectedCategory = $selectedCategory ?? request('category');
    $search = $search ?? request('q', '');

    $insightItems = $insights instanceof \Illuminate\Pagination\AbstractPaginator
        ? $insights->getCollection()
        : collect($insights);

    $totalInsight = $insights instanceof \Illuminate\Pagination\AbstractPaginator
        ? $insights->total()
        : $insightItems->count();

    $currentCount = $insightItems->count();

    $featuredInsight = $featuredInsight
        ?? $insightItems->firstWhere('featured', true)
        ?? $insightItems->firstWhere('is_featured', true)
        ?? $insightItems->first();

    $insightImage = function ($insight) {
        return $insight?->cover_image_url;
    };

    $categoryName = function ($insight) {
        return $insight?->display_category
            ?? $insight?->category?->name
            ?? $insight?->insightCategory?->name
            ?? 'Legal Insight';
    };

    $authorName = function ($insight) {
        if (isset($insight->authors) && $insight->authors->count()) {
            return $insight->authors->pluck('name')->filter()->join(', ');
        }

        return $insight?->author_name
            ?? $insight?->author?->name
            ?? 'Edulaw Project';
    };

    $readingTime = function ($insight) {
        if (! empty($insight?->reading_time)) {
            return $insight->reading_time . ' menit baca';
        }

        $text = trim(strip_tags(($insight?->content ?? '') . ' ' . ($insight?->excerpt ?? '')));
        $words = str_word_count($text);
        $minutes = max(1, (int) ceil($words / 200));

        return $minutes . ' menit baca';
    };

    $wordCount = function ($insight) {
        $text = trim(strip_tags(($insight?->content ?? '') . ' ' . ($insight?->excerpt ?? '')));

        return max(0, str_word_count($text));
    };

    $publishedDate = function ($insight) {
        if (empty($insight?->published_at)) {
            return 'Belum dijadwalkan';
        }

        try {
            return Carbon::parse($insight->published_at)->translatedFormat('d F Y');
        } catch (\Throwable $e) {
            return $insight->published_at;
        }
    };

    $excerpt = function ($insight, int $limit = 150) {
        return Str::limit(
            $insight?->excerpt ?: strip_tags($insight?->content ?? 'Insight Edulaw Project tentang isu hukum dan kebijakan publik.'),
            $limit
        );
    };

    $fallbackPalettes = [
        ['from' => '#061A3D', 'via' => '#0F2868', 'to' => '#2DD4BF', 'accent' => '#F4B942'],
        ['from' => '#102A43', 'via' => '#1E5F74', 'to' => '#F4B942', 'accent' => '#2DD4BF'],
        ['from' => '#240046', 'via' => '#5A189A', 'to' => '#F4B942', 'accent' => '#C77DFF'],
        ['from' => '#001219', 'via' => '#005F73', 'to' => '#94D2BD', 'accent' => '#F4B942'],
        ['from' => '#3D0C11', 'via' => '#8D0801', 'to' => '#F4B942', 'accent' => '#2DD4BF'],
        ['from' => '#22223B', 'via' => '#4A4E69', 'to' => '#C9ADA7', 'accent' => '#F4B942'],
    ];

    $fallbackPalette = function ($insight, int $index = 0) use ($fallbackPalettes) {
        $seed = abs(crc32(($insight->slug ?? '') . '|' . ($insight->title ?? '') . '|' . ($insight->id ?? $index)));

        return $fallbackPalettes[$seed % count($fallbackPalettes)];
    };

    $featuredImage = $featuredInsight ? $insightImage($featuredInsight) : null;
    $featuredPalette = $featuredInsight ? $fallbackPalette($featuredInsight, 0) : $fallbackPalettes[0];

    $popularInsights = $popularInsights ?? $insightItems->take(3);
@endphp

<style>
    .insight-control-scroll {
        scrollbar-width: thin;
        scrollbar-color: rgba(15, 40, 104, .35) transparent;
    }

    .insight-control-scroll::-webkit-scrollbar {
        height: 7px;
    }

    .insight-control-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .insight-control-scroll::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: rgba(15, 40, 104, .22);
    }

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
            radial-gradient(circle at 85% 18%, rgba(255,255,255,.20), transparent 28%),
            radial-gradient(circle at 15% 85%, rgba(255,255,255,.10), transparent 32%),
            linear-gradient(120deg, rgba(255,255,255,.12), transparent 38%, rgba(0,0,0,.28));
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

    .insight-card-title {
        display: -webkit-box;
        min-height: 3.05rem;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .insight-card-excerpt {
        display: -webkit-box;
        min-height: 4.5rem;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
    }
</style>

<main class="bg-slate-50">
    <x-shared.page-header
        title="Insight"
        :compact="true"
        eyebrow="Kanal Insight"
        description="Analisis hukum, pembaruan regulasi, dan isu kebijakan publik yang disajikan secara ringkas, relevan, dan mudah dipahami."
        background-image="https://images.unsplash.com/photo-1589578527966-fdac0f44566c?auto=format&fit=crop&w=1800&q=85"
        background-alt="Analisis hukum dan insight Edulaw Project"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Insight'],
        ]"
    />

    @if ($featuredInsight)
        <section class="border-b border-slate-200 bg-white py-10 lg:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <article class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-900/5 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10">
                    <a href="{{ route('insights.show', $featuredInsight->slug) }}" class="grid gap-0 lg:grid-cols-[0.9fr_1.1fr]">
                        <div class="relative min-h-72 overflow-hidden bg-slate-100 sm:min-h-80 lg:min-h-90">
                            <div
                                class="insight-visual-fallback"
                                style="background: linear-gradient(135deg, {{ $featuredPalette['from'] }} 0%, {{ $featuredPalette['via'] }} 52%, {{ $featuredPalette['to'] }} 100%);"
                            >
                                <div>
                                    <span class="insight-fallback-mark" style="background: {{ $featuredPalette['accent'] }};"></span>

                                    <p class="mt-5 max-w-48 text-xs font-black uppercase tracking-[0.24em] text-white/72">
                                        Edulaw Insight
                                    </p>
                                </div>

                                <strong class="max-w-72 text-2xl font-black leading-tight tracking-tight text-white">
                                    {{ $categoryName($featuredInsight) }}
                                </strong>
                            </div>

                            @if ($featuredImage)
                                <img
                                    src="{{ $featuredImage }}"
                                    alt="{{ $featuredInsight->title }}"
                                    loading="lazy"
                                    class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                    onerror="this.remove()"
                                >
                            @endif

                            <div class="absolute inset-0 bg-linear-to-t from-brand-navy/45 via-transparent to-transparent"></div>

                            <span class="absolute right-5 top-5 rounded-full bg-white/90 px-4 py-2 text-[10px] font-black uppercase tracking-[0.16em] text-brand-navy shadow-sm">
                                Featured Insight
                            </span>
                        </div>

                        <div class="flex flex-col justify-center p-6 sm:p-8 lg:p-10 xl:p-12">
                            <div class="flex flex-wrap items-center gap-2 text-xs font-black uppercase tracking-[0.16em] text-brand-navy">
                                <span>{{ $categoryName($featuredInsight) }}</span>
                                <span class="text-slate-300">/</span>
                                <span>{{ $publishedDate($featuredInsight) }}</span>
                            </div>

                            <h2 class="mt-4 max-w-3xl text-3xl font-black leading-tight tracking-tight text-brand-ink sm:text-4xl lg:text-[2.75rem]">
                                {{ $featuredInsight->title }}
                            </h2>

                            <p class="mt-5 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                                {{ $excerpt($featuredInsight, 220) }}
                            </p>

                            <div class="mt-7 flex flex-wrap items-center justify-between gap-5">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-mist text-sm font-black text-brand-navy">
                                        {{ Str::upper(Str::substr($authorName($featuredInsight), 0, 1)) }}
                                    </div>

                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-black text-brand-ink">
                                            {{ $authorName($featuredInsight) }}
                                        </p>

                                        <p class="text-xs font-semibold text-slate-500">
                                            {{ $readingTime($featuredInsight) }}
                                        </p>
                                    </div>
                                </div>

                                <span class="inline-flex rounded-full bg-brand-navy px-5 py-3 text-sm font-black text-white transition group-hover:bg-brand-ink">
                                    Baca Analisis Lengkap →
                                </span>
                            </div>
                        </div>
                    </a>
                </article>
            </div>
        </section>
    @endif

    <section class="border-b border-slate-200 bg-white py-5">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('insights.index') }}" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_420px] lg:items-center">
                <div class="insight-control-scroll flex gap-2 overflow-x-auto pb-1">
                    <a
                        href="{{ route('insights.index', array_filter(['q' => $search ?: null])) }}"
                        class="shrink-0 rounded-full border px-5 py-2.5 text-sm font-bold shadow-sm transition
                            {{ blank($selectedCategory)
                                ? 'border-brand-navy bg-brand-navy text-white'
                                : 'border-slate-200 bg-white text-brand-ink hover:border-brand-silver hover:bg-brand-paper' }}"
                    >
                        Semua
                    </a>

                    @foreach ($insightCategories as $category)
                        <a
                            href="{{ route('insights.index', array_filter(['category' => $category->slug, 'q' => $search ?: null])) }}"
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
        </div>
    </section>

    <section class="bg-slate-50 py-10 lg:py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.20em] text-brand-navy">
                        Artikel Terbaru
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-brand-ink">
                        Baca Insight Terbaru
                    </h2>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                        {{ number_format($currentCount, 0, ',', '.') }} tulisan ditemukan
                    </span>

                    @if ($search || $selectedCategory)
                        <a
                            href="{{ route('insights.index') }}"
                            class="inline-flex rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-black uppercase tracking-[0.12em] text-brand-navy transition hover:bg-brand-paper"
                        >
                            Reset Filter
                        </a>
                    @endif
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($insightItems as $insightIndex => $insight)
                    @php
                        $currentImage = $insightImage($insight);
                        $palette = $fallbackPalette($insight, $insightIndex);
                    @endphp

                    <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-brand-silver hover:shadow-xl hover:shadow-slate-900/10">
                        <a href="{{ route('insights.show', $insight->slug) }}" class="flex h-full flex-col">
                            <div class="relative h-52 overflow-hidden bg-slate-100">
                                <div
                                    class="insight-visual-fallback"
                                    style="background: linear-gradient(135deg, {{ $palette['from'] }} 0%, {{ $palette['via'] }} 52%, {{ $palette['to'] }} 100%);"
                                >
                                    <div>
                                        <span class="insight-fallback-mark" style="background: {{ $palette['accent'] }};"></span>

                                        <p class="mt-5 max-w-48 text-xs font-black uppercase tracking-[0.24em] text-white/72">
                                            Edulaw Insight
                                        </p>
                                    </div>

                                    <strong class="max-w-60 text-lg font-black leading-tight tracking-tight text-white">
                                        {{ $categoryName($insight) }}
                                    </strong>
                                </div>

                                @if ($currentImage)
                                    <img
                                        src="{{ $currentImage }}"
                                        alt="{{ $insight->title }}"
                                        loading="lazy"
                                        class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                        onerror="this.remove()"
                                    >
                                @endif

                                <span class="absolute right-4 top-4 rounded-full bg-white/90 px-3 py-1 text-[10px] font-black uppercase tracking-[0.12em] text-brand-navy shadow-sm">
                                    {{ $publishedDate($insight) }}
                                </span>

                                @if ($insight->is_featured ?? $insight->featured ?? false)
                                    <span class="absolute left-4 top-4 rounded-full bg-brand-amber px-3 py-1 text-[10px] font-black uppercase tracking-[0.12em] text-brand-black shadow-sm">
                                        Unggulan
                                    </span>
                                @endif
                            </div>

                            <div class="flex flex-1 flex-col p-5">
                                <div class="text-[11px] font-black uppercase tracking-[0.16em] text-brand-navy">
                                    {{ $categoryName($insight) }}
                                </div>

                                <h3 class="insight-card-title mt-3 text-xl font-extrabold leading-tight tracking-tight text-brand-ink transition group-hover:text-brand-navy">
                                    {{ $insight->title }}
                                </h3>

                                <p class="insight-card-excerpt mt-3 text-sm leading-6 text-slate-600">
                                    {{ $excerpt($insight) }}
                                </p>

                                <div class="mt-auto pt-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-mist text-xs font-black text-brand-navy">
                                            {{ Str::upper(Str::substr($authorName($insight), 0, 1)) }}
                                        </div>

                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-black text-brand-ink">
                                                {{ $authorName($insight) }}
                                            </p>

                                            <p class="text-[11px] font-semibold text-slate-500">
                                                {{ $readingTime($insight) }} · {{ number_format($wordCount($insight), 0, ',', '.') }} kata
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-4 text-sm font-black text-brand-navy">
                                        Baca selengkapnya →
                                    </div>
                                </div>
                            </div>
                        </a>
                    </article>
                @empty
                    <div class="md:col-span-2 lg:col-span-3">
                        <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                                Tidak Ada Insight
                            </p>

                            <h3 class="mt-3 text-2xl font-black tracking-tight text-brand-ink">
                                Insight belum ditemukan.
                            </h3>

                            <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-slate-600">
                                Coba gunakan kata kunci lain, pilih kategori berbeda, atau hapus filter untuk melihat seluruh insight.
                            </p>

                            <a
                                href="{{ route('insights.index') }}"
                                class="mt-6 inline-flex rounded-full bg-brand-navy px-5 py-3 text-sm font-black text-white transition hover:bg-brand-ink"
                            >
                                Reset Pencarian
                            </a>
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
                                href="{{ $insights->previousPageUrl() }}"
                                class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-black text-brand-navy transition hover:border-brand-navy hover:bg-brand-paper"
                            >
                                ← Sebelumnya
                            </a>
                        @endif

                        @if ($insights->hasMorePages())
                            <a
                                href="{{ $insights->nextPageUrl() }}"
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

    @if ($popularInsights->count())
        <section class="border-t border-slate-200 bg-white py-12 lg:py-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-6 lg:grid-cols-[1fr_0.72fr_1fr]">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-900/5">
                        <h3 class="text-xl font-black tracking-tight text-brand-ink">
                            Paling Banyak Dibaca
                        </h3>

                        <div class="mt-5 grid gap-4">
                            @foreach ($popularInsights->take(3) as $popularIndex => $popular)
                                <a href="{{ route('insights.show', $popular->slug) }}" class="group grid grid-cols-[2rem_minmax(0,1fr)] gap-3 border-b border-slate-100 pb-4 last:border-b-0 last:pb-0">
                                    <div class="text-xl font-black text-brand-navy">
                                        {{ $popularIndex + 1 }}
                                    </div>

                                    <div>
                                        <h4 class="text-sm font-black leading-snug text-brand-ink group-hover:text-brand-navy">
                                            {{ $popular->title }}
                                        </h4>

                                        <p class="mt-1 text-xs font-semibold text-slate-500">
                                            {{ $categoryName($popular) }} · {{ $publishedDate($popular) }}
                                        </p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-900/5">
                        <h3 class="text-xl font-black tracking-tight text-brand-ink">
                            Topik Populer
                        </h3>

                        <div class="mt-5 flex flex-wrap gap-2">
                            @forelse ($insightCategories->take(8) as $category)
                                <a
                                    href="{{ route('insights.index', ['category' => $category->slug]) }}"
                                    class="rounded-full bg-brand-mist px-3 py-2 text-xs font-black text-brand-navy transition hover:bg-brand-navy hover:text-white"
                                >
                                    {{ $category->name }}
                                </a>
                            @empty
                                <p class="text-sm leading-7 text-slate-500">
                                    Topik akan tampil setelah kategori insight tersedia.
                                </p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-900/5">
                        <h3 class="text-xl font-black tracking-tight text-brand-ink">
                            Trending Issue
                        </h3>

                        <div class="mt-5 grid gap-4">
                            @foreach (['Revisi UU TNI', 'Putusan MK', 'AI & Hukum', 'Demokrasi Digital'] as $issue)
                                <a href="{{ route('insights.index', ['q' => $issue]) }}" class="group flex items-center justify-between gap-4 border-b border-slate-100 pb-4 last:border-b-0 last:pb-0">
                                    <div>
                                        <h4 class="text-sm font-black text-brand-ink group-hover:text-brand-navy">
                                            {{ $issue }}
                                        </h4>

                                        <p class="mt-1 text-xs leading-5 text-slate-500">
                                            Pembahasan publik dan catatan kritis dari berbagai kalangan.
                                        </p>
                                    </div>

                                    <span class="text-lg font-black text-brand-navy">→</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
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
</main>
@endsection
