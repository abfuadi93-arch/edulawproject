@props(['opportunities' => collect()])

@php
    $opportunityCollection = collect($opportunities)->take(4)->values();
    $featuredOpportunity = $opportunityCollection->first();
    $secondaryOpportunities = $opportunityCollection->slice(1, 3)->values();
@endphp

<section id="opportunities" class="home-section home-surface-warm scroll-mt-20 border-y border-[#e8e5dc]" aria-labelledby="home-opportunities-title">
    <div class="section-shell">
        <div class="home-section-header">
            <div class="home-section-copy">
                <p class="home-section-eyebrow">Opportunities</p>
                <h2 id="home-opportunities-title" class="home-section-title">Peluang untuk Tumbuh dan Berkontribusi</h2>
                <p class="home-section-description">Ragam kesempatan untuk belajar, berkembang, dan memberi dampak nyata.</p>
            </div>
            <a href="{{ route('opportunities.index') }}" class="home-section-link hidden sm:inline-flex">Semua Peluang →</a>
        </div>

        @if ($featuredOpportunity)
            <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1.8fr)_minmax(330px,1fr)]">
                <article data-home-opportunity data-home-opportunity-featured class="grid overflow-hidden rounded-xl border border-[#e7ebf0] bg-white md:grid-cols-[minmax(0,1.05fr)_minmax(250px,.78fr)]">
                    <div class="flex min-w-0 flex-col justify-center p-6 sm:p-8 lg:p-9">
                        <p class="home-card-kicker">{{ $featuredOpportunity->display_type }}</p>
                        <h3 class="mt-3 line-clamp-4 text-2xl font-extrabold leading-[1.18] tracking-[-0.025em] text-[#102f56] sm:text-3xl">{{ $featuredOpportunity->title }}</h3>

                        @if ($featuredOpportunity->location || $featuredOpportunity->format)
                            <p class="mt-4 text-sm font-bold text-slate-500">
                                {{ $featuredOpportunity->location }}
                                @if ($featuredOpportunity->location && $featuredOpportunity->format)<span aria-hidden="true"> · </span>@endif
                                {{ $featuredOpportunity->format ? Illuminate\Support\Str::headline($featuredOpportunity->format) : '' }}
                            </p>
                        @endif

                        <div class="mt-7 border-l-2 border-[#f5c451] pl-4">
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.12em] text-slate-500">Batas akhir</p>
                            <p class="mt-1 text-lg font-extrabold text-[#a45e08]">{{ $featuredOpportunity->deadline_display }}</p>
                            <p class="mt-1 text-xs font-bold text-slate-500">{{ $featuredOpportunity->deadline_relative_label }}</p>
                        </div>

                        <a href="{{ route('opportunities.show', $featuredOpportunity->slug) }}" class="mt-7 inline-flex w-fit text-sm font-extrabold text-[#102f56] transition hover:text-brand-teal">Lihat Detail →</a>
                    </div>

                    <div class="relative order-first min-h-[340px] overflow-hidden bg-[#dfe5eb] md:order-last md:min-h-full">
                        <div data-home-opportunity-fallback class="absolute inset-0 grid place-items-center bg-[linear-gradient(145deg,#dbe7ef,#a9becf)] text-7xl font-black text-[#0d315e]/20" aria-hidden="true">{{ mb_substr($featuredOpportunity->display_type, 0, 1) }}</div>
                        @if ($featuredOpportunity->poster_url)
                            <x-responsive-image :src="$featuredOpportunity->poster_url" alt="Poster {{ $featuredOpportunity->title }}" :widths="[320, 480, 640]" sizes="(min-width: 1024px) 320px, (min-width: 768px) 42vw, 100vw" width="640" height="800" class="absolute inset-0 h-full w-full object-cover transition duration-500 hover:scale-[1.02]" onerror="this.remove()" />
                        @endif
                    </div>
                </article>

                @if ($secondaryOpportunities->isNotEmpty())
                    <div class="grid auto-rows-fr gap-3">
                        @foreach ($secondaryOpportunities as $opportunity)
                            <article data-home-opportunity data-home-opportunity-secondary class="group overflow-hidden rounded-xl border border-[#e7ebf0] bg-white transition hover:border-slate-300">
                                <a href="{{ route('opportunities.show', $opportunity->slug) }}" class="grid h-full grid-cols-[104px_minmax(0,1fr)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber sm:grid-cols-[120px_minmax(0,1fr)]">
                                    <div class="relative min-h-32 overflow-hidden bg-[#dfe5eb]">
                                        <div data-home-opportunity-fallback class="absolute inset-0 grid place-items-center text-4xl font-black text-[#0d315e]/20" aria-hidden="true">{{ mb_substr($opportunity->display_type, 0, 1) }}</div>
                                        @if ($opportunity->poster_url)
                                            <x-responsive-image :src="$opportunity->poster_url" alt="Poster {{ $opportunity->title }}" :widths="[160, 240, 320]" sizes="(min-width: 640px) 120px, 104px" width="240" height="300" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.02]" onerror="this.remove()" />
                                        @endif
                                    </div>
                                    <div class="min-w-0 p-4">
                                        <p class="home-card-kicker">{{ $opportunity->display_type }}</p>
                                        <h3 class="mt-2 line-clamp-2 text-[16px] font-extrabold leading-[1.3] text-[#102f56]">{{ $opportunity->title }}</h3>
                                        <p class="mt-3 text-xs font-extrabold text-[#a45e08]">Deadline {{ $opportunity->deadline_display }}</p>
                                    </div>
                                </a>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        @else
            <div class="home-empty-state mt-8"><p class="text-sm leading-6 text-slate-600">Belum ada peluang yang sedang dibuka.</p></div>
        @endif

        <a href="{{ route('opportunities.index') }}" class="home-section-link mt-6 sm:hidden">Semua Peluang →</a>
    </div>
</section>
