@props(['articles', 'popularArticles', 'recentArticles', 'popularHasViews' => false, 'excerpt', 'categoryName', 'publishedDate', 'readingTime', 'archiveUrl'])

@php
    $articles = collect($articles ?? [])->take(6)->values();
    $popularArticles = collect($popularArticles ?? [])->take(5)->values();
    $recentArticles = collect($recentArticles ?? [])->take(5)->values();
    $hasImage = fn ($article): bool => filled($article?->cover_image) && edulaw_file_exists($article->cover_image);
@endphp

@if ($articles->isNotEmpty() || $popularArticles->isNotEmpty() || $recentArticles->isNotEmpty())
    <section id="editorial-terbaru" class="bg-white py-10 sm:py-12 lg:py-14" aria-labelledby="latest-editorial-heading">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_320px] lg:gap-12">
                <div class="min-w-0">
                    <div class="flex items-end justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <h2 id="latest-editorial-heading" class="font-display text-2xl font-bold text-brand-navy sm:text-3xl">Editorial Terbaru</h2>
                            <span class="h-1 w-10 rounded-full bg-brand-amber" aria-hidden="true"></span>
                        </div>
                        <a href="{{ $archiveUrl }}" class="shrink-0 text-sm font-bold text-brand-navy underline decoration-brand-amber decoration-2 underline-offset-4 hover:text-brand-coral">Lihat semua</a>
                    </div>

                    @if ($articles->isNotEmpty())
                        <div class="mt-7 grid gap-x-6 gap-y-8 sm:grid-cols-2">
                            @foreach ($articles as $article)
                                <article class="group min-w-0" data-latest-editorial="{{ $article->id }}">
                                    <a href="{{ route('insights.show', $article->slug) }}" class="block rounded-2xl focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">
                                        <div class="relative aspect-[16/10] overflow-hidden rounded-2xl bg-brand-navy shadow-sm ring-1 ring-black/5">
                                            @if ($hasImage($article))
                                                <img src="{{ $article->cover_image_url }}" alt="{{ $article->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03] motion-reduce:transition-none">
                                            @else
                                                <div class="absolute inset-0 bg-linear-to-br from-brand-navy via-[#244972] to-[#0f766e]"></div>
                                            @endif
                                            <span class="absolute bottom-3 left-3 rounded-full bg-brand-teal px-2.5 py-1 text-[9px] font-extrabold uppercase tracking-[0.12em] text-white">{{ $categoryName($article) }}</span>
                                        </div>
                                        <h3 class="mt-3 line-clamp-2 text-lg font-bold leading-snug text-brand-ink transition group-hover:text-brand-navy">{{ $article->title }}</h3>
                                        <p class="mt-2 text-[11px] font-semibold text-slate-500">{{ $publishedDate($article) }} · {{ $readingTime($article) }}</p>
                                        @if ($excerpt($article, 130) !== '')
                                            <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">{{ $excerpt($article, 130) }}</p>
                                        @endif
                                    </a>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-7 rounded-2xl border border-dashed border-slate-300 px-5 py-7 text-sm font-semibold text-slate-500">Editorial terbaru sedang disiapkan.</p>
                    @endif

                    <div class="mt-9 text-center">
                        <a href="{{ $archiveUrl }}" class="inline-flex min-h-11 items-center justify-center rounded-full border border-brand-navy/20 bg-white px-5 text-sm font-bold text-brand-navy shadow-sm transition hover:border-brand-navy hover:bg-brand-navy hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">Lihat Semua Editorial</a>
                    </div>
                </div>

                <aside class="min-w-0 space-y-9 lg:border-l lg:border-slate-200 lg:pl-8" aria-label="Ringkasan editorial">
                    @if ($popularArticles->isNotEmpty())
                        <section aria-labelledby="popular-editorial-heading">
                            <div class="flex items-center gap-3">
                                <h2 id="popular-editorial-heading" class="font-display text-xl font-bold text-brand-navy">Populer</h2>
                                <span class="h-0.5 w-8 bg-brand-amber" aria-hidden="true"></span>
                            </div>
                            <ol class="mt-4 divide-y divide-slate-200">
                                @foreach ($popularArticles as $index => $article)
                                    <li data-most-read-item>
                                        <a href="{{ route('insights.show', $article->slug) }}" class="group grid grid-cols-[38px_minmax(0,1fr)] gap-3 py-3 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                                            <span class="font-display text-2xl font-bold tabular-nums text-brand-navy/25">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                            <span class="min-w-0">
                                                <span class="block text-[9px] font-bold uppercase tracking-[0.12em] text-brand-coral">{{ $categoryName($article) }}</span>
                                                <span class="mt-1 block line-clamp-2 text-sm font-bold leading-snug text-brand-ink transition group-hover:text-brand-navy">{{ $article->title }}</span>
                                                <span class="mt-1 block text-[10px] font-medium text-slate-500">
                                                    {{ $publishedDate($article) }}
                                                    @if ($popularHasViews && (int) $article->getAttribute('visit_count') > 0)
                                                        · {{ number_format((int) $article->getAttribute('visit_count'), 0, ',', '.') }} kali dibaca
                                                    @endif
                                                </span>
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ol>
                        </section>
                    @endif

                    @if ($recentArticles->isNotEmpty())
                        <section aria-labelledby="recent-sidebar-heading">
                            <div class="flex items-center gap-3">
                                <h2 id="recent-sidebar-heading" class="font-display text-xl font-bold text-brand-navy">Terbaru</h2>
                                <span class="h-0.5 w-8 bg-brand-amber" aria-hidden="true"></span>
                            </div>
                            <div class="mt-4 divide-y divide-slate-200">
                                @foreach ($recentArticles as $article)
                                    <a href="{{ route('insights.show', $article->slug) }}" class="group grid grid-cols-[68px_minmax(0,1fr)] gap-3 py-3 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                                        <div class="relative aspect-square overflow-hidden rounded-lg bg-brand-navy">
                                            @if ($hasImage($article))
                                                <img src="{{ $article->cover_image_url }}" alt="" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105 motion-reduce:transition-none">
                                            @else
                                                <div class="absolute inset-0 bg-linear-to-br from-brand-navy to-[#0f766e]"></div>
                                            @endif
                                        </div>
                                        <span class="min-w-0 self-center">
                                            <span class="block line-clamp-2 text-sm font-bold leading-snug text-brand-ink transition group-hover:text-brand-navy">{{ $article->title }}</span>
                                            <span class="mt-1 block text-[10px] font-medium text-slate-500">{{ $publishedDate($article) }}</span>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </aside>
            </div>
        </div>
    </section>
@endif
