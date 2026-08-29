@props([
    'insight' => null,
    'insights' => collect(),
])

@php
    $items = collect($insights)
        ->filter()
        ->when($insight, fn ($collection) => $collection->where('id', '!=', $insight->id))
        ->unique('id')
        ->take(6)
        ->values();
@endphp

<section id="editorial-pilihan" class="home-surface-warm scroll-mt-20 py-8 sm:py-9 lg:py-10">
    <div class="section-shell grid gap-8 xl:grid-cols-[minmax(0,2fr)_minmax(340px,1fr)] xl:gap-10">
        <div class="flex min-w-0 flex-col" aria-labelledby="home-insights-title">
            <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-end sm:justify-between sm:gap-4">
                <div>
                    <p class="home-section-eyebrow text-[#e57b66]">Edulaw Insight Terbaru</p>
                    <h2 id="home-insights-title" class="home-section-title">Analisis Hukum yang Relevan</h2>
                </div>
                <a href="{{ route('insights.index') }}" class="home-section-link shrink-0">Semua Editorial →</a>
            </div>

            @if ($items->isNotEmpty())
                <div class="mt-7 grid flex-1 auto-rows-fr grid-cols-1 gap-x-4 gap-y-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($items as $index => $item)
                        <article data-home-insight @if ($index === 0) data-home-insight-latest @endif class="group flex h-full flex-col overflow-hidden rounded-xl border border-[#e7ebf0] bg-white transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-sm">
                            <a href="{{ route('insights.show', $item->slug) }}" class="flex h-full flex-1 flex-col focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                                <div class="relative aspect-[16/9] overflow-hidden bg-[linear-gradient(135deg,#173b63,#4a8796)]">
                                    @if ($item->cover_image_url)
                                        <x-responsive-image :src="$item->cover_image_url" alt="Sampul {{ $item->title }}" :widths="[320, 480, 640]" sizes="(min-width: 1280px) 260px, (min-width: 640px) 50vw, 100vw" width="520" height="292" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.025]" onerror="this.remove()" />
                                    @endif
                                </div>
                                <div class="flex flex-1 flex-col p-4">
                                    <p class="home-card-kicker">{{ $item->display_category }}</p>
                                    <h3 class="home-card-title line-clamp-3 min-h-[4.3rem]">{{ $item->title }}</h3>
                                    <div class="home-card-meta mt-auto flex flex-wrap gap-x-2 gap-y-1 pt-4">
                                        @if ($item->published_at)<span>{{ $item->published_at->translatedFormat('d M Y') }}</span>@endif
                                        @if ($item->published_at && $item->reading_time)<span>·</span>@endif
                                        @if ($item->reading_time)<span>{{ $item->reading_time }} menit</span>@endif
                                    </div>
                                    <p class="mt-2 line-clamp-1 text-xs font-bold text-slate-500">Oleh: {{ $item->display_author }}</p>
                                </div>
                            </a>
                        </article>
                    @endforeach
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
                    <p class="home-section-eyebrow text-[#e57b66]">Pilihan Editor</p>
                    <h2 id="home-featured-editorial-title" class="home-section-title">Perspektif Utama</h2>
                </div>
                <a href="{{ route('insights.index') }}" class="home-section-link shrink-0">Editorial →</a>
            </div>

            @if ($insight)
                <article data-home-insight-featured class="group relative mt-7 flex min-h-[420px] flex-1 overflow-hidden rounded-xl bg-[linear-gradient(145deg,#142f57,#155e68)] text-white sm:min-h-[460px]">
                    @if ($insight->cover_image_url)
                        <x-responsive-image :src="$insight->cover_image_url" alt="Sampul {{ $insight->title }}" :widths="[480, 640, 960]" sizes="(min-width: 1280px) 400px, 100vw" class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.025]" />
                    @endif
                    <div class="absolute inset-0 bg-linear-to-t from-[#07172e]/95 via-[#142f57]/60 to-[#142f57]/10"></div>
                    <a href="{{ route('insights.show', $insight->slug) }}" aria-label="Baca editorial: {{ $insight->title }}" class="relative flex flex-1 flex-col justify-end p-6 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-4px] focus-visible:outline-brand-amber sm:p-8">
                        <span class="w-fit rounded bg-[#f8bd38] px-3 py-1.5 text-[11px] font-extrabold uppercase tracking-wide text-[#142f57]">Editor's Pick</span>
                        <p class="home-card-kicker mt-5 text-[#f0c55e]">{{ $insight->display_category }}</p>
                        <h3 class="mt-3 line-clamp-4 text-2xl font-extrabold leading-[1.18] tracking-[-0.015em] text-white sm:text-3xl">{{ $insight->title }}</h3>
                        <div class="mt-5 flex flex-wrap items-end justify-between gap-4">
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
