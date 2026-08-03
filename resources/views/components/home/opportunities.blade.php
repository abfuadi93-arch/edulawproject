@props(['opportunities' => collect()])

@php($opportunityCollection = collect($opportunities)->take(3)->values())

@if ($opportunityCollection->isNotEmpty())
    <section id="opportunities" class="home-section scroll-mt-24 bg-brand-paper" aria-labelledby="home-opportunities-title">
        <div class="section-shell">
            <div class="home-section-header">
                <div class="home-section-copy">
                    <p class="home-section-eyebrow">Pengembangan Kapasitas</p>
                    <h2 id="home-opportunities-title" class="home-section-title">Opportunities</h2>
                    <p class="home-section-description">Peluang aktif dengan tenggat terdekat untuk belajar, berkarya, dan berkolaborasi.</p>
                </div>
                <a href="{{ route('opportunities.index') }}" class="section-link">
                    Lihat Semua Peluang <span aria-hidden="true">→</span>
                </a>
            </div>

            <div @class([
                'mt-7 grid gap-6',
                'max-w-4xl' => $opportunityCollection->count() === 1,
                'md:grid-cols-2 lg:grid-cols-3' => $opportunityCollection->count() > 1,
            ])>
                @foreach ($opportunityCollection as $opportunity)
                    @php($isSingle = $opportunityCollection->count() === 1)
                    <article @class([
                        'home-card home-card-interactive group grid h-full',
                        'sm:grid-cols-[15rem_minmax(0,1fr)]' => $isSingle,
                    ]) data-home-opportunity>
                        <div @class([
                            'relative overflow-hidden bg-linear-to-br from-brand-navy via-brand-blue to-brand-teal',
                            'aspect-video sm:aspect-auto sm:min-h-56' => $isSingle,
                            'aspect-video' => ! $isSingle,
                        ])>
                            <div class="absolute inset-0 flex items-center justify-center text-white/80" aria-hidden="true" data-home-opportunity-fallback>
                                <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none">
                                    <path d="M8 3v3m8-3v3M5 9h14M6 5h12a2 2 0 0 1 2 2v12H4V7a2 2 0 0 1 2-2Zm3 8h6m-6 3h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>

                            @if ($opportunity->poster_url)
                                <img
                                    src="{{ $opportunity->poster_url }}"
                                    alt="Poster {{ $opportunity->title }}"
                                    width="640"
                                    height="360"
                                    loading="lazy"
                                    decoding="async"
                                    class="relative h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
                                    onerror="this.remove()"
                                >
                            @endif
                        </div>

                        <div class="flex flex-1 flex-col p-5">
                            <span class="edulaw-badge edulaw-badge-amber">{{ $opportunity->display_type }}</span>
                            <h3 class="mt-3 line-clamp-2 text-lg font-extrabold leading-snug text-brand-ink">{{ $opportunity->title }}</h3>
                            @if ($opportunity->excerpt)
                                <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">{{ $opportunity->excerpt }}</p>
                            @endif
                            <p class="home-meta mt-auto pt-4">
                                @if ($opportunity->deadline?->isPast())
                                    Tenggat telah lewat
                                @elseif ($opportunity->deadline)
                                    Tenggat {{ $opportunity->deadline->translatedFormat('d M Y') }}
                                @else
                                    Tenggat fleksibel
                                @endif
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
        </div>
    </section>
@endif
