@props(['article', 'excerpt', 'categoryName', 'publishedDate', 'readingTime', 'authorName'])

@if ($article)
    @php
        $coverImage = $article->cover_image_url ?: asset('images/hero/hero-edulaw.jpg');
    @endphp

    <section class="bg-[#fbfaf7] pt-10 pb-12 sm:pt-14 sm:pb-14 lg:pt-16 lg:pb-16" data-featured-editorial="{{ $article->id }}">
        <div class="mx-auto grid max-w-7xl gap-7 px-5 sm:px-6 lg:grid-cols-12 lg:items-start lg:gap-12 lg:px-8 xl:gap-14">
            <a href="{{ route('insights.show', $article->slug) }}" class="group relative aspect-[16/10] overflow-hidden rounded-xl bg-slate-100 shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-navy lg:col-span-7">
                <img src="{{ $coverImage }}" alt="{{ $article->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.02] motion-reduce:transition-none" fetchpriority="high">
            </a>

            <article class="min-w-0 lg:col-span-5 lg:pt-1">
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[13px] font-semibold text-slate-500">
                    <span class="font-bold uppercase tracking-[0.1em] text-brand-coral">{{ $categoryName($article) }}</span>
                    <span class="text-slate-300" aria-hidden="true">•</span>
                    <time class="text-slate-500" datetime="{{ optional($article->published_at)->toDateString() }}">{{ $publishedDate($article) }}</time>
                </div>
                <h2 class="mt-4 max-w-[19ch] text-balance font-display text-[clamp(2.25rem,3.25vw,3.75rem)] font-bold leading-[1.03] tracking-normal text-brand-ink">
                    <a href="{{ route('insights.show', $article->slug) }}" class="rounded-sm underline-offset-4 transition hover:text-brand-navy hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">
                        {{ $article->title }}
                    </a>
                </h2>
                @if ($excerpt($article, 190) !== '')
                    <p class="mt-5 line-clamp-3 max-w-xl text-pretty text-[15px] leading-7 text-slate-600">{{ $excerpt($article, 190) }}</p>
                @endif
                <div class="mt-6 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm font-medium text-slate-500">
                    <span>{{ $authorName($article) }}</span>
                    <span class="text-slate-300" aria-hidden="true">•</span>
                    <span>{{ $readingTime($article) }}</span>
                </div>
                <a href="{{ route('insights.show', $article->slug) }}" class="mt-6 inline-flex min-h-10 items-center gap-2 border-b-2 border-brand-amber text-sm font-bold text-brand-navy transition hover:text-brand-coral focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">
                    Baca selengkapnya <span aria-hidden="true">→</span>
                </a>
            </article>
        </div>
    </section>
@endif
