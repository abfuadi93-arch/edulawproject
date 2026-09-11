@props(['publications' => collect()])

@php
    $publicationCollection = collect($publications)->take(3)->values();
@endphp

<section id="riset-publikasi" class="scroll-mt-20 bg-white py-6 sm:py-8" aria-labelledby="home-publications-title">
    <div class="section-shell">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-3xl">
                <p class="home-section-eyebrow">Pengetahuan Terbitan</p>
                <h2 id="home-publications-title" class="home-section-title">Riset &amp; Publikasi Pilihan</h2>
                <p class="home-section-description">Repositori kajian, policy brief, naskah akademik, dan buku digital.</p>
            </div>
            <a href="{{ route('publications.index') }}" class="home-section-link hidden sm:inline-flex">Lihat Semua Publikasi →</a>
        </div>

        @if ($publicationCollection->isNotEmpty())
            <div class="mt-7 grid gap-x-6 gap-y-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($publicationCollection as $publication)
                    @php
                        $typeName = $publication->type?->name ?? 'Publikasi';
                        $publicationDate = $publication->publication_date_display;
                        $authorNames = $publication->authors
                            ->pluck('name')
                            ->map(fn ($name): string => trim((string) $name))
                            ->filter()
                            ->take(2)
                            ->join(', ');
                    @endphp

                    <article data-home-publication class="group min-w-0 border-y border-slate-200 transition duration-200 hover:border-slate-300 hover:bg-slate-50/70">
                        <a href="{{ route('publications.show', $publication->slug) }}" aria-label="Lihat publikasi: {{ $publication->title }}" class="flex h-full gap-4 py-4 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                            <div class="relative w-[68px] shrink-0 self-stretch overflow-hidden rounded-md sm:w-[72px]">
                                <x-home.media-fallback kind="publication" />
                                @if ($publication->cover_image_url)
                                    <x-responsive-image :src="$publication->cover_image_url" alt="Sampul {{ $publication->title }}" :widths="[96, 160, 240]" sizes="72px" width="160" height="214" class="absolute inset-0 size-full object-cover" onerror="this.remove()" />
                                @endif
                            </div>

                            <div class="flex min-w-0 flex-1 flex-col">
                                <p class="home-card-kicker">{{ $typeName }}</p>
                                <h3 class="mt-2 line-clamp-2 text-[15px] font-bold leading-[1.4] text-brand-navy transition group-hover:text-brand-teal">{{ $publication->title }}</h3>
                                <div class="mt-3 flex items-start justify-between gap-3 text-xs font-semibold leading-5 text-slate-500">
                                    @if ($authorNames !== '')<span class="min-w-0 flex-1 truncate text-left">{{ $authorNames }}</span>@endif
                                    @if (filled($publicationDate) && $publicationDate !== 'Belum diketahui' && $publicationDate !== '-')<span class="shrink-0 text-right">{{ $publicationDate }}</span>@endif
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        @else
            <div class="home-empty-state mt-8">
                <p class="text-sm leading-6 text-slate-600">Publikasi sedang disiapkan.</p>
                <a href="{{ route('publications.index') }}" class="home-section-link mt-3">Buka Repositori →</a>
            </div>
        @endif

        <a href="{{ route('publications.index') }}" class="home-section-link mt-6 sm:hidden">Lihat Semua Publikasi →</a>
    </div>
</section>
