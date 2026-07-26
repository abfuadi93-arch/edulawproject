@props(['opportunities' => collect()])

<section id="opportunities" class="home-section scroll-mt-24 bg-brand-paper" aria-labelledby="home-opportunities-title">
    <div class="section-shell">
        <div class="home-section-header">
            <div class="home-section-copy">
                <p class="home-section-eyebrow">Pengembangan Kapasitas</p>
                <h2 id="home-opportunities-title" class="home-section-title">Opportunities</h2>
                <p class="home-section-description">Peluang aktif dengan tenggat terdekat untuk belajar, berkarya, dan berkolaborasi.</p>
            </div>
            <a href="{{ route('opportunities.index') }}" class="section-link">
                Lihat Semua Peluang
                <span aria-hidden="true">→</span>
            </a>
        </div>

        @if ($opportunities->isNotEmpty())
            <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($opportunities as $opportunity)
                    <article class="home-card home-card-interactive flex h-full flex-col" data-home-opportunity>
                        <div class="relative aspect-video overflow-hidden bg-linear-to-br from-brand-navy via-brand-blue to-brand-teal">
                            @if ($opportunity->poster_url)
                                <img
                                    src="{{ $opportunity->poster_url }}"
                                    alt="Poster {{ $opportunity->title }}"
                                    width="640"
                                    height="360"
                                    loading="lazy"
                                    decoding="async"
                                    class="h-full w-full object-cover"
                                >
                            @else
                                <div class="flex h-full items-center justify-center text-white/85" aria-hidden="true" data-home-opportunity-fallback>
                                    <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none">
                                        <path d="M8 3v3m8-3v3M5 9h14M6 5h12a2 2 0 0 1 2 2v12H4V7a2 2 0 0 1 2-2Zm3 8h6m-6 3h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-1 flex-col p-5">
                            <span class="edulaw-badge edulaw-badge-amber">{{ $opportunity->display_type }}</span>
                            <h3 class="mt-3 text-base font-extrabold leading-snug text-brand-ink">{{ $opportunity->title }}</h3>
                            @if ($opportunity->excerpt)
                                <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">{{ $opportunity->excerpt }}</p>
                            @endif
                            <p class="home-meta mt-auto pt-4">
                                {{ $opportunity->deadline ? 'Tenggat '.$opportunity->deadline->translatedFormat('d M Y') : 'Tenggat fleksibel' }}
                            </p>
                            <a
                                href="{{ $opportunity->application_link }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="home-card-link mt-4 inline-flex items-center gap-2 text-sm font-extrabold text-brand-navy"
                                aria-label="Buka peluang {{ $opportunity->title }} di situs eksternal"
                            >
                                Lihat Peluang <span aria-hidden="true">↗</span>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="home-empty-state mt-7">
                <p class="text-sm font-bold text-brand-ink">Belum ada peluang aktif saat ini.</p>
                <p class="mt-1 text-sm text-slate-600">Silakan kembali lagi untuk melihat kesempatan terbaru dari Edulaw.</p>
            </div>
        @endif
    </div>
</section>
