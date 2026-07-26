@props([
    'featuredInsight' => null,
    'insights' => collect(),
])

@php
    $insightCollection = collect($insights);
    $hasInsightIndex = \Illuminate\Support\Facades\Route::has('insights.index');
    $hasInsightShow = \Illuminate\Support\Facades\Route::has('insights.show');

    $featured = $featuredInsight ?: $insightCollection->first();

    $list = $insightCollection
        ->when($featured && isset($featured->id), fn ($collection) => $collection->where('id', '!=', $featured->id))
        ->take(3)
        ->values();

    $primaryAuthor = function ($insight) {
        return $insight && $insight->relationLoaded('authors')
            ? $insight->authors->sortBy(fn ($author) => $author->pivot?->author_order ?? 999)->first()
            : null;
    };

    $authorInitials = function (string $name): string {
        return \Illuminate\Support\Str::of($name)
            ->explode(' ')
            ->filter()
            ->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))
            ->take(2)
            ->implode('') ?: 'E';
    };
@endphp

<section id="edulaw-insight" class="home-section scroll-mt-24 bg-brand-paper" aria-labelledby="home-insights-title">
    <div class="section-shell">
        {{-- Header --}}
        <div class="home-section-header">
            <div class="home-section-copy">
                <p class="home-section-eyebrow text-brand-coral">
                    Edulaw Insight
                </p>

                <h2 id="home-insights-title" class="home-section-title">
                    Edulaw Insight Terbaru
                </h2>

                <p class="home-section-description">
                    Analisis hukum, pembaruan regulasi, dan isu kebijakan publik yang disajikan secara jernih.
                </p>
            </div>

                @if ($hasInsightIndex)
                    <a
                        href="{{ route('insights.index') }}"
                        class="section-link w-fit shrink-0"
                    >
                        Lihat Semua Insight
                        <svg class="h-4 w-4 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                @endif
        </div>

        @if ($featured && $hasInsightShow)
            <div class="mt-8 grid gap-5 lg:grid-cols-[1.08fr_0.92fr]">
                {{-- Featured Editorial --}}
                <article data-home-insight data-home-insight-featured class="home-card home-card-interactive group">
                    <a href="{{ route('insights.show', $featured->slug) }}" class="home-card-link flex h-full flex-col">
                        <div class="relative aspect-video overflow-hidden bg-slate-100">
                            @if ($featured->cover_image_url)
                                <img
                                    src="{{ $featured->cover_image_url }}"
                                    alt="Sampul Insight: {{ $featured->title }}"
                                    width="800"
                                    height="450"
                                    class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                                    loading="lazy"
                                    decoding="async"
                                >
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-linear-to-br from-brand-navy via-brand-charcoal to-brand-teal">
                                    <div class="rounded-2xl border border-white/15 bg-white/10 px-5 py-4 text-center text-white shadow-sm backdrop-blur">
                                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-brand-amber">
                                            Editorial Edulaw
                                        </p>
                                        <p class="mt-2 text-sm font-semibold text-white/80">
                                            Konten visual sedang disiapkan
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <div class="absolute inset-0 bg-linear-to-t from-brand-navy/82 via-brand-navy/18 to-transparent"></div>

                            <div class="absolute left-4 top-4">
                                <span class="inline-flex items-center rounded-md bg-white/90 px-2.5 py-1 text-xs font-bold text-brand-navy shadow-sm backdrop-blur">
                                    {{ $featured->display_category }}
                                </span>
                            </div>

                            <div class="absolute bottom-4 left-4 right-4">
                                <h3 class="max-w-2xl text-2xl font-black leading-tight tracking-tight text-white sm:text-3xl lg:text-[2rem]">
                                    {{ $featured->title }}
                                </h3>
                            </div>
                        </div>

                        <div class="flex flex-1 flex-col p-5">
                            @if ($featured->excerpt)
                                <p class="line-clamp-3 text-[15px] leading-6 text-slate-600">
                                    {{ $featured->excerpt }}
                                </p>
                            @endif

                            @php
                                $featuredAuthor = $primaryAuthor($featured);
                                $featuredAuthorName = $featuredAuthor?->name ?: $featured->display_author;
                            @endphp

                            <div class="home-meta mt-4 flex flex-wrap items-center gap-x-3 gap-y-2">
                                @if ($featured->published_at)
                                    <span>{{ $featured->published_at->translatedFormat('d M Y') }}</span>
                                    <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                @endif
                                @if ($featured->reading_time)
                                    <span>{{ $featured->reading_time }} min read</span>
                                    <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                @endif
                                <span class="inline-flex max-w-full items-center gap-2">
                                    @if ($featuredAuthor?->photo_url)
                                        <img
                                            src="{{ $featuredAuthor->photo_url }}"
                                            alt="Foto profil {{ $featuredAuthorName }}"
                                            width="24"
                                            height="24"
                                            class="h-6 w-6 shrink-0 rounded-full object-cover ring-1 ring-slate-200"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    @else
                                        <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-navy text-[9px] font-black text-white">
                                            {{ $authorInitials($featuredAuthorName) }}
                                        </span>
                                    @endif

                                    <span class="min-w-0">{{ $featuredAuthorName }}</span>
                                </span>
                            </div>

                            <div class="mt-auto flex items-center justify-between gap-4 border-t border-slate-100 pt-5">
                                <span class="text-sm font-bold text-brand-navy underline decoration-brand-amber decoration-2 underline-offset-4">
                                    Baca Insight
                                </span>

                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-brand-navy text-white transition group-hover:bg-brand-amber group-hover:text-brand-black">
                                    <svg class="h-4 w-4 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                </article>

                {{-- Right Side: 3 Cards --}}
                <div class="grid gap-4">
                    @foreach ($list as $item)
                        @php
                            $thumb = $item->cover_image_url;
                            $itemAuthor = $primaryAuthor($item);
                            $itemAuthorName = $itemAuthor?->name ?: $item->display_author;
                        @endphp

                        <article data-home-insight data-home-insight-compact class="home-card home-card-interactive group">
                            <a href="{{ route('insights.show', $item->slug) }}" class="home-card-link grid min-h-40 grid-cols-[120px_1fr] sm:grid-cols-[160px_1fr]">
                                <div class="relative overflow-hidden bg-slate-100">
                                    @if ($thumb)
                                        <img
                                            src="{{ $thumb }}"
                                            alt="Thumbnail Insight: {{ $item->title }}"
                                            width="800"
                                            height="450"
                                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-linear-to-br from-brand-navy via-brand-blue to-brand-teal">
                                            <span class="rounded-full bg-white/15 px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-white">
                                                Editorial
                                            </span>
                                        </div>
                                    @endif

                                    <div class="absolute inset-0 bg-brand-navy/10"></div>
                                </div>

                                <div class="flex min-h-full flex-col p-3.5 sm:p-4">
                                    <p class="text-xs font-bold text-brand-teal">
                                        {{ $item->display_category }}
                                    </p>

                                    <h3 class="mt-1.5 line-clamp-2 text-[15px] font-black leading-snug tracking-tight text-brand-ink transition group-hover:text-brand-navy sm:text-base">
                                        {{ $item->title }}
                                    </h3>

                                    <p class="mt-1.5 line-clamp-2 text-xs leading-5 text-slate-600 sm:text-sm">
                                        {{ $item->excerpt }}
                                    </p>

                                    @if ($item->published_at || $itemAuthorName || $item->reading_time)
                                        <div class="home-meta mt-auto flex flex-wrap items-center gap-x-2 gap-y-1 pt-2">
                                            @if ($item->published_at)
                                                <span>{{ $item->published_at->translatedFormat('d M Y') }}</span>
                                            @endif
                                            @if ($item->published_at && ($itemAuthorName || $item->reading_time))
                                                <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                            @endif
                                            @if ($itemAuthorName)
                                                <span class="line-clamp-1">{{ $itemAuthorName }}</span>
                                            @elseif ($item->reading_time)
                                                <span>{{ $item->reading_time }} min read</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>
        @else
            <div class="home-empty-state mt-8">
                <p class="text-sm leading-6 text-slate-600">
                    Belum ada Insight yang ditampilkan. Nantikan analisis hukum terbaru dari Edulaw Project.
                </p>
                @if ($hasInsightIndex)
                    <a
                        href="{{ route('insights.index') }}"
                        class="btn-dark mt-4 min-h-11"
                    >
                        Lihat Semua Insight
                    </a>
                @endif
            </div>
        @endif
    </div>
</section>
