@props(['publications' => collect()])

@php
    $publicationCollection = collect($publications)->take(4)->values();
    $hasPublicationIndex = \Illuminate\Support\Facades\Route::has('publications.index');
    $hasPublicationShow = \Illuminate\Support\Facades\Route::has('publications.show');
@endphp

<section id="riset-publikasi" class="home-section scroll-mt-24 bg-white" aria-labelledby="home-publications-title">
    <div class="section-shell">
        <div class="home-section-header">
            <div class="home-section-copy">
                <p class="home-section-eyebrow text-[#8A6B2F]">Publikasi Edulaw</p>
                <h2 id="home-publications-title" class="home-section-title">Riset &amp; Publikasi Pilihan</h2>
                <p class="home-section-description">Repositori kajian, policy brief, naskah akademik, dan buku digital.</p>
            </div>

            @if ($hasPublicationIndex)
                <a href="{{ route('publications.index') }}" class="section-link w-fit shrink-0">
                    Lihat Semua Publikasi
                    <span aria-hidden="true">→</span>
                </a>
            @endif
        </div>

        @if ($hasPublicationShow && $publicationCollection->isNotEmpty())
            <div @class([
                'mt-7 grid auto-rows-fr gap-6',
                'md:grid-cols-2 lg:grid-cols-3' => $publicationCollection->count() <= 3,
                'md:grid-cols-2 xl:grid-cols-4' => $publicationCollection->count() >= 4,
            ])>
                @foreach ($publicationCollection as $publication)
                    @php
                        $typeName = $publication->type?->name ?: 'Dokumen';
                        $year = optional($publication->published_at)->format('Y');
                        $documentMeta = filled($publication->page_count)
                            ? number_format((int) $publication->page_count, 0, ',', '.').' halaman'
                            : 'Dokumen digital';
                    @endphp

                    <article data-home-publication class="home-card home-card-interactive group flex h-full flex-col">
                        <a href="{{ route('publications.show', $publication->slug) }}" class="home-card-link flex h-full flex-col">
                            <div class="relative aspect-[16/10] overflow-hidden bg-linear-to-br from-[#edf3f7] via-[#f7f3e9] to-[#dceeea]">
                                @if ($publication->cover_image_url)
                                    <img
                                        src="{{ $publication->cover_image_url }}"
                                        alt="Sampul {{ $publication->title }}"
                                        width="800"
                                        height="500"
                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
                                        loading="lazy"
                                        decoding="async"
                                        onerror="this.remove()"
                                    >
                                @else
                                    <div class="flex h-full items-center justify-center" data-publication-fallback>
                                        <div class="grid h-20 w-16 place-items-center rounded-lg border border-brand-navy/15 bg-white text-brand-navy shadow-md shadow-brand-navy/10">
                                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M7 3h7l4 4v14H7V3Zm7 0v5h4M10 12h5m-5 4h5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                    </div>
                                @endif

                                <span class="absolute left-4 top-4 inline-flex rounded-full bg-white/92 px-3 py-1 text-[10px] font-black uppercase tracking-[0.1em] text-brand-navy shadow-sm backdrop-blur">
                                    {{ $typeName }}
                                </span>
                            </div>

                            <div class="flex flex-1 flex-col p-5">
                                <h3 class="line-clamp-2 text-lg font-black leading-snug text-brand-ink transition group-hover:text-brand-navy">
                                    {{ $publication->title }}
                                </h3>

                                <div class="home-meta mt-3 flex flex-wrap items-center gap-2">
                                    @if ($year)
                                        <span>{{ $year }}</span>
                                        <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                    @endif
                                    <span>{{ $documentMeta }}</span>
                                </div>

                                <span class="mt-auto inline-flex items-center gap-2 pt-5 text-sm font-extrabold text-brand-navy">
                                    Lihat Publikasi <span aria-hidden="true">→</span>
                                </span>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        @else
            <div class="home-empty-state mt-6 py-3.5">
                <p class="text-sm leading-6 text-slate-600">Publikasi sedang disiapkan.</p>
            </div>
        @endif
    </div>
</section>
