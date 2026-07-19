@props(['articles', 'excerpt', 'categoryName', 'publishedDate', 'readingTime', 'authorName', 'archiveUrl'])

@if ($articles->isNotEmpty())
    @php
        $primary = $articles->first();
        $secondary = $articles->skip(1)->take(4);
        $metaItems = fn ($article) => collect([
            $categoryName($article),
            $publishedDate($article),
            $readingTime($article),
            $authorName($article),
        ])->filter(fn ($item) => filled($item))->values();
    @endphp

    <section id="editorial-terbaru" class="pt-12 pb-12 sm:pt-14 sm:pb-14 lg:pt-16 lg:pb-12">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-5 border-b border-slate-300 pb-5">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-brand-coral">Publikasi terkini</p>
                    <h2 class="mt-2 font-display text-3xl font-bold text-brand-ink sm:text-4xl">Editorial Terbaru</h2>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-slate-600">Artikel terbaru dari kanal editorial Edulaw.</p>
                </div>
                <a href="{{ $archiveUrl }}" class="shrink-0 text-sm font-bold text-brand-navy underline decoration-brand-amber decoration-2 underline-offset-4 hover:text-brand-coral focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">Lihat semua</a>
            </div>

            <div class="divide-y divide-slate-200 border-b border-slate-200">
                <article class="group py-6" data-latest-editorial="{{ $primary->id }}">
                    <a href="{{ route('insights.show', $primary->slug) }}" class="grid gap-5 rounded-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber sm:grid-cols-[260px_minmax(0,1fr)] lg:grid-cols-[340px_minmax(0,1fr)] lg:gap-8">
                        <div class="relative aspect-[16/10] overflow-hidden rounded-xl bg-slate-100">
                            @if (filled($primary->cover_image))
                                <img src="{{ $primary->cover_image_url }}" alt="{{ $primary->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.025] motion-reduce:transition-none">
                            @else
                                <div class="absolute inset-0 bg-linear-to-br from-brand-navy via-[#1e3f66] to-[#0f172a]"></div>
                                <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_18%,rgba(255,255,255,0.16),transparent_26%),linear-gradient(135deg,rgba(255,255,255,0.08)_0,transparent_42%)]"></div>
                                <span class="absolute bottom-4 left-4 rounded-full bg-white/10 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-white ring-1 ring-white/20">Editorial</span>
                            @endif
                        </div>
                        <div class="min-w-0 self-center">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-semibold text-slate-500">
                                @foreach ($metaItems($primary) as $metaIndex => $meta)
                                    @if ($metaIndex > 0)
                                        <span class="text-slate-300" aria-hidden="true">•</span>
                                    @endif
                                    <span @class([
                                        'font-bold uppercase tracking-[0.1em] text-brand-coral' => $metaIndex === 0,
                                    ])>{{ $meta }}</span>
                                @endforeach
                            </div>
                            <h3 class="mt-2 line-clamp-2 max-w-3xl text-balance text-2xl font-bold leading-snug text-brand-ink transition group-hover:text-brand-navy sm:text-3xl">{{ $primary->title }}</h3>
                            @if ($excerpt($primary, 190) !== '')
                                <p class="mt-3 line-clamp-3 max-w-3xl text-sm leading-6 text-slate-600">{{ $excerpt($primary, 190) }}</p>
                            @endif
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
                                                @if (filled($article->cover_image))
                                                    <img src="{{ $article->cover_image_url }}" alt="{{ $article->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.025] motion-reduce:transition-none">
                                                @else
                                                    <div class="absolute inset-0 bg-linear-to-br from-brand-navy via-[#244972] to-[#0f172a]"></div>
                                                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_18%,rgba(255,255,255,0.16),transparent_28%)]"></div>
                                                @endif
                                            </div>
                                            <div class="min-w-0 self-center">
                                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] font-semibold text-slate-500">
                                                    @foreach ($metaItems($article) as $metaIndex => $meta)
                                                        @if ($metaIndex > 0)
                                                            <span class="text-slate-300" aria-hidden="true">•</span>
                                                        @endif
                                                        <span @class([
                                                            'font-bold uppercase tracking-[0.1em] text-brand-coral' => $metaIndex === 0,
                                                        ])>{{ $meta }}</span>
                                                    @endforeach
                                                </div>
                                                <h3 class="mt-1.5 line-clamp-2 max-w-3xl text-lg font-bold leading-snug text-brand-ink transition group-hover:text-brand-navy sm:text-xl">{{ $article->title }}</h3>
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
@else
    <section id="editorial-terbaru" class="pt-12 pb-12 sm:pt-14 sm:pb-14 lg:pt-16 lg:pb-12">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-8 text-center">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-brand-coral">Editorial Terbaru</p>
                <p class="mt-2 text-sm font-semibold text-slate-600">Artikel terbaru sedang disiapkan.</p>
            </div>
        </div>
    </section>
@endif
