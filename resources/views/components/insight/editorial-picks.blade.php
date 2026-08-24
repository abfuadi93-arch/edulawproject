@props(['articles', 'categoryName', 'publishedDate', 'readingTime', 'archiveUrl'])

@php
    $articles = collect($articles ?? [])->take(4)->values();
    $hasImage = fn ($article): bool => filled($article?->cover_image) && edulaw_file_exists($article->cover_image);
@endphp

@if ($articles->isNotEmpty())
    <section id="editorial-terbaru" class="bg-[#f7f8fa] py-10 sm:py-11 lg:py-12" aria-labelledby="latest-editorial-heading">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-brand-coral"><span class="mr-1 text-brand-amber" aria-hidden="true">●</span> Terbaru</p>
                    <h2 id="latest-editorial-heading" class="mt-1 font-display text-2xl font-black text-brand-navy sm:text-3xl">Editorial Terbaru</h2>
                    <p class="mt-1.5 text-base leading-7 text-slate-600">Analisis dan penjelasan terbaru mengenai hukum, regulasi, putusan, dan kebijakan publik.</p>
                </div>
                <a href="{{ $archiveUrl }}" class="shrink-0 text-sm font-extrabold text-brand-navy underline decoration-brand-amber decoration-2 underline-offset-4 hover:text-brand-coral">Lihat Semua Editorial <span aria-hidden="true">→</span></a>
            </div>

            <div class="mt-7 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($articles as $article)
                    <article class="group min-w-0" data-latest-editorial="{{ $article->id }}">
                        <a href="{{ route('insights.show', $article->slug) }}" class="block focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-brand-amber">
                            <div class="relative aspect-[16/10] overflow-hidden rounded-[13px] bg-brand-navy">
                                @if ($hasImage($article))
                                    <img src="{{ $article->cover_image_url }}" alt="{{ $article->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.025] motion-reduce:transition-none">
                                @else
                                    <div class="absolute inset-0 bg-linear-to-br from-brand-navy via-[#244972] to-[#0f766e]"></div>
                                @endif
                            </div>
                            <div class="pt-4">
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.1em] text-brand-coral">{{ $categoryName($article) }}</p>
                                <h3 class="mt-1.5 line-clamp-3 text-base font-black leading-snug text-brand-ink transition group-hover:text-brand-navy">{{ $article->title }}</h3>
                                <p class="mt-2.5 text-xs font-semibold text-slate-500">{{ $publishedDate($article) }} · {{ $readingTime($article) }}</p>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
