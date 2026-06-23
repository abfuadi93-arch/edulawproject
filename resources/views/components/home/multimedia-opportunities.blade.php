@props([
    'featuredMultimedia' => null,
    'multimediaItems' => collect(),
    'opportunities' => collect(),
])

@php
    use Illuminate\Support\Str;

    $mainMultimedia = $featuredMultimedia ?: $multimediaItems->first();

    $sideMultimedia = $multimediaItems
        ->when($mainMultimedia, fn ($collection) => $collection->where('id', '!=', $mainMultimedia->id))
        ->take(3);

    $externalOpportunityUrl = function ($opportunity) {
        return $opportunity->application_link
            ?? $opportunity->external_url
            ?? $opportunity->url
            ?? url('/kontak');
    };

    $isExternalOpportunityUrl = function ($opportunity) {
        $url = $opportunity->application_link
            ?? $opportunity->external_url
            ?? $opportunity->url
            ?? null;

        return $url && Str::startsWith($url, ['http://', 'https://']);
    };

    $opportunityButtonLabel = function ($opportunity) {
        $url = $opportunity->application_link
            ?? $opportunity->external_url
            ?? $opportunity->url
            ?? null;

        return $url ? 'Buka Peluang' : 'Tanya Informasi';
    };
@endphp

<section class="bg-[#F9F5F6] py-4 lg:py-5">
    <div class="section-shell">
        <div class="grid gap-4 lg:grid-cols-[1.12fr_0.88fr] lg:items-start">
            {{-- Multimedia --}}
            <div>
                {{-- Header --}}
                <div>
                    <div class="max-w-full">
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-brand-navy">
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
                    <article class="group mt-3 overflow-hidden rounded-2xl bg-brand-navy shadow-sm ring-1 ring-brand-ink/10 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-brand-ink/10">
                        <a
                            href="{{ $mainMultimedia->media_url ?: route('multimedia.index') }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="block"
                        >
                            <div class="relative h-64 overflow-hidden sm:h-80">
                                <img
                                    src="{{ $mainMultimedia->thumbnail_url ?: 'https://images.unsplash.com/photo-1551818255-e6e10975bc17?auto=format&fit=crop&w=1200&q=85' }}"
                                    alt="{{ $mainMultimedia->title }}"
                                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                >

                                <div class="absolute inset-0 bg-linear-to-t from-brand-navy via-brand-navy/40 to-transparent"></div>
                                <div class="absolute inset-0 bg-linear-to-r from-brand-navy/40 via-transparent to-transparent"></div>

                                <div class="absolute left-5 top-5">
                                    <span class="inline-flex rounded-full bg-brand-amber px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-brand-black shadow-sm">
                                        {{ $mainMultimedia->display_type }}
                                    </span>
                                </div>

                                <div class="absolute right-5 top-5">
                                    <span class="inline-flex rounded-full bg-brand-black/70 px-3 py-1 text-xs font-bold text-white backdrop-blur">
                                        {{ $mainMultimedia->duration ?: '-' }}
                                    </span>
                                </div>

                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="flex h-16 w-16 items-center justify-center rounded-full bg-white/90 text-brand-black shadow-xl transition group-hover:scale-105 group-hover:bg-brand-amber">
                                        <svg class="ml-1 h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M8 5v14l11-7L8 5Z"/>
                                        </svg>
                                    </span>
                                </div>

                                <div class="absolute bottom-0 left-0 right-0 p-3 sm:p-4">
                                    <h3 class="max-w-2xl text-xl font-extrabold leading-tight tracking-normal text-white sm:text-2xl">
                                        {{ $mainMultimedia->title }}
                                    </h3>

                                    <p class="mt-2 max-w-xl text-[15px] leading-7 text-white/80">
                                        {{ $mainMultimedia->description }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    </article>
                @endif

                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                    @foreach ($sideMultimedia as $item)
                        <article class="group overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-brand-ink/10 transition duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-brand-ink/10">
                            <a
                                href="{{ $item->media_url ?: route('multimedia.index') }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="block"
                            >
                                <div class="relative aspect-video overflow-hidden bg-slate-100">
                                    <img
                                        src="{{ $item->thumbnail_url ?: 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=600&q=85' }}"
                                        alt="{{ $item->title }}"
                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                    >

                                    <div class="absolute inset-0 bg-linear-to-t from-brand-navy/70 via-brand-navy/10 to-transparent"></div>

                                    <div class="absolute left-3 top-3">
                                        <span class="rounded-full bg-white/90 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.12em] text-brand-ink shadow-sm">
                                            {{ $item->display_type }}
                                        </span>
                                    </div>

                                    <div class="absolute right-3 top-3">
                                        <span class="rounded-full bg-brand-black/70 px-2.5 py-1 text-[9px] font-bold text-white backdrop-blur">
                                            {{ $item->duration ?: '-' }}
                                        </span>
                                    </div>

                                    <div class="absolute bottom-3 left-3 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-brand-black shadow-sm transition group-hover:bg-brand-amber">
                                        <svg class="ml-0.5 h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M8 5v14l11-7L8 5Z"/>
                                        </svg>
                                    </div>
                                </div>

                                <div class="p-3">
                                    <h4 class="line-clamp-2 text-sm font-extrabold leading-snug text-brand-ink">
                                        {{ $item->title }}
                                    </h4>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>

            {{-- Opportunities --}}
            <aside class="rounded-2xl border border-brand-ink/10 bg-[#fbf7ef] p-4 shadow-sm shadow-brand-ink/5 lg:mt-13">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-brand-navy">
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
                                    <span class="inline-flex shrink-0 rounded-full bg-brand-amber-soft px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.12em] text-brand-ink">
                                        {{ $opportunity->display_type }}
                                    </span>

                                    <span class="rounded-full bg-brand-paper px-2.5 py-1 text-right text-[10px] font-bold uppercase tracking-[0.12em] text-brand-blue">
                                        {{ $opportunity->format ?: '-' }}
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
                                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-brand-blue/70">
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
