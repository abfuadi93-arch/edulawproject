@props(['articles', 'excerpt', 'categoryName', 'publishedDate', 'readingTime', 'archiveUrl'])

@if ($articles->isNotEmpty())
    @php
        $primary = $articles->first();
        $secondary = $articles->skip(1)->take(4);
    @endphp

    <section id="editorial-terbaru" class="pt-12 pb-12 sm:pt-14 sm:pb-14 lg:pt-16 lg:pb-12">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-5 border-b border-slate-300 pb-5">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-brand-coral">Publikasi terkini</p>
                    <h2 class="mt-2 font-display text-3xl font-bold text-brand-ink sm:text-4xl">Editorial Terbaru</h2>
                </div>
                <a href="{{ $archiveUrl }}" class="shrink-0 text-sm font-bold text-brand-navy underline decoration-brand-amber decoration-2 underline-offset-4 hover:text-brand-coral focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">Lihat semua</a>
            </div>

            <div class="divide-y divide-slate-200 border-b border-slate-200">
                <article class="group py-6" data-latest-editorial="{{ $primary->id }}">
                    <a href="{{ route('insights.show', $primary->slug) }}" class="grid gap-5 rounded-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber sm:grid-cols-[260px_minmax(0,1fr)] lg:grid-cols-[340px_minmax(0,1fr)] lg:gap-8">
                        <div class="relative aspect-[16/10] overflow-hidden rounded-xl bg-slate-100">
                            <img src="{{ $primary->cover_image_url }}" alt="{{ $primary->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.025] motion-reduce:transition-none">
                        </div>
                        <div class="min-w-0 self-center">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-semibold text-slate-500">
                                <span class="font-bold uppercase tracking-[0.1em] text-brand-coral">{{ $categoryName($primary) }}</span>
                                <span class="text-slate-300" aria-hidden="true">•</span>
                                <span>{{ $publishedDate($primary) }}</span>
                            </div>
                            <h3 class="mt-2 line-clamp-3 max-w-3xl text-balance text-2xl font-bold leading-snug text-brand-ink transition group-hover:text-brand-navy sm:text-3xl">{{ $primary->title }}</h3>
                            @if ($excerpt($primary, 190) !== '')
                                <p class="mt-3 line-clamp-3 max-w-3xl text-sm leading-6 text-slate-600">{{ $excerpt($primary, 190) }}</p>
                            @endif
                            <p class="mt-4 text-xs font-semibold text-slate-500">{{ $readingTime($primary) }}</p>
                        </div>
                    </a>
                </article>

                @if ($secondary->isNotEmpty())
                    <div class="grid divide-y divide-slate-200 md:grid-cols-2 md:divide-x md:divide-y-0">
                        @foreach ($secondary->chunk(2) as $column)
                            <div class="divide-y divide-slate-200 md:px-6 md:first:pl-0 md:last:pr-0">
                                @foreach ($column as $article)
                                    <article class="group py-5" data-latest-editorial="{{ $article->id }}">
                                        <a href="{{ route('insights.show', $article->slug) }}" class="grid gap-4 rounded-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber sm:grid-cols-[128px_minmax(0,1fr)] lg:grid-cols-[150px_minmax(0,1fr)]">
                                            <div class="relative aspect-[4/3] overflow-hidden rounded-lg bg-slate-100">
                                                <img src="{{ $article->cover_image_url }}" alt="{{ $article->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.025] motion-reduce:transition-none">
                                            </div>
                                            <div class="min-w-0 self-center">
                                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] font-semibold text-slate-500">
                                                    <span class="font-bold uppercase tracking-[0.1em] text-brand-coral">{{ $categoryName($article) }}</span>
                                                    <span class="text-slate-300" aria-hidden="true">•</span>
                                                    <span>{{ $publishedDate($article) }}</span>
                                                </div>
                                                <h3 class="mt-1.5 line-clamp-2 max-w-3xl text-lg font-bold leading-snug text-brand-ink transition group-hover:text-brand-navy sm:text-xl">{{ $article->title }}</h3>
                                                <p class="mt-3 text-xs font-semibold text-slate-500">{{ $readingTime($article) }}</p>
                                            </div>
                                        </a>
                                    </article>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
