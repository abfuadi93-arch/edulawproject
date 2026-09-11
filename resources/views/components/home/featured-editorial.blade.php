@props([
    'insight' => null,
    'insights' => collect(),
])

@php
    $items = collect($insights)
        ->filter()
        ->when($insight, fn ($collection) => $collection->where('id', '!=', $insight->id))
        ->unique('id')
        ->take(3)
        ->values();
    $primaryItem = $items->first();
    $secondaryItems = $items->slice(1, 2)->values();
@endphp

<section id="editorial-pilihan" class="scroll-mt-20 bg-white pb-8 pt-6 sm:pb-10 sm:pt-8">
    <div class="section-shell grid gap-9 lg:grid-cols-[minmax(0,1.38fr)_minmax(340px,1fr)] lg:gap-8 xl:gap-10">
        <div class="flex min-w-0 flex-col" aria-labelledby="home-insights-title">
            <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-end sm:justify-between sm:gap-4">
                <div>
                    <p class="home-section-eyebrow">Artikel Terbaru</p>
                    <h2 id="home-insights-title" class="home-section-title">Analisis Hukum yang Relevan</h2>
                </div>
                <a href="{{ route('insights.index') }}" class="home-section-link shrink-0">Lihat Semua Insight →</a>
            </div>

            @if ($primaryItem)
                <div class="mt-7 flex flex-1 flex-col">
                    <article data-home-insight data-home-insight-latest class="group overflow-hidden rounded-xl border border-slate-200 bg-white transition duration-200 hover:border-slate-300 hover:shadow-sm">
                        <a href="{{ route('insights.show', $primaryItem->slug) }}" class="block focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                            <div class="relative h-52 overflow-hidden sm:h-60 lg:h-[260px] xl:h-[270px]">
                                <x-home.media-fallback kind="editorial" :label="$primaryItem->display_category" />
                                @if ($primaryItem->cover_image_url)
                                    <x-responsive-image :src="$primaryItem->cover_image_url" alt="Sampul {{ $primaryItem->title }}" :widths="[480, 640, 960]" sizes="(min-width: 1024px) 58vw, 100vw" width="760" height="428" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.02]" onerror="this.remove()" />
                                @endif
                            </div>
                            <div class="p-5 sm:p-6">
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <p class="home-card-kicker">{{ $primaryItem->display_category }}</p>
                                    @if ($primaryItem->published_at)<p class="text-xs font-semibold text-slate-500">{{ $primaryItem->published_at->translatedFormat('d M Y') }}</p>@endif
                                </div>
                                <h3 class="mt-3 line-clamp-3 text-[22px] font-extrabold leading-[1.24] tracking-[-0.018em] text-brand-navy sm:text-2xl">{{ $primaryItem->title }}</h3>
                                <div class="mt-4 flex flex-wrap items-center justify-between gap-x-5 gap-y-3">
                                    <div class="flex min-w-0 flex-wrap gap-x-2 gap-y-1 text-xs font-bold leading-5 text-slate-500">
                                        @if ($primaryItem->reading_time)<span>{{ $primaryItem->reading_time }} menit</span><span>·</span>@endif
                                        <span class="line-clamp-1">{{ $primaryItem->display_author }}</span>
                                    </div>
                                    <span class="home-card-action shrink-0">Baca Selengkapnya →</span>
                                </div>
                            </div>
                        </a>
                    </article>

                    @if ($secondaryItems->isNotEmpty())
                        <div class="mt-4 grid gap-x-5 sm:grid-cols-2">
                            @foreach ($secondaryItems as $item)
                                <article data-home-insight class="group border-y border-slate-200">
                                    <a href="{{ route('insights.show', $item->slug) }}" class="grid h-full grid-cols-[72px_minmax(0,1fr)] gap-3 py-3.5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                                        <div class="relative aspect-[3/4] w-[72px] overflow-hidden rounded-lg">
                                            <x-home.media-fallback kind="editorial" />
                                            @if ($item->cover_image_url)
                                                <x-responsive-image :src="$item->cover_image_url" alt="Sampul {{ $item->title }}" :widths="[96, 160, 240]" sizes="72px" width="144" height="192" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.02]" onerror="this.remove()" />
                                            @endif
                                        </div>
                                        <div class="flex min-w-0 flex-col py-0.5">
                                            <p class="home-card-kicker line-clamp-1">{{ $item->display_category }}</p>
                                            <h3 class="mt-1.5 line-clamp-3 text-[15px] font-bold leading-[1.35] text-brand-navy sm:text-base">{{ $item->title }}</h3>
                                            <div class="mt-auto flex flex-wrap gap-x-2 pt-2 text-xs font-semibold text-slate-500">
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

        <div class="flex min-w-0 flex-col" aria-labelledby="home-featured-editorial-title">
            <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-end sm:justify-between sm:gap-4">
                <div>
                    <p class="home-section-eyebrow">Kurasi Editorial</p>
                    <h2 id="home-featured-editorial-title" class="home-section-title">Pilihan Editor</h2>
                </div>
                <a href="{{ route('insights.index') }}" class="home-section-link shrink-0">Lihat Semua →</a>
            </div>

            @if ($insight)
                <article data-home-insight-featured class="group relative mt-7 flex aspect-[4/3] flex-1 overflow-hidden rounded-xl bg-[#142f57] text-white sm:aspect-[16/10] lg:aspect-auto">
                    <x-home.media-fallback kind="editorial" dark placement="corner" />
                    @if ($insight->cover_image_url)
                        <x-responsive-image :src="$insight->cover_image_url" alt="Sampul {{ $insight->title }}" :widths="[480, 640, 960]" sizes="(min-width: 1024px) 42vw, 100vw" class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.025]" onerror="this.remove()" />
                    @endif
                    <div class="absolute inset-0 bg-linear-to-t from-[#07172e]/95 via-[#142f57]/60 to-[#142f57]/10"></div>
                    <a href="{{ route('insights.show', $insight->slug) }}" aria-label="Baca editorial: {{ $insight->title }}" class="relative flex flex-1 flex-col justify-end p-6 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-4px] focus-visible:outline-brand-amber sm:p-8">
                        <span class="w-fit rounded bg-[#f8bd38] px-3 py-1.5 text-xs font-extrabold uppercase tracking-wide text-[#142f57]">Editor's Pick</span>
                        <p class="home-card-kicker mt-5 text-[#f0c55e]">{{ $insight->display_category }}</p>
                        <h3 class="mt-3 line-clamp-3 text-xl font-extrabold leading-[1.2] tracking-[-0.015em] text-white sm:text-2xl">{{ $insight->title }}</h3>
                        <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
                            <p class="min-w-0 text-sm leading-6 text-slate-200">
                                @if ($insight->published_at){{ $insight->published_at->translatedFormat('d M Y') }} · @endif{{ $insight->display_author }}{{ $insight->reading_time ? ' · '.$insight->reading_time.' menit baca' : '' }}
                            </p>
                            <span class="ml-auto inline-flex shrink-0 items-center text-sm font-extrabold text-white transition group-hover:text-brand-amber">Baca Editorial →</span>
                        </div>
                    </a>
                </article>
            @else
                <div class="home-empty-state mt-7 flex-1"><p class="text-sm leading-6 text-slate-600">Pilihan editor sedang disiapkan.</p></div>
            @endif
        </div>
    </div>
</section>
