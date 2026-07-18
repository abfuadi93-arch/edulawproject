@props(['articles', 'excerpt', 'categoryName', 'publishedDate', 'authorName', 'archiveUrl'])

@if ($articles->isNotEmpty())
    @php($primary = $articles->first())
    @php($secondary = $articles->skip(1)->take(3))
    <section class="bg-[#f4f2ec] pt-12 pb-12 sm:pt-14 sm:pb-14 lg:pt-12 lg:pb-12">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-5">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-brand-coral">Pilihan Redaksi</p>
                    <h2 class="mt-2 font-display text-3xl font-bold text-brand-ink sm:text-4xl">Pilihan Editor</h2>
                </div>
                <a href="{{ $archiveUrl }}" class="shrink-0 text-sm font-bold text-brand-navy underline decoration-brand-amber decoration-2 underline-offset-4 hover:text-brand-coral focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">Lihat semua</a>
            </div>

            <div class="mt-8 grid gap-8 lg:grid-cols-[1.15fr_0.85fr] lg:gap-12">
                <article class="group min-w-0">
                    <a href="{{ route('insights.show', $primary->slug) }}" class="block rounded-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">
                        <div class="aspect-[16/9] overflow-hidden rounded-2xl bg-slate-100">
                            <img src="{{ $primary->cover_image_url }}" alt="{{ $primary->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.02] motion-reduce:transition-none">
                        </div>
                        <p class="mt-5 text-[11px] font-bold uppercase tracking-[0.12em] text-brand-coral">{{ $categoryName($primary) }}</p>
                        <h3 class="mt-2 max-w-3xl text-balance font-display text-2xl font-bold leading-tight text-brand-ink transition group-hover:text-brand-navy sm:text-3xl">{{ $primary->title }}</h3>
                        @if ($excerpt($primary, 150) !== '')
                            <p class="mt-3 line-clamp-2 max-w-2xl text-sm leading-6 text-slate-600">{{ $excerpt($primary, 150) }}</p>
                        @endif
                    </a>
                </article>

                <div class="divide-y divide-slate-300 border-y border-slate-300">
                    @foreach ($secondary as $article)
                        <article class="group py-5">
                            <a href="{{ route('insights.show', $article->slug) }}" class="grid grid-cols-[92px_minmax(0,1fr)] gap-4 rounded-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber sm:grid-cols-[116px_minmax(0,1fr)]">
                                <div class="aspect-[4/3] overflow-hidden rounded-lg bg-slate-100">
                                    <img src="{{ $article->cover_image_url }}" alt="{{ $article->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.025] motion-reduce:transition-none">
                                </div>
                                <div class="min-w-0 self-center">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-brand-coral">{{ $categoryName($article) }}</p>
                                    <h3 class="mt-1.5 line-clamp-2 text-base font-bold leading-snug text-brand-ink transition group-hover:text-brand-navy sm:text-lg">{{ $article->title }}</h3>
                                    <p class="mt-2 line-clamp-1 text-xs text-slate-500">{{ $publishedDate($article) }} · {{ $authorName($article) }}</p>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
