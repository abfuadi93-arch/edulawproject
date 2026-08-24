@props([
    'popularArticles' => [],
    'popularHasViews' => false,
    'contributors' => [],
    'categoryName',
    'publishedDate',
    'archiveUrl',
])

@php
    $popularArticles = collect($popularArticles ?? [])->take(5)->values();
    $contributors = collect($contributors ?? [])->take(5)->values();
    $leadPopular = $popularArticles->first();
    $leadContributor = $contributors->first();
    $hasImage = fn ($article): bool => filled($article?->cover_image) && edulaw_file_exists($article->cover_image);

@endphp

@if (($popularHasViews && $popularArticles->isNotEmpty()) || $contributors->isNotEmpty())
    <section class="bg-white py-9 sm:py-10 lg:py-11" aria-labelledby="editorial-pulse-heading">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-brand-coral"><span class="mr-1 text-brand-amber" aria-hidden="true">●</span> Editorial Pulse</p>
                <h2 id="editorial-pulse-heading" class="mt-1 font-display text-2xl font-black text-brand-navy sm:text-3xl">Paling Dibaca &amp; Penulis Produktif</h2>
                <p class="mt-1.5 text-base leading-7 text-slate-600">Tulisan yang paling banyak dibaca dan kontributor paling aktif di kanal Editorial Edulaw.</p>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1.65fr)_minmax(300px,0.85fr)]">
                @if ($popularHasViews && $popularArticles->isNotEmpty())
                    <section class="rounded-[14px] bg-[#f7f8fa] p-4 sm:p-5" aria-labelledby="popular-editorial-heading">
                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.13em] text-slate-500">Tulisan Terpopuler</p>
                                <h3 id="popular-editorial-heading" class="mt-1 font-display text-xl font-black text-brand-navy">Paling Banyak Dibaca</h3>
                            </div>
                            <a href="{{ $archiveUrl }}" class="text-xs font-extrabold text-brand-navy">Lihat semua <span aria-hidden="true">→</span></a>
                        </div>

                        <div class="mt-4 grid gap-4 sm:grid-cols-[minmax(0,1.15fr)_minmax(240px,0.85fr)]">
                            <a href="{{ route('insights.show', $leadPopular->slug) }}" class="group relative flex min-h-[250px] overflow-hidden rounded-xl bg-brand-navy focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-brand-amber" data-most-read-item>
                                @if ($hasImage($leadPopular))
                                    <img src="{{ $leadPopular->cover_image_url }}" alt="{{ $leadPopular->title }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.025] motion-reduce:transition-none">
                                @else
                                    <div class="absolute inset-0 bg-linear-to-br from-brand-navy via-[#244972] to-[#0f766e]"></div>
                                @endif
                                <div class="absolute inset-0 bg-linear-to-t from-black/90 via-black/25 to-transparent"></div>
                                <span class="absolute left-3 top-3 grid h-9 w-9 place-items-center rounded-lg bg-brand-amber text-sm font-black text-brand-navy">01</span>
                                <span class="relative mt-auto p-4 text-white sm:p-5">
                                    <span class="text-[11px] font-extrabold uppercase tracking-[0.1em] text-brand-amber">{{ $categoryName($leadPopular) }}</span>
                                    <span class="mt-1.5 block line-clamp-3 text-lg font-black leading-snug">{{ $leadPopular->title }}</span>
                                    <span class="mt-2 block text-xs font-semibold text-white/75">{{ $publishedDate($leadPopular) }} · {{ number_format((int) $leadPopular->getAttribute('visit_count'), 0, ',', '.') }} kali dibaca</span>
                                </span>
                            </a>

                            <ol class="divide-y divide-slate-200">
                                @foreach ($popularArticles->skip(1)->values() as $index => $article)
                                    <li data-most-read-item>
                                        <a href="{{ route('insights.show', $article->slug) }}" class="group grid grid-cols-[34px_minmax(0,1fr)] gap-3 py-3 first:pt-0 last:pb-0 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                                            <span class="font-display text-xl font-black tabular-nums text-brand-navy/25">{{ str_pad((string) ($index + 2), 2, '0', STR_PAD_LEFT) }}</span>
                                            <span class="min-w-0">
                                                <span class="block text-[11px] font-extrabold uppercase tracking-[0.09em] text-brand-coral">{{ $categoryName($article) }}</span>
                                                <span class="mt-1 block line-clamp-2 text-sm font-extrabold leading-snug text-brand-ink group-hover:text-brand-navy">{{ $article->title }}</span>
                                                <span class="mt-1 block text-[11px] font-medium text-slate-500">{{ number_format((int) $article->getAttribute('visit_count'), 0, ',', '.') }} kali dibaca</span>
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    </section>
                @endif

                @if ($contributors->isNotEmpty())
                    <section class="rounded-[14px] bg-[#f7f8fa] p-4 sm:p-5" aria-labelledby="productive-heading">
                        <div class="flex items-end justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.13em] text-slate-500">Kontributor Editorial</p>
                                <h3 id="productive-heading" class="mt-1 font-display text-xl font-black text-brand-navy">Penulis Terproduktif</h3>
                            </div>
                            <a href="{{ route('about') }}#tim" class="text-right text-[11px] font-extrabold text-brand-navy">Lihat Semua Kontributor <span aria-hidden="true">→</span></a>
                        </div>

                        <a href="{{ route('profiles.show', $leadContributor->slug) }}" class="mt-4 flex items-center gap-3 rounded-xl bg-[#eef4f8] p-3 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber" data-editorial-contributor="{{ $leadContributor->id }}">
                            <span class="relative grid h-14 w-14 shrink-0 place-items-center overflow-hidden rounded-full bg-brand-navy text-sm font-black text-white">
                                <span aria-hidden="true">{{ $leadContributor->initials }}</span>
                                @if ($leadContributor->photo_url)
                                    <img src="{{ $leadContributor->photo_url }}" alt="Foto profil {{ $leadContributor->name }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover" onerror="this.remove()">
                                @endif
                            </span>
                            <span class="min-w-0">
                                <span class="block text-[11px] font-extrabold uppercase tracking-[0.09em] text-brand-coral">#1 Penulis Terproduktif</span>
                                <strong class="mt-1 block truncate text-base text-brand-ink">{{ $leadContributor->name }}</strong>
                                <span class="mt-1 block text-[11px] font-bold text-brand-navy">{{ $leadContributor->published_insights_count }} tulisan terbit</span>
                            </span>
                        </a>

                        <ol class="mt-3 divide-y divide-slate-200">
                            @foreach ($contributors->skip(1)->values() as $index => $author)
                                <li>
                                    <a href="{{ route('profiles.show', $author->slug) }}" class="grid grid-cols-[24px_34px_minmax(0,1fr)_auto] items-center gap-2 py-2 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber" data-editorial-contributor="{{ $author->id }}">
                                        <span class="text-[11px] font-bold tabular-nums text-slate-500">{{ str_pad((string) ($index + 2), 2, '0', STR_PAD_LEFT) }}</span>
                                        <span class="relative grid h-8 w-8 place-items-center overflow-hidden rounded-full bg-brand-navy text-[11px] font-bold text-white">
                                            <span aria-hidden="true">{{ $author->initials }}</span>
                                            @if ($author->photo_url)
                                                <img src="{{ $author->photo_url }}" alt="Foto profil {{ $author->name }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover" onerror="this.remove()">
                                            @endif
                                        </span>
                                        <span class="min-w-0">
                                            <strong class="block truncate text-xs text-brand-ink">{{ $author->name }}</strong>
                                        </span>
                                        <span class="text-[11px] font-bold text-slate-500">{{ $author->published_insights_count }} tulisan</span>
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                @endif
            </div>
        </div>
    </section>
@endif
