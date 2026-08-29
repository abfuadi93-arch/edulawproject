@props(['authors' => collect()])

<section id="kontributor" class="home-surface-paper scroll-mt-20 py-7 sm:py-8 lg:py-9" aria-labelledby="home-authors-title">
    <div class="section-shell">
        <div class="flex items-end justify-between gap-5">
            <div>
                <p class="home-section-eyebrow text-[#e57b66]">Kontributor</p>
                <h2 id="home-authors-title" class="home-section-title">Penulis dan Peneliti Edulaw</h2>
            </div>
            <a href="{{ route('about') }}#contributors" class="home-section-link">Tentang Tim →</a>
        </div>

        @if (collect($authors)->isNotEmpty())
            <div class="mt-6 grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                @foreach (collect($authors) as $author)
                    @php
                        $insightCount = (int) ($author->published_insights_count ?? 0);
                        $publicationCount = (int) ($author->published_publications_count ?? 0);
                    @endphp
                    <article data-home-author class="group h-full rounded-xl border border-slate-200 bg-white transition hover:-translate-y-0.5 hover:border-brand-navy/20 hover:shadow-sm">
                        <a href="{{ route('profiles.show', $author->slug) }}" aria-label="Lihat profil {{ $author->name }}" class="flex h-full flex-col p-4 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                            <div class="flex items-center gap-4">
                                @if ($author->photo_url)
                                    <img src="{{ $author->photo_url }}" alt="Foto profil {{ $author->name }}" width="40" height="40" loading="lazy" decoding="async" class="size-10 shrink-0 rounded-full object-cover ring-1 ring-slate-200">
                                @else
                                    <span class="grid size-10 shrink-0 place-items-center rounded-full bg-brand-navy text-[10px] font-black text-white">{{ $author->initials }}</span>
                                @endif
                                <div class="min-w-0">
                                    <h3 class="line-clamp-2 text-xs font-black leading-snug text-brand-navy transition group-hover:text-brand-teal">{{ $author->name }}</h3>
                                    @if ($insightCount > 0 || $publicationCount > 0)
                                        <p class="mt-1 text-[10px] font-semibold text-slate-500">{{ $insightCount }} tulisan · {{ $publicationCount }} publikasi</p>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        @else
            <div class="home-empty-state mt-7"><p class="text-sm leading-6 text-slate-600">Profil kontributor sedang disiapkan.</p></div>
        @endif
    </div>
</section>
