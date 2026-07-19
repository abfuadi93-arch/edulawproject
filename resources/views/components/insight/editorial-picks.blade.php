@props(['articles', 'categoryName', 'publishedDate', 'readingTime', 'archiveUrl'])

@php
    $articles = collect($articles ?? [])->take(4)->values();
    $hasImage = fn ($article): bool => filled($article?->cover_image) && edulaw_file_exists($article->cover_image);
@endphp

@if ($articles->isNotEmpty())
    <section class="border-y border-[#e6dfd0] bg-[#f5f1e8] py-10 sm:py-12 lg:py-14" aria-labelledby="editor-picks-heading">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h2 id="editor-picks-heading" class="font-display text-2xl font-bold text-brand-navy sm:text-3xl">Pilihan Editor</h2>
                        <span class="h-1 w-10 rounded-full bg-brand-amber" aria-hidden="true"></span>
                    </div>
                    <p class="mt-2 text-sm text-slate-600">Kurasi redaksi untuk isu hukum yang penting dibaca.</p>
                </div>
                <a href="{{ $archiveUrl }}" class="shrink-0 text-sm font-bold text-brand-navy underline decoration-brand-amber decoration-2 underline-offset-4 hover:text-brand-coral">Lihat semua</a>
            </div>

            <div class="mt-7 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($articles as $article)
                    <article class="group min-w-0" data-editor-pick="{{ $article->id }}">
                        <a href="{{ route('insights.show', $article->slug) }}" class="block rounded-2xl focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">
                            <div class="relative aspect-[4/3] overflow-hidden rounded-2xl bg-brand-navy shadow-sm ring-1 ring-black/5">
                                @if ($hasImage($article))
                                    <img src="{{ $article->cover_image_url }}" alt="{{ $article->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.035] motion-reduce:transition-none">
                                @else
                                    <div class="absolute inset-0 bg-linear-to-br from-brand-navy via-[#244972] to-[#0f766e]"></div>
                                @endif
                                <span class="absolute left-3 top-3 rounded-full bg-brand-amber px-2.5 py-1 text-[9px] font-extrabold uppercase tracking-[0.13em] text-brand-ink">{{ $categoryName($article) }}</span>
                            </div>
                            <h3 class="mt-3 line-clamp-2 text-base font-bold leading-snug text-brand-ink transition group-hover:text-brand-navy sm:text-lg">{{ $article->title }}</h3>
                            <p class="mt-2 text-[11px] font-semibold text-slate-500">{{ $publishedDate($article) }} · {{ $readingTime($article) }}</p>
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
