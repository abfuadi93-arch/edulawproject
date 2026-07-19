@props(['article', 'excerpt', 'categoryName', 'publishedDate', 'readingTime', 'authorName'])

@if ($article)
    @php
        $coverImage = $article->cover_image_url;
        $metadata = collect([
            ['label' => $categoryName($article), 'type' => 'category'],
            filled($article->published_at) ? ['label' => $publishedDate($article), 'type' => 'date'] : null,
            ['label' => $readingTime($article), 'type' => 'text'],
            ['label' => $authorName($article), 'type' => 'text'],
        ])
            ->filter(fn ($item): bool => filled($item['label'] ?? null))
            ->values();
    @endphp

    <section class="bg-[#fbfaf7] pt-10 pb-12 sm:pt-14 sm:pb-14 lg:pt-16 lg:pb-16" data-featured-editorial="{{ $article->id }}">
        <div class="mx-auto grid max-w-7xl gap-7 px-5 sm:px-6 lg:grid-cols-12 lg:items-start lg:gap-12 lg:px-8 xl:gap-14">
            <a href="{{ route('insights.show', $article->slug) }}" class="group relative h-[220px] overflow-hidden rounded-2xl border border-slate-200 bg-brand-navy shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-navy sm:h-[280px] lg:col-span-7 lg:h-[340px] xl:h-[360px]">
                @if ($coverImage)
                    <img src="{{ $coverImage }}" alt="{{ $article->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.02] motion-reduce:transition-none" fetchpriority="high">
                @else
                    <div class="absolute inset-0 bg-linear-to-br from-brand-navy via-[#173968] to-[#0f766e]"></div>
                    <div class="absolute inset-0 bg-linear-to-t from-black/30 via-transparent to-white/5"></div>
                    <span class="absolute bottom-5 left-5 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] text-white/82 backdrop-blur">
                        Editorial Edulaw
                    </span>
                @endif
            </a>

            <article class="min-w-0 lg:col-span-5 lg:pt-1">
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-semibold text-slate-500 sm:text-[13px]">
                    @foreach ($metadata as $meta)
                        @if (! $loop->first)
                            <span class="text-slate-300" aria-hidden="true">•</span>
                        @endif

                        @if ($meta['type'] === 'category')
                            <span class="font-bold uppercase tracking-[0.1em] text-brand-coral">{{ $meta['label'] }}</span>
                        @elseif ($meta['type'] === 'date')
                            <time datetime="{{ optional($article->published_at)->toDateString() }}">{{ $meta['label'] }}</time>
                        @else
                            <span>{{ $meta['label'] }}</span>
                        @endif
                    @endforeach
                </div>
                <h2 class="mt-3 line-clamp-3 max-w-[20ch] text-balance font-display text-[clamp(2rem,3vw,3.25rem)] font-bold leading-[1.06] tracking-normal text-brand-ink">
                    <a href="{{ route('insights.show', $article->slug) }}" class="rounded-sm underline-offset-4 transition hover:text-brand-navy hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">
                        {{ $article->title }}
                    </a>
                </h2>
                @if ($excerpt($article, 170) !== '')
                    <p class="mt-4 line-clamp-2 max-w-xl text-pretty text-[15px] leading-7 text-slate-600">{{ $excerpt($article, 170) }}</p>
                @endif
                <a href="{{ route('insights.show', $article->slug) }}" class="mt-5 inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-full bg-brand-navy px-5 text-sm font-bold text-white shadow-sm transition hover:bg-brand-ink focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber sm:w-fit">
                    Baca Selengkapnya <span aria-hidden="true">→</span>
                </a>
            </article>
        </div>
    </section>
@else
    <section class="bg-[#fbfaf7] pt-10 pb-12 sm:pt-14 sm:pb-14 lg:pt-16 lg:pb-16" data-featured-editorial-empty>
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 px-5 py-6 text-sm font-semibold text-slate-500">
                Editorial utama sedang disiapkan.
            </div>
        </div>
    </section>
@endif
