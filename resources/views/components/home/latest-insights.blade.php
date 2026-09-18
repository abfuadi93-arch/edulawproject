@props(['insight' => null, 'insights' => collect()])

@php
    $items = collect($insights)
        ->filter()
        ->when($insight, fn ($collection) => $collection->where('id', '!=', $insight->id))
        ->unique('id')
        ->take(5)
        ->values();
    $primaryItem = $items->first();
    $secondaryItems = $items->slice(1, 4)->values();
@endphp

<section id="artikel-terbaru" aria-labelledby="home-insights-title" class="scroll-mt-20">
    <div class="section-shell">
        <div class="home-latest-insights flex min-w-0 flex-col" aria-labelledby="home-insights-title">
            <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-end sm:justify-between sm:gap-4">
                <div>
                    <p class="home-section-eyebrow">Artikel Terbaru</p>
                    <h2 id="home-insights-title" class="home-section-title">Analisis Hukum yang Relevan</h2>
                </div>
                <a href="{{ route('insights.index') }}" class="home-section-link shrink-0">Lihat Semua Insight →</a>
            </div>

            @if ($primaryItem)
                <div class="home-latest-grid mt-5 flex min-w-0 flex-1 flex-col">
                    <article data-home-insight data-home-insight-latest class="group overflow-hidden rounded-xl border border-slate-200 bg-white transition duration-200 hover:border-slate-300 hover:shadow-sm">
                        <a href="{{ route('insights.show', $primaryItem->slug) }}" class="block focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                            <div class="relative h-52 overflow-hidden sm:h-60 lg:h-[260px] xl:h-[270px]">
                                <x-home.media-fallback kind="editorial" :label="$primaryItem->display_category" />
                                @if ($primaryItem->cover_image_url)
                                    <x-responsive-image :src="$primaryItem->cover_image_url" alt="Sampul {{ $primaryItem->title }}" :widths="[480, 640, 960]" sizes="(min-width: 1280px) 536px, (min-width: 1024px) 43vw, 100vw" width="760" height="428" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.02]" onerror="this.remove()" />
                                @endif
                            </div>
                            <div class="p-5 sm:p-6">
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <p class="home-card-kicker">{{ $primaryItem->display_category }}</p>
                                    @if ($primaryItem->published_at)<p class="type-role-meta font-normal text-slate-500">{{ $primaryItem->published_at->translatedFormat('d M Y') }}</p>@endif
                                </div>
                                <h3 class="mt-3 line-clamp-3 text-[22px] font-bold leading-[1.24] tracking-[-0.018em] text-brand-navy sm:text-2xl">{{ $primaryItem->title }}</h3>
                                <div class="mt-4 flex flex-wrap items-center justify-between gap-x-5 gap-y-3">
                                    <div class="flex min-w-0 flex-wrap gap-x-2 gap-y-1 type-role-meta font-normal leading-5 text-slate-500">
                                        @if ($primaryItem->reading_time)<span>{{ $primaryItem->reading_time }} menit</span><span>·</span>@endif
                                        <span class="line-clamp-1">{{ $primaryItem->display_author }}</span>
                                    </div>
                                    <span class="home-card-action shrink-0">Baca Selengkapnya →</span>
                                </div>
                            </div>
                        </a>
                    </article>

                    @if ($secondaryItems->isNotEmpty())
                        <div class="home-latest-secondary mt-4 grid gap-x-4 gap-y-0 sm:grid-cols-2">
                            @foreach ($secondaryItems as $item)
                                <article data-home-insight class="group min-w-0 border-b border-slate-200">
                                    <a href="{{ route('insights.show', $item->slug) }}" class="grid h-full grid-cols-[112px_minmax(0,1fr)] gap-3 py-3.5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                                        <div class="relative aspect-video w-[112px] overflow-hidden rounded-lg">
                                            <x-home.media-fallback kind="editorial" />
                                            @if ($item->cover_image_url)
                                                <x-responsive-image :src="$item->cover_image_url" alt="Sampul {{ $item->title }}" :widths="[96, 240, 480, 640]" sizes="(min-width: 1024px) 144px, 112px" width="256" height="144" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.02]" onerror="this.remove()" />
                                            @endif
                                        </div>
                                        <div class="flex min-w-0 flex-col py-0.5">
                                            <p class="home-card-kicker line-clamp-1">{{ $item->display_category }}</p>
                                            <h3 class="mt-1.5 line-clamp-3 text-[15px] font-bold leading-[1.4] text-brand-navy sm:text-base">{{ $item->title }}</h3>
                                            <div class="mt-2 flex flex-wrap gap-x-2 type-role-meta font-normal text-slate-500">
                                                @if ($item->published_at)<span>{{ $item->published_at->translatedFormat('d M Y') }}</span>@endif
                                                @if ($item->reading_time)<span>· {{ $item->reading_time }} menit</span>@endif
                                            </div>
                                        </div>
                                    </a>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div class="home-empty-state mt-7 py-4">
                    <p class="text-sm leading-6 text-slate-600">Insight terbaru sedang disiapkan.</p>
                    <a href="{{ route('insights.index') }}" class="home-section-link mt-3">Lihat Semua Insight →</a>
                </div>
            @endif
        </div>

    </div>
</section>
