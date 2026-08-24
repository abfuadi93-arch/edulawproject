@props(['articles', 'categoryName', 'publishedDate', 'readingTime', 'authorName', 'excerpt'])

@php
    $primary = collect($articles ?? [])->first();
    $hasImage = filled($primary?->cover_image) && edulaw_file_exists($primary?->cover_image);
@endphp

@if ($primary)
    <section class="bg-white py-9 sm:py-10 lg:py-11" aria-labelledby="featured-editorial-heading">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <p class="mb-4 text-[11px] font-extrabold uppercase tracking-[0.16em] text-brand-navy"><span class="mr-1 text-brand-amber" aria-hidden="true">★</span> Editorial Utama</p>

            <article class="grid overflow-hidden rounded-[14px] bg-[#f7f8fa] lg:grid-cols-[minmax(0,0.92fr)_minmax(0,1.08fr)]" data-featured-editorial="{{ $primary->id }}">
                <a href="{{ route('insights.show', $primary->slug) }}" class="group relative block min-h-[240px] overflow-hidden bg-brand-navy focus-visible:outline-2 focus-visible:outline-offset-[-3px] focus-visible:outline-brand-amber sm:min-h-[330px] lg:min-h-[365px]">
                    @if ($hasImage)
                        <img src="{{ $primary->cover_image_url }}" alt="{{ $primary->title }}" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.02] motion-reduce:transition-none" fetchpriority="high">
                    @else
                        <div class="absolute inset-0 bg-linear-to-br from-brand-navy via-[#234a70] to-[#0f766e]"></div>
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.14),transparent_30%)]"></div>
                    @endif
                </a>

                <div class="flex min-w-0 flex-col justify-center px-5 py-7 sm:px-8 lg:px-10 lg:py-8">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full bg-brand-navy/7 px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-[0.1em] text-brand-navy">{{ $categoryName($primary) }}</span>
                        @if ($primary->editor_pick)
                            <span class="inline-flex rounded-full bg-[#dff4ec] px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-[0.1em] text-[#14705f]">Pilihan Editor</span>
                        @endif
                    </div>
                    <h2 id="featured-editorial-heading" class="mt-3 text-balance font-display text-2xl font-black leading-[1.1] text-brand-navy sm:text-3xl lg:text-[2.15rem]">{{ $primary->title }}</h2>
                    @if ($excerpt($primary, 240) !== '')
                        <p class="mt-3 line-clamp-3 text-base leading-7 text-slate-600">{{ $excerpt($primary, 240) }}</p>
                    @endif
                    <div class="mt-4 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-semibold text-slate-500">
                        <time datetime="{{ optional($primary->published_at)->toDateString() }}">{{ $publishedDate($primary) }}</time>
                        <span aria-hidden="true">·</span><span>{{ $readingTime($primary) }}</span>
                        @if (filled($authorName($primary)))
                            <span aria-hidden="true">·</span><span>{{ $authorName($primary) }}</span>
                        @endif
                    </div>
                    <a href="{{ route('insights.show', $primary->slug) }}" class="mt-5 inline-flex min-h-10 w-fit items-center justify-center rounded-lg bg-brand-navy px-4 text-sm font-extrabold text-white transition hover:bg-brand-ink focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-brand-amber">Baca Editorial <span class="ml-2" aria-hidden="true">→</span></a>
                </div>
            </article>
        </div>
    </section>
@endif
