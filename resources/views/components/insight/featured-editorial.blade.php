@props(['articles', 'categoryName', 'publishedDate', 'readingTime', 'authorName'])

@php
    $articles = collect($articles ?? [])->take(3)->values();
    $primary = $articles->first();
    $secondary = $articles->skip(1);
    $hasImage = fn ($article): bool => filled($article?->cover_image) && edulaw_file_exists($article->cover_image);
@endphp

@if ($primary)
    <section class="bg-[#fbfaf7] py-10 sm:py-12 lg:py-14" aria-labelledby="featured-editorial-heading">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center gap-3">
                <h2 id="featured-editorial-heading" class="font-display text-2xl font-bold text-brand-navy sm:text-3xl">Editorial Pilihan</h2>
                <span class="h-1 w-10 rounded-full bg-brand-amber" aria-hidden="true"></span>
            </div>

            <div class="grid gap-4 lg:grid-cols-[minmax(0,1.65fr)_minmax(300px,0.85fr)] lg:auto-rows-[minmax(0,1fr)]">
                <article class="group min-h-[310px] sm:min-h-[390px] lg:min-h-[470px]" data-featured-editorial="{{ $primary->id }}">
                    <a href="{{ route('insights.show', $primary->slug) }}" class="relative flex h-full min-h-[310px] overflow-hidden rounded-2xl bg-brand-navy shadow-sm ring-1 ring-black/5 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber sm:min-h-[390px] lg:min-h-[470px]">
                        @if ($hasImage($primary))
                            <img src="{{ $primary->cover_image_url }}" alt="{{ $primary->title }}" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.025] motion-reduce:transition-none" fetchpriority="high">
                        @else
                            <div class="absolute inset-0 bg-linear-to-br from-brand-navy via-[#234a70] to-[#0f766e]"></div>
                            <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_18%,rgba(255,255,255,0.16),transparent_28%)]"></div>
                        @endif
                        <div class="absolute inset-0 bg-linear-to-t from-black/90 via-black/30 to-transparent"></div>
                        <div class="relative mt-auto w-full p-5 text-white sm:p-7 lg:p-8">
                            <span class="inline-flex rounded-full bg-brand-amber px-3 py-1 text-[10px] font-extrabold uppercase tracking-[0.14em] text-brand-ink">{{ $categoryName($primary) }}</span>
                            <h3 class="mt-3 line-clamp-3 max-w-[25ch] text-balance font-display text-2xl font-bold leading-tight text-white sm:text-3xl lg:text-4xl">{{ $primary->title }}</h3>
                            <div class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-semibold text-white/75">
                                <time datetime="{{ optional($primary->published_at)->toDateString() }}">{{ $publishedDate($primary) }}</time>
                                <span aria-hidden="true">•</span><span>{{ $readingTime($primary) }}</span>
                                @if (filled($authorName($primary)))
                                    <span aria-hidden="true">•</span><span>{{ $authorName($primary) }}</span>
                                @endif
                            </div>
                            <span class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-white">Baca Selengkapnya <span aria-hidden="true">→</span></span>
                        </div>
                    </a>
                </article>

                @if ($secondary->isNotEmpty())
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                        @foreach ($secondary as $article)
                            <article class="group min-h-[230px] sm:min-h-[250px] lg:min-h-0" data-featured-editorial="{{ $article->id }}">
                                <a href="{{ route('insights.show', $article->slug) }}" class="relative flex h-full min-h-[230px] overflow-hidden rounded-2xl bg-brand-navy shadow-sm ring-1 ring-black/5 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber sm:min-h-[250px] lg:min-h-0">
                                    @if ($hasImage($article))
                                        <img src="{{ $article->cover_image_url }}" alt="{{ $article->title }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.025] motion-reduce:transition-none">
                                    @else
                                        <div class="absolute inset-0 bg-linear-to-br from-[#173968] via-brand-navy to-[#0f766e]"></div>
                                    @endif
                                    <div class="absolute inset-0 bg-linear-to-t from-black/90 via-black/35 to-transparent"></div>
                                    <div class="relative mt-auto w-full p-5 text-white">
                                        <span class="inline-flex rounded-full bg-brand-teal px-2.5 py-1 text-[9px] font-extrabold uppercase tracking-[0.12em] text-white">{{ $categoryName($article) }}</span>
                                        <h3 class="mt-2 line-clamp-2 text-lg font-bold leading-snug text-white sm:text-xl">{{ $article->title }}</h3>
                                        <p class="mt-2 text-[11px] font-semibold text-white/70">{{ $publishedDate($article) }} · {{ $readingTime($article) }}</p>
                                    </div>
                                </a>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
