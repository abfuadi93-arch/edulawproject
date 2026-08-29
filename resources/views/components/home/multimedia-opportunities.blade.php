@props([
    'featuredMultimedia' => null,
    'multimediaItems' => collect(),
    'opportunities' => collect(),
])

@php
    $mainMultimedia = $featuredMultimedia ?: $multimediaItems->first();

    $sideMultimedia = $multimediaItems
        ->when($mainMultimedia, fn ($collection) => $collection->where('id', '!=', $mainMultimedia->id))
        ->take(3)
        ->values();

    $multimediaUrl = function ($item) {
        return \App\Support\EdulawSite::resolveUrl($item?->media_url)
            ?: \App\Support\EdulawSite::resolveUrl($item?->embed_url);
    };

    $externalOpportunityUrl = fn ($opportunity) => $opportunity?->external_url ?: route('opportunities.index');

    $isExternalOpportunityUrl = fn ($opportunity) => filled($opportunity?->external_url);

    $opportunityButtonLabel = function ($opportunity) {
        return 'Lihat Informasi Resmi';
    };
@endphp

<section class="bg-[#F9F5F6] py-8 lg:py-10">
    <div class="section-shell">
        <div class="grid gap-4 lg:grid-cols-[1.12fr_0.88fr] lg:items-start">
            {{-- Multimedia --}}
            <div>
                {{-- Header --}}
                <div>
                    <div class="max-w-full">
                        <p class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.18em] text-brand-teal shadow-sm ring-1 ring-slate-200">
                            <span class="h-2 w-2 rounded-full bg-brand-teal"></span>
                            Multimedia
                        </p>

                        <h2 class="mt-1.5 max-w-none text-2xl font-extrabold tracking-normal text-brand-ink sm:text-3xl lg:whitespace-nowrap lg:text-[1.95rem] xl:text-[2rem]">
                            Konten Visual dan Dokumentasi
                        </h2>
                    </div>

                    <div class="no-scrollbar mt-3 flex gap-2 overflow-x-auto pb-1 lg:justify-end lg:pb-0">
                        <a href="{{ route('multimedia.index') }}" class="section-link">
                            Lihat Semua Multimedia
                        </a>
                    </div>
                </div>

                @if ($mainMultimedia)
                    @php
                        $mainUrl = $multimediaUrl($mainMultimedia);
                    @endphp

                    <article class="group mt-3 overflow-hidden rounded-2xl bg-brand-navy shadow-sm ring-1 ring-brand-ink/10 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-brand-ink/10">
                        @if ($mainUrl)
                            <a href="{{ $mainUrl }}" target="_blank" rel="noopener noreferrer" class="block">
                        @else
                            <div class="block">
                        @endif
                            <div class="relative h-64 overflow-hidden sm:h-80">
                                @if ($mainMultimedia->thumbnail_url)
                                    <img
                                        src="{{ $mainMultimedia->thumbnail_url }}"
                                        alt="{{ $mainMultimedia->title }}"
                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                    >
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-linear-to-br from-brand-navy via-brand-charcoal to-brand-teal">
                                        <div class="rounded-2xl border border-white/15 bg-white/10 px-5 py-4 text-center text-white shadow-sm backdrop-blur">
                                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-brand-amber">
                                                Multimedia Edulaw
                                            </p>
                                            <p class="mt-2 text-sm font-semibold text-white/80">
                                                Thumbnail sedang disiapkan
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                <div class="absolute inset-0 bg-linear-to-t from-brand-navy via-brand-navy/40 to-transparent"></div>
                                <div class="absolute inset-0 bg-linear-to-r from-brand-navy/40 via-transparent to-transparent"></div>

                                <div class="absolute left-5 top-5">
                                    <span class="inline-flex rounded-full bg-brand-amber px-3 py-1 text-[11px] font-black uppercase tracking-[0.11em] text-brand-black shadow-sm">
                                        {{ $mainMultimedia->display_type }}
                                    </span>
                                </div>

                                <div class="absolute right-5 top-5">
                                    <span class="inline-flex rounded-full bg-brand-black/70 px-3 py-1 text-xs font-bold text-white backdrop-blur">
                                        {{ $mainMultimedia->display_meta }}
                                    </span>
                                </div>

                                @if ($mainUrl)
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <span class="flex h-16 w-16 items-center justify-center rounded-full bg-white/90 text-brand-black shadow-xl transition group-hover:scale-105 group-hover:bg-brand-amber">
                                            <svg class="ml-1 h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                <path d="M8 5v14l11-7L8 5Z"/>
                                            </svg>
                                        </span>
                                    </div>
                                @endif

                                <div class="absolute bottom-0 left-0 right-0 p-3 sm:p-4">
                                    <h3 class="max-w-2xl text-xl font-extrabold leading-tight tracking-normal text-white sm:text-2xl">
                                        {{ $mainMultimedia->title }}
                                    </h3>

                                    <p class="mt-2 max-w-xl text-[15px] leading-7 text-white/80">
                                        {{ $mainMultimedia->description }}
                                    </p>
                                </div>
                            </div>
                        @if ($mainUrl)
                            </a>
                        @else
                            </div>
                        @endif
                    </article>
                @else
                    <div class="mt-3 rounded-2xl border border-dashed border-slate-300 bg-white p-5 text-center shadow-sm">
                        <p class="text-sm font-black text-brand-ink">
                            Konten sedang disiapkan.
                        </p>
                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Multimedia published akan tampil otomatis di sini.
                        </p>
                    </div>
                @endif

                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                    @foreach ($sideMultimedia as $item)
                        @php
                            $itemUrl = $multimediaUrl($item);
                        @endphp

                        <article class="group overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-brand-ink/10 transition duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-brand-ink/10">
                            @if ($itemUrl)
                                <a href="{{ $itemUrl }}" target="_blank" rel="noopener noreferrer" class="block">
                            @else
                                <div class="block">
                            @endif
                                <div class="relative aspect-video overflow-hidden bg-slate-100">
                                    @if ($item->thumbnail_url)
                                        <img
                                            src="{{ $item->thumbnail_url }}"
                                            alt="{{ $item->title }}"
                                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                        >
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-linear-to-br from-brand-navy via-brand-blue to-brand-teal">
                                            <span class="rounded-full bg-white/15 px-3 py-1 text-[11px] font-black uppercase tracking-[0.11em] text-white">
                                                Multimedia
                                            </span>
                                        </div>
                                    @endif

                                    <div class="absolute inset-0 bg-linear-to-t from-brand-navy/70 via-brand-navy/10 to-transparent"></div>

                                    <div class="absolute left-3 top-3">
                                        <span class="rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-black uppercase tracking-[0.09em] text-brand-ink shadow-sm">
                                            {{ $item->display_type }}
                                        </span>
                                    </div>

                                    <div class="absolute right-3 top-3">
                                        <span class="rounded-full bg-brand-black/70 px-2.5 py-1 text-[11px] font-bold text-white backdrop-blur">
                                            {{ $item->display_meta }}
                                        </span>
                                    </div>

                                    @if ($itemUrl)
                                        <div class="absolute bottom-3 left-3 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-brand-black shadow-sm transition group-hover:bg-brand-amber">
                                            <svg class="ml-0.5 h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                <path d="M8 5v14l11-7L8 5Z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <div class="p-3">
                                    <h4 class="line-clamp-2 text-sm font-extrabold leading-snug text-brand-ink">
                                        {{ $item->title }}
                                    </h4>
                                </div>
                            @if ($itemUrl)
                                </a>
                            @else
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>

            {{-- Opportunities --}}
            <aside class="rounded-2xl border border-brand-amber/25 bg-[#fbf7ef] p-4 shadow-sm shadow-brand-ink/5 lg:mt-13">
                <div>
                    <p class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.18em] text-brand-amber shadow-sm ring-1 ring-brand-amber/25">
                        <span class="h-2 w-2 rounded-full bg-brand-amber"></span>
                        Opportunities
                    </p>

                    <h2 class="mt-1.5 text-2xl font-extrabold tracking-normal text-brand-ink">
                        Opportunities Terbaru
                    </h2>

                    <p class="mt-2 text-[15px] leading-7 text-slate-600">
                        Kanal pengembangan untuk belajar, berjejaring, dan bertumbuh di bidang hukum.
                    </p>
                </div>

                @if ($opportunities->count())
                    <div class="mt-3 space-y-2.5">
                        @foreach ($opportunities as $opportunity)
                            <article class="group rounded-2xl border border-brand-ink/10 bg-white p-3 shadow-sm shadow-brand-ink/5 transition duration-300 hover:-translate-y-0.5 hover:border-brand-amber/50 hover:shadow-lg hover:shadow-brand-ink/10">
                                <div class="flex items-start justify-between gap-3">
                                    <span class="inline-flex shrink-0 rounded-full bg-brand-amber-soft px-2.5 py-1 text-[11px] font-black uppercase tracking-[0.09em] text-brand-ink">
                                        {{ $opportunity->display_type }}
                                    </span>

                                    <span class="rounded-full bg-brand-paper px-2.5 py-1 text-right text-[11px] font-bold uppercase tracking-[0.09em] text-brand-blue">
                                        {{ $opportunity->format ?: $opportunity->location ?: '-' }}
                                    </span>
                                </div>

                                <h3 class="mt-2 text-[15px] font-extrabold leading-snug text-brand-ink">
                                    {{ $opportunity->title }}
                                </h3>

                                <p class="mt-1 line-clamp-2 text-[13px] leading-5 text-slate-600">
                                    {{ $opportunity->excerpt }}
                                </p>

                                <div class="mt-3 flex items-end justify-between gap-4 border-t border-brand-ink/10 pt-3">
                                    <div>
                                        <p class="text-[11px] font-black uppercase tracking-[0.11em] text-brand-blue/70">
                                            Batas Akhir
                                        </p>

                                        <p class="mt-1 text-[15px] font-extrabold text-brand-ink">
                                            {{ optional($opportunity->deadline)->translatedFormat('d M Y') ?: 'Tidak dibatasi' }}
                                        </p>
                                    </div>

                                    <a
                                        href="{{ $externalOpportunityUrl($opportunity) }}"
                                        @if ($isExternalOpportunityUrl($opportunity)) target="_blank" rel="noopener noreferrer" @endif
                                        class="inline-flex shrink-0 items-center justify-end gap-1.5 pb-0.5 text-right text-xs font-extrabold text-brand-ink transition hover:text-brand-navy"
                                    >
                                        {{ $opportunityButtonLabel($opportunity) }}
                                        <svg class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="mt-3 rounded-2xl border border-dashed border-brand-ink/15 bg-white p-5 text-center">
                        <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-paper text-brand-navy">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M10 6V5a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v1m-9 0h14v13a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                <path d="M9 10h6M9 14h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </div>

                        <h3 class="mt-3 text-sm font-extrabold text-brand-ink">
                            Opportunities segera hadir
                        </h3>

                        <p class="mt-1 text-xs leading-5 text-slate-600">
                            Peluang terbaru akan ditampilkan setelah tersedia.
                        </p>
                    </div>
                @endif

                <div class="mt-3">
                    <a
                        href="{{ route('opportunities.index') }}"
                        class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-brand-black px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-brand-navy"
                    >
                        Lihat Semua Opportunities
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </aside>
        </div>
    </div>
</section>
