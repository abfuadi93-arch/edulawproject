@props(['articles', 'excerpt', 'categoryName', 'publishedDate', 'authorName', 'archiveUrl'])

@if ($articles->isNotEmpty())
    @php
        $primary = $articles->first();
        $secondary = $articles->skip(1)->take(3);
        $metaItems = fn ($article) => collect([
            $categoryName($article),
            $publishedDate($article),
            $authorName($article),
        ])->filter(fn ($item) => filled($item))->values();
    @endphp

    <section class="bg-[#f4f2ec] pt-12 pb-12 sm:pt-14 sm:pb-14 lg:pt-12 lg:pb-12">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-5">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-brand-coral">Pilihan Redaksi</p>
                    <h2 class="mt-2 font-display text-3xl font-bold text-brand-ink sm:text-4xl">Pilihan Editor</h2>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-slate-600">Kurasi redaksi untuk isu hukum yang penting dibaca.</p>
                </div>
                <a href="{{ $archiveUrl }}" class="shrink-0 text-sm font-bold text-brand-navy underline decoration-brand-amber decoration-2 underline-offset-4 hover:text-brand-coral focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">Lihat semua</a>
            </div>

            <div class="mt-8 grid gap-8 lg:grid-cols-[1.15fr_0.85fr] lg:gap-12">
                <article class="group min-w-0">
                    <a href="{{ route('insights.show', $primary->slug) }}" class="block rounded-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">
                        <div class="relative aspect-[16/9] overflow-hidden rounded-2xl bg-slate-100">
                            @if (filled($primary->cover_image))
                                <img src="{{ $primary->cover_image_url }}" alt="{{ $primary->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.02] motion-reduce:transition-none">
                            @else
                                <div class="absolute inset-0 bg-linear-to-br from-brand-navy via-[#1f456d] to-[#0f172a]"></div>
                                <div class="absolute inset-0 bg-[radial-gradient(circle_at_22%_20%,rgba(255,255,255,0.18),transparent_28%),linear-gradient(135deg,rgba(255,255,255,0.08)_0,transparent_44%)]"></div>
                            @endif
                            <span class="absolute left-4 top-4 rounded-full bg-brand-amber px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-brand-ink shadow-sm">Pilihan Editor</span>
                        </div>
                        <div class="mt-5 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] font-semibold text-slate-500">
                            @foreach ($metaItems($primary) as $metaIndex => $meta)
                                @if ($metaIndex > 0)
                                    <span class="text-slate-300" aria-hidden="true">•</span>
                                @endif
                                <span @class([
                                    'font-bold uppercase tracking-[0.1em] text-brand-coral' => $metaIndex === 0,
                                ])>{{ $meta }}</span>
                            @endforeach
                        </div>
                        <h3 class="mt-2 line-clamp-3 max-w-3xl text-balance font-display text-2xl font-bold leading-tight text-brand-ink transition group-hover:text-brand-navy sm:text-3xl">{{ $primary->title }}</h3>
                        @if ($excerpt($primary, 150) !== '')
                            <p class="mt-3 line-clamp-2 max-w-2xl text-sm leading-6 text-slate-600">{{ $excerpt($primary, 150) }}</p>
                        @endif
                    </a>
                </article>

                <div class="divide-y divide-slate-300 border-y border-slate-300">
                    @foreach ($secondary as $article)
                        <article class="group py-5">
                            <a href="{{ route('insights.show', $article->slug) }}" class="grid grid-cols-[92px_minmax(0,1fr)] gap-4 rounded-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber sm:grid-cols-[116px_minmax(0,1fr)]">
                                <div class="relative aspect-[4/3] overflow-hidden rounded-lg bg-slate-100">
                                    @if (filled($article->cover_image))
                                        <img src="{{ $article->cover_image_url }}" alt="{{ $article->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.025] motion-reduce:transition-none">
                                    @else
                                        <div class="absolute inset-0 bg-linear-to-br from-brand-navy via-[#244972] to-[#0f172a]"></div>
                                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_18%,rgba(255,255,255,0.16),transparent_28%)]"></div>
                                    @endif
                                </div>
                                <div class="min-w-0 self-center">
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <span class="rounded-full bg-white px-2.5 py-1 text-[9px] font-bold uppercase tracking-[0.14em] text-brand-navy ring-1 ring-slate-200">Kurasi Redaksi</span>
                                        <span class="text-[10px] font-bold uppercase tracking-[0.1em] text-brand-coral">{{ $categoryName($article) }}</span>
                                    </div>
                                    <h3 class="mt-1.5 line-clamp-2 text-base font-bold leading-snug text-brand-ink transition group-hover:text-brand-navy sm:text-lg">{{ $article->title }}</h3>
                                    <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                                        @foreach ($metaItems($article)->skip(1)->values() as $metaIndex => $meta)
                                            @if ($metaIndex > 0)
                                                <span class="text-slate-300" aria-hidden="true">•</span>
                                            @endif
                                            <span>{{ $meta }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
