@props(['opportunities' => collect()])

@php
    $opportunityCollection = collect($opportunities)->take(4)->values();
    $featuredOpportunity = $opportunityCollection->first();
    $secondaryOpportunities = $opportunityCollection->slice(1, 3)->values();
@endphp

<section id="opportunities" class="scroll-mt-20 bg-white py-6 sm:py-10" aria-labelledby="home-opportunities-title">
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
            <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,3fr)_minmax(320px,2fr)]">
                <article data-home-opportunity data-home-opportunity-featured class="group border-y border-slate-200 bg-white">
                    <a href="{{ $featuredOpportunity->external_url }}" target="_blank" rel="noopener noreferrer" aria-label="Buka informasi resmi {{ $featuredOpportunity->title }} (membuka tab baru)" class="grid h-full focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber md:grid-cols-[minmax(0,3fr)_minmax(200px,2fr)]">
                        <div class="flex min-w-0 flex-col justify-center p-5 sm:p-7 lg:p-8">
                            <p class="home-card-kicker">{{ $featuredOpportunity->display_type }}</p>
                            <h3 class="mt-3 line-clamp-3 text-2xl font-bold leading-[1.18] tracking-[-0.025em] text-[#102f56] sm:text-3xl">{{ $featuredOpportunity->title }}</h3>

                            <div class="home-opportunity-deadline mt-5 border-l-2 border-[#f5c451] pl-4">
                                <p class="type-role-meta font-normal uppercase tracking-[0.12em] text-slate-500">Batas akhir</p>
                                <p class="mt-1 text-lg font-bold text-[#a45e08]">{{ $featuredOpportunity->deadline_display }}</p>
                                <p class="mt-1 type-role-meta font-normal text-slate-500">{{ $featuredOpportunity->deadline_relative_label }}</p>
                            </div>

                            @if ($featuredOpportunity->location || $featuredOpportunity->format)
                                <p class="mt-3 type-role-meta font-normal text-slate-500">
                                    {{ $featuredOpportunity->location }}
                                    @if ($featuredOpportunity->location && $featuredOpportunity->format)<span aria-hidden="true"> · </span>@endif
                                    {{ $featuredOpportunity->format ? Illuminate\Support\Str::headline($featuredOpportunity->format) : '' }}
                                </p>
                            @endif

                            <span class="mt-5 inline-flex w-fit text-sm font-bold text-[#102f56] transition group-hover:text-brand-teal">Informasi Resmi ↗</span>
                        </div>

                        <div class="relative order-last aspect-[4/5] w-full self-center overflow-hidden border border-slate-200 p-0">
                            <x-home.media-fallback kind="opportunity" :label="$featuredOpportunity->display_type" data-home-opportunity-fallback />
                            @if ($featuredOpportunity->poster_url)
                                <x-responsive-image :src="$featuredOpportunity->poster_url" alt="Poster {{ $featuredOpportunity->title }}" :widths="[320, 480, 640]" sizes="(min-width: 1024px) 40vw, (min-width: 768px) 40vw, 100vw" width="640" height="800" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.02]" onerror="this.remove()" />
                            @endif
                        </div>
                    </a>
                </article>

                @if ($secondaryOpportunities->isNotEmpty())
                    <div class="grid content-start divide-y divide-slate-200 border-y border-slate-200">
                        @foreach ($secondaryOpportunities as $opportunity)
                            <article data-home-opportunity data-home-opportunity-secondary class="group min-w-0 transition duration-200 hover:bg-slate-50/70">
                                <a href="{{ $opportunity->external_url }}" target="_blank" rel="noopener noreferrer" class="grid h-full grid-cols-[80px_minmax(0,1fr)] gap-4 py-3 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                                    <div class="relative aspect-[4/5] w-full self-center overflow-hidden border border-slate-200 p-0">
                                        <x-home.media-fallback kind="opportunity" data-home-opportunity-fallback />
                                        @if ($opportunity->poster_url)
                                            <x-responsive-image :src="$opportunity->poster_url" alt="Poster {{ $opportunity->title }}" :widths="[96, 160, 240]" sizes="80px" width="160" height="200" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.02]" onerror="this.remove()" />
                                        @endif
                                    </div>
                                    <div class="flex min-w-0 flex-col py-1.5 pr-2">
                                        <p class="home-card-kicker">{{ $opportunity->display_type }}</p>
                                        <h3 class="mt-2 line-clamp-2 text-[15px] font-bold leading-[1.35] text-[#102f56]">{{ $opportunity->title }}</h3>
                                        @if ($opportunity->organizer)
                                            <p class="mt-2 line-clamp-1 type-role-meta font-normal text-slate-500">{{ $opportunity->organizer }}</p>
                                        @endif
                                        <div class="mt-auto pt-3">
                                            <p class="text-xs font-bold text-[#a45e08]">Deadline {{ $opportunity->deadline_display }}</p>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        @else
            <div class="home-empty-state mt-5"><p class="text-sm leading-6 text-slate-600">Belum ada peluang yang sedang dibuka.</p></div>
        @endif

        <a href="{{ route('opportunities.index') }}" class="home-section-link mt-6 sm:hidden">Semua Peluang →</a>
    </div>
</section>
