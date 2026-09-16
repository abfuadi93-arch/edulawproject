@props(['insight' => null])

<section id="editorial-pilihan" class="scroll-mt-20" aria-labelledby="home-featured-editorial-title">
    <div class="section-shell">
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
                        <x-responsive-image :src="$insight->cover_image_url" alt="Sampul {{ $insight->title }}" :widths="[480, 640, 960]" sizes="100vw" class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.025]" onerror="this.remove()" />
                    @endif
                    <div class="absolute inset-0 bg-linear-to-t from-[#07172e]/95 via-[#142f57]/60 to-[#142f57]/10"></div>
                    <a href="{{ route('insights.show', $insight->slug) }}" aria-label="Baca editorial: {{ $insight->title }}" class="relative flex flex-1 flex-col justify-end p-6 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-4px] focus-visible:outline-brand-amber sm:p-8">
                        <span class="w-fit rounded bg-[#f8bd38] px-3 py-1.5 text-xs font-bold uppercase tracking-wide text-[#142f57]">Editor's Pick</span>
                        <p class="home-card-kicker mt-5 text-[#f0c55e]">{{ $insight->display_category }}</p>
                        <h3 class="mt-3 line-clamp-3 text-xl font-bold leading-[1.2] tracking-[-0.015em] text-white sm:text-2xl">{{ $insight->title }}</h3>
                        <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
                            <p class="min-w-0 text-sm leading-6 text-slate-200">
                                @if ($insight->published_at){{ $insight->published_at->translatedFormat('d M Y') }} · @endif{{ $insight->display_author }}{{ $insight->reading_time ? ' · '.$insight->reading_time.' menit baca' : '' }}
                            </p>
                            <span class="ml-auto inline-flex shrink-0 items-center text-sm font-bold text-white transition group-hover:text-brand-amber">Baca Editorial →</span>
                        </div>
                    </a>
                </article>
            @else
                <div class="home-empty-state mt-7 flex-1"><p class="text-sm leading-6 text-slate-600">Pilihan editor sedang disiapkan.</p></div>
            @endif
        </div>
    </div>
</section>
